<?php

/**
 * PHPMailer POP-Before-SMTP Authentication Class.
 * PHP Version 7.4+ recommended.
 *
 * @see https://github.com/PHPMailer/PHPMailer/ The PHPMailer GitHub project
 *
 * @author    Marcus Bointon (Synchro/coolbru) <phpmailer@synchromedia.co.uk>
 * @author    Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author    Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author    Brent R. Matzelle (original founder)
 * @copyright 2012 - 2023 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license   https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License
 * @note      This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

namespace PHPMailer\PHPMailer;

/**
 * PHPMailer POP-Before-SMTP Authentication Class.
 * Modernized implementation with improved error handling and security.
 */
class POP3
{
    /**
     * The POP3 PHPMailer Version number.
     */
    const VERSION = '6.10.1';

    /**
     * Default POP3 port number.
     */
    const DEFAULT_PORT = 110;

    /**
     * Default timeout in seconds.
     */
    const DEFAULT_TIMEOUT = 30;

    /**
     * Debug level constants.
     */
    const DEBUG_OFF = 0;
    const DEBUG_SERVER = 1;
    const DEBUG_CLIENT = 2;

    /**
     * Line break constant.
     */
    const LE = "\r\n";

    /**
     * POP3 mail server hostname.
     */
    public string $host;

    /**
     * POP3 port number.
     */
    public int $port;

    /**
     * POP3 Timeout Value in seconds.
     */
    public int $tval;

    /**
     * POP3 username.
     */
    public string $username;

    /**
     * POP3 password.
     */
    public string $password;

    /**
     * Debug output mode.
     */
    public int $do_debug = self::DEBUG_OFF;

    /**
     * Resource handle for the POP3 connection socket.
     */
    protected $pop_conn;

    /**
     * Connection status flag.
     */
    protected bool $connected = false;

    /**
     * Error container.
     */
    protected array $errors = [];

    /**
     * Simple static wrapper for all-in-one POP before SMTP.
     */
    public static function popBeforeSmtp(
        string $host,
        $port = false,
        $timeout = false,
        string $username = '',
        string $password = '',
        int $debug_level = 0
    ): bool {
        return (new self())->authorise($host, $port, $timeout, $username, $password, $debug_level);
    }

    /**
     * Authenticate with a POP3 server.
     */
    public function authorise(
        string $host,
        $port = false,
        $timeout = false,
        string $username = '',
        string $password = '',
        int $debug_level = 0
    ): bool {
        $this->host = $host;
        $this->port = (false === $port) ? static::DEFAULT_PORT : (int)$port;
        $this->tval = (false === $timeout) ? static::DEFAULT_TIMEOUT : (int)$timeout;
        $this->do_debug = $debug_level;
        $this->username = $username;
        $this->password = $password;
        $this->errors = [];

        try {
            if ($this->connect($this->host, $this->port, $this->tval)) {
                if ($this->login($this->username, $this->password)) {
                    $this->disconnect();
                    return true;
                }
            }
        } catch (\Exception $e) {
            $this->setError('POP3 authentication failed: ' . $e->getMessage());
        }

        $this->disconnect();
        return false;
    }

    /**
     * Connect to a POP3 server.
     */
    public function connect(string $host, $port = false, int $tval = 30): bool
    {
        if ($this->connected) {
            return true;
        }

        if (false === $port) {
            $port = static::DEFAULT_PORT;
        }

        set_error_handler([$this, 'handleConnectionError']);

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false
            ]
        ]);

        $this->pop_conn = stream_socket_client(
            "tcp://{$host}:{$port}",
            $errno,
            $errstr,
            $tval,
            STREAM_CLIENT_CONNECT,
            $context
        );

        restore_error_handler();

        if (!is_resource($this->pop_conn)) {
            $this->setError(
                "Failed to connect to server {$host} on port {$port}. errno: {$errno}; errstr: {$errstr}"
            );
            return false;
        }

        stream_set_timeout($this->pop_conn, $tval, 0);
        stream_set_blocking($this->pop_conn, true);

        $pop3_response = $this->getResponse();
        if ($this->checkResponse($pop3_response)) {
            $this->connected = true;
            return true;
        }

        return false;
    }

    /**
     * Log in to the POP3 server.
     */
    public function login(string $username = '', string $password = ''): bool
    {
        if (!$this->connected) {
            $this->setError('Not connected to POP3 server');
            return false;
        }

        $username = $username ?: $this->username;
        $password = $password ?: $this->password;

        if ($this->sendCommand("USER {$username}") && $this->sendCommand("PASS {$password}")) {
            return true;
        }

        return false;
    }

    /**
     * Disconnect from the POP3 server.
     */
    public function disconnect(): void
    {
        if (!is_resource($this->pop_conn)) {
            return;
        }

        try {
            $this->sendString('QUIT' . static::LE);
            $this->getResponse();
        } catch (\Exception $e) {
            // Ignore disconnect errors
        }

        try {
            if (is_resource($this->pop_conn)) {
                fclose($this->pop_conn);
            }
        } catch (\Exception $e) {
            // Ignore close errors
        }

        $this->connected = false;
        $this->pop_conn = null;
    }

    /**
     * Send a command and check response.
     */
    protected function sendCommand(string $command): bool
    {
        $this->sendString($command . static::LE);
        return $this->checkResponse($this->getResponse());
    }

    /**
     * Get a response from the POP3 server.
     */
    protected function getResponse(int $size = 128): string
    {
        $response = fgets($this->pop_conn, $size);
        
        if ($this->do_debug >= self::DEBUG_SERVER) {
            echo 'Server -> Client: ', htmlspecialchars($response);
        }

        if ($response === false) {
            throw new \RuntimeException('Failed to read from POP3 connection');
        }

        return $response;
    }

    /**
     * Send raw data to the POP3 server.
     */
    protected function sendString(string $string): int
    {
        if (!is_resource($this->pop_conn)) {
            return 0;
        }

        if ($this->do_debug >= self::DEBUG_CLIENT) {
            echo 'Client -> Server: ', htmlspecialchars($string);
        }

        $result = fwrite($this->pop_conn, $string);
        
        if ($result === false) {
            throw new \RuntimeException('Failed to write to POP3 connection');
        }

        return $result;
    }

    /**
     * Check the POP3 server response.
     */
    protected function checkResponse(string $string): bool
    {
        if (strpos($string, '+OK') !== 0) {
            $this->setError("Server reported an error: " . trim($string));
            return false;
        }
        return true;
    }

    /**
     * Handle connection errors.
     */
    protected function handleConnectionError(int $errno, string $errstr, string $errfile = '', string $errline = 0): bool
    {
        $this->setError(sprintf(
            'Connecting to the POP3 server raised a PHP warning: errno: %d; errstr: %s; errfile: %s; errline: %d',
            $errno,
            $errstr,
            $errfile,
            $errline
        ));
        
        return true;
    }

    /**
     * Add an error to the internal error store.
     */
    protected function setError(string $error): void
    {
        $this->errors[] = $error;
        
        if ($this->do_debug >= self::DEBUG_SERVER) {
            echo '<pre>', htmlspecialchars(print_r($error, true)), '</pre>';
        }
    }

    /**
     * Get an array of error messages.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Destructor - ensure connection is closed.
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}
