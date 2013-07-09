<?php
/**
 * PHPMailer POP-Before-SMTP Authentication Class.
 * PHP Version 5.0.0
 * Version 5.2.7
 * @package PHPMailer
 * @link https://github.com/PHPMailer/PHPMailer/
 * @author Marcus Bointon (coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author Brent R. Matzelle (original founder)
 * @copyright 2013 Marcus Bointon
 * @copyright 2010 - 2012 Jim Jagielski
 * @copyright 2004 - 2009 Andy Prevost
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * PHPMailer POP-Before-SMTP Authentication Class.
 * Specifically for PHPMailer to use for RFC1939 POP-before-SMTP authentication.
 * Does not support APOP.
 * @package PHPMailer
 * @author Richard Davey (original author) <rich@corephp.co.uk>
 * @author Marcus Bointon (coolbru) <phpmailer@synchromedia.co.uk>
 * @author Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 */

class POP3
{
    /**
     * The POP3 PHPMailer Version number.
     * @type string
     * @access public
     */
    public $Version = '5.2.7';

    /**
     * Default POP3 port number.
     * @type int
     * @access public
     */
    public $POP3_PORT = 110;

    /**
     * Default timeout in seconds.
     * @type int
     * @access public
     */
    public $POP3_TIMEOUT = 30;

    /**
     * POP3 Carriage Return + Line Feed.
     * @type string
     * @access public
     */
    public $CRLF = "\r\n";

    /**
     * Debug display level.
     * Options: 0 = no, 1+ = yes
     * @type int
     * @access public
     */
    public $do_debug = 0;

    /**
     * POP3 mail server hostname.
     * @type string
     * @access public
     */
    public $host;

    /**
     * POP3 port number.
     * @type int
     * @access public
     */
    public $port;

    /**
     * POP3 Timeout Value in seconds.
     * @type int
     * @access public
     */
    public $tval;

    /**
     * POP3 username
     * @type string
     * @access public
     */
    public $username;

    /**
     * POP3 password.
     * @type string
     * @access public
     */
    public $password;

    /**
     * Resource handle for the POP3 connection socket.
     * @type resource
     * @access private
     */
    private $pop_conn;

    /**
     * Are we connected?
     * @type bool
     * @access private
     */
    private $connected;

    /**
     * Error container.
     * @type array
     * @access private
     */
    private $error;

    /**
     * Constructor.
     * @access public
     * @access private
     */
    public function __construct()
    {
        $this->pop_conn = 0;
        $this->connected = false;
        $this->error = null;
    }

    /**
     * Authenticate with a POP3 server.
     * A connect, login, disconnect sequence
     * appropriate for POP-before SMTP authorisation.
     * @access public
     * @param string $host
     * @param bool|int $port
     * @param bool|int $tval
     * @param string $username
     * @param string $password
     * @param int $debug_level
     * @return bool
     */
    public function authorise($host, $port = false, $tval = false, $username = '', $password = '', $debug_level = 0)
    {
        $this->host = $host;
        //  If no port value is passed, retrieve it
        if ($port == false) {
            $this->port = $this->POP3_PORT;
        } else {
            $this->port = $port;
        }
        //  If no port value is passed, retrieve it
        if ($tval == false) {
            $this->tval = $this->POP3_TIMEOUT;
        } else {
            $this->tval = $tval;
        }
        $this->do_debug = $debug_level;
        $this->username = $username;
        $this->password = $password;
        //  Refresh the error log
        $this->error = null;
        //  connect
        $result = $this->connect($this->host, $this->port, $this->tval);
        if ($result) {
            $login_result = $this->login($this->username, $this->password);
            if ($login_result) {
                $this->disconnect();
                return true;
            }
        }
        //  We need to disconnect regardless if the login succeeded
        $this->disconnect();
        return false;
    }

    /**
     * Connect to a POP3 server.
     * @access public
     * @param string $host
     * @param bool|int $port
     * @param integer $tval
     * @return boolean
     */
    public function connect($host, $port = false, $tval = 30)
    {
        //  Are we already connected?
        if ($this->connected) {
            return true;
        }

        //On Windows this will raise a PHP Warning error if the hostname doesn't exist.
        //Rather than suppress it with @fsockopen, capture it cleanly instead
        set_error_handler(array($this, 'catchWarning'));

        //  connect to the POP3 server
        $this->pop_conn = fsockopen(
            $host, //  POP3 Host
            $port, //  Port #
            $errno, //  Error Number
            $errstr, //  Error Message
            $tval
        ); //  Timeout (seconds)
        //  Restore the error handler
        restore_error_handler();
        //  Does the Error Log now contain anything?
        if ($this->error && $this->do_debug >= 1) {
            $this->displayErrors();
        }
        //  Did we connect?
        if ($this->pop_conn == false) {
            //  It would appear not...
            $this->error = array(
                'error' => "Failed to connect to server $host on port $port",
                'errno' => $errno,
                'errstr' => $errstr
            );
            if ($this->do_debug >= 1) {
                $this->displayErrors();
            }
            return false;
        }

        //  Increase the stream time-out
        //  Check for PHP 4.3.0 or later
        if (version_compare(phpversion(), '5.0.0', 'ge')) {
            stream_set_timeout($this->pop_conn, $tval, 0);
        } else {
            //  Does not work on Windows
            if (substr(PHP_OS, 0, 3) !== 'WIN') {
                socket_set_timeout($this->pop_conn, $tval, 0);
            }
        }

        //  Get the POP3 server response
        $pop3_response = $this->getResponse();
        //  Check for the +OK
        if ($this->checkResponse($pop3_response)) {
            //  The connection is established and the POP3 server is talking
            $this->connected = true;
            return true;
        }
        return false;
    }

    /**
     * Log in to the POP3 server.
     * Does not support APOP (RFC 2828, 4949).
     * @access public
     * @param string $username
     * @param string $password
     * @return boolean
     */
    public function login($username = '', $password = '')
    {
        if ($this->connected == false) {
            $this->error = 'Not connected to POP3 server';

            if ($this->do_debug >= 1) {
                $this->displayErrors();
            }
        }
        if (empty($username)) {
            $username = $this->username;
        }
        if (empty($password)) {
            $password = $this->password;
        }
        $pop_username = "USER $username" . $this->CRLF;
        $pop_password = "PASS $password" . $this->CRLF;
        //  send the Username
        $this->sendString($pop_username);
        $pop3_response = $this->getResponse();
        if ($this->checkResponse($pop3_response)) {
            //  send the Password
            $this->sendString($pop_password);
            $pop3_response = $this->getResponse();
            if ($this->checkResponse($pop3_response)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Disconnect from the POP3 server.
     * @access public
     */
    public function disconnect()
    {
        $this->sendString('QUIT');
        fclose($this->pop_conn);
    }

    /**
     * Get a response from the POP3 server.
     * $size is the maximum number of bytes to retrieve
     * @param integer $size
     * @return string
     * @access private
     */
    private function getResponse($size = 128)
    {
        $pop3_response = fgets($this->pop_conn, $size);
        return $pop3_response;
    }

    /**
     * Send raw data to the POP3 server.
     * @param string $string
     * @return integer
     * @access private
     */
    private function sendString($string)
    {
        $bytes_sent = fwrite($this->pop_conn, $string, strlen($string));
        return $bytes_sent;
    }

    /**
     * Checks the POP3 server response.
     * Looks for for +OK or -ERR.
     * @param string $string
     * @return boolean
     * @access private
     */
    private function checkResponse($string)
    {
        if (substr($string, 0, 3) !== '+OK') {
            $this->error = array(
                'error' => "Server reported an error: $string",
                'errno' => 0,
                'errstr' => ''
            );
            if ($this->do_debug >= 1) {
                $this->displayErrors();
            }
            return false;
        } else {
            return true;
        }
    }

    /**
     * Display errors if debug is enabled.
     * @access private
     */
    private function displayErrors()
    {
        echo '<pre>';
        foreach ($this->error as $single_error) {
            print_r($single_error);
        }
        echo '</pre>';
    }

    /**
     * POP3 connection error handler.
     * @param integer $errno
     * @param string $errstr
     * @param string $errfile
     * @param integer $errline
     * @access private
     */
    private function catchWarning($errno, $errstr, $errfile, $errline)
    {
        $this->error[] = array(
            'error' => "Connecting to the POP3 server raised a PHP warning: ",
            'errno' => $errno,
            'errstr' => $errstr,
            'errfile' => $errfile,
            'errline' => $errline
        );
    }
}
