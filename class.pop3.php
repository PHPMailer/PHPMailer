<?php
/**
 * PHPMailer - PHP POP-Before-SMTP Authentication Class
 *
 * PHP Version 5.0.0
 *
 * @package PHPMailer
 * @link https://github.com/PHPMailer/PHPMailer/
 * @author Marcus Bointon (coolbru) phpmailer@synchromedia.co.uk
 * @author Jim Jagielski (jimjag) jimjag@gmail.com
 * @author Andy Prevost (codeworxtech) codeworxtech@users.sourceforge.net
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
 * PHP POP-Before-SMTP Authentication Class
 *
 * @license: LGPL, see PHPMailer License
 *
 * Specifically for PHPMailer to allow POP before SMTP authentication.
 * Does not yet work with APOP - if you have an APOP account, contact Jim Jagielski
 * and we can test changes to this script.
 *
 * This class is based on the structure of the SMTP class originally authored by Chris Ryan
 *
 * This class is rfc 1939 compliant and implements all the commands
 * required for POP3 connection, authentication and disconnection.
 *
 * @package PHPMailer
 * @author Richard Davey (orig) <rich@corephp.co.uk>
 * @author Andy Prevost
 * @author Jim Jagielski
 */

class POP3
{
    /**
     * @var string The POP3 PHPMailer Version number
     */
    public $Version = '5.2.6';

    /**
     * @var int Default POP3 port number
     */
    public $POP3_PORT = 110;

    /**
     * @var int Default Timeout in seconds
     */
    public $POP3_TIMEOUT = 30;

    /**
     * @var string POP3 Carriage Return + Line Feed
     */
    public $CRLF = "\r\n";

    /**
     * @var int Debug display level
     * Options: 0 = no, 1+ = yes
     */
    public $do_debug = 0;

    /**
     * @var string POP3 mail Server
     */
    public $host;

    /**
     * @var int POP3 Port number
     */
    public $port;

    /**
     * @var int POP3 Timeout Value in seconds
     */
    public $tval;

    /**
     * @var string POP3 Username
     */
    public $username;

    /**
     * @var string POP3 Password
     */
    public $password;

    /**
     * @var resource Resource handle for the POP connection socket
     */
    private $pop_conn;

    /**
     * @var bool Are we connected?
     */
    private $connected;

    /**
     * @var array Error container
     */
    private $error; //  Error log array

    /**
     * Constructor
     * @access public
     */
    public function __construct()
    {
        $this->pop_conn = 0;
        $this->connected = false;
        $this->error = null;
    }

    /**
     * Combination of public events - connect, login, disconnect
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
     * Connect to the POP3 server
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
        set_error_handler(array(&$this, 'catchWarning'));

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
     * login to the POP3 server (does not support APOP yet)
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
     * disconnect from the POP3 server
     * @access public
     */
    public function disconnect()
    {
        $this->sendString('QUIT');
        fclose($this->pop_conn);
    }

    /**
     * Get the socket response back.
     * $size is the maximum number of bytes to retrieve
     * @access private
     * @param integer $size
     * @return string
     */
    private function getResponse($size = 128)
    {
        $pop3_response = fgets($this->pop_conn, $size);
        return $pop3_response;
    }

    /**
     * send a string down the open socket connection to the POP3 server
     * @access private
     * @param string $string
     * @return integer
     */
    private function sendString($string)
    {
        $bytes_sent = fwrite($this->pop_conn, $string, strlen($string));
        return $bytes_sent;
    }

    /**
     * Checks the POP3 server response for +OK or -ERR
     * @access private
     * @param string $string
     * @return boolean
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
     * If debug is enabled, display the error message array
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
     * Takes over from PHP for the socket warning handler
     * @access private
     * @param integer $errno
     * @param string $errstr
     * @param string $errfile
     * @param integer $errline
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
