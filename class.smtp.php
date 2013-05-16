<?php
/*~ class.smtp.php
.---------------------------------------------------------------------------.
|  Software: PHPMailer - PHP email class                                    |
|   Version: 5.2.6                                                          |
|      Site: https://github.com/PHPMailer/PHPMailer/                        |
| ------------------------------------------------------------------------- |
|    Admins: Marcus Bointon                                                 |
|    Admins: Jim Jagielski                                                  |
|   Authors: Andy Prevost (codeworxtech) codeworxtech@users.sourceforge.net |
|          : Marcus Bointon (coolbru) phpmailer@synchromedia.co.uk          |
|          : Jim Jagielski (jimjag) jimjag@gmail.com                        |
|   Founder: Brent R. Matzelle (original founder)                           |
| Copyright (c) 2010-2012, Jim Jagielski. All Rights Reserved.              |
| Copyright (c) 2004-2009, Andy Prevost. All Rights Reserved.               |
| Copyright (c) 2001-2003, Brent R. Matzelle                                |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (LGPL)     |
|            http://www.gnu.org/copyleft/lesser.html                        |
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
'---------------------------------------------------------------------------'
*/

/**
 * PHPMailer - PHP SMTP email transport class
 * NOTE: Designed for use with PHP version 5 and up
 * @package PHPMailer
 * @author Andy Prevost
 * @author Marcus Bointon
 * @copyright 2004 - 2008 Andy Prevost
 * @author Jim Jagielski
 * @copyright 2010 - 2012 Jim Jagielski
 * @license http://www.gnu.org/copyleft/lesser.html Distributed under the Lesser General Public License (LGPL)
 */

/**
 * PHP RFC821 SMTP client
 *
 * Implements all the RFC 821 SMTP commands except TURN which will always return a not implemented error.
 * SMTP also provides some utility methods for sending mail to an SMTP server.
 * @author Chris Ryan
 * @package PHPMailer
 */

class SMTP {
  /**
   *  SMTP server port
   *  @var int
   */
  public $SMTP_PORT = 25;

  /**
   *  SMTP reply line ending (don't change)
   *  @var string
   */
  public $CRLF = "\r\n";

  /**
   *  Debug output level; 0 for no output
   *  @var int
   */
  public $do_debug = 0;

  /**
   * Sets the function/method to use for debugging output.
   * Right now we only honor 'echo', 'html' or 'error_log'
   * @var string
   */
  public $Debugoutput     = 'echo';

  /**
   *  Sets VERP use on/off (default is off)
   *  @var bool
   */
  public $do_verp = false;

  /**
   * Sets the SMTP timeout value for reads, in seconds
   * @var int
   */
  public $Timeout         = 15;

  /**
   * Sets the SMTP timelimit value for reads, in seconds
   * @var int
   */
  public $Timelimit       = 30;

  /**
   * Sets the SMTP PHPMailer Version number
   * @var string
   */
  public $Version         = '5.2.6';

  /////////////////////////////////////////////////
  // PROPERTIES, PRIVATE AND PROTECTED
  /////////////////////////////////////////////////

  /**
   * @var resource The socket to the server
   */
  protected $smtp_conn;
  /**
   * @var string Error message, if any, for the last call
   */
  protected $error;
  /**
   * @var string The reply the server sent to us for HELO
   */
  protected $helo_rply;

  /**
   * Outputs debugging info via user-defined method
   * @param string $str
   */
  protected function edebug($str) {
    switch ($this->Debugoutput) {
      case 'error_log':
        error_log($str);
        break;
      case 'html':
        //Cleans up output a bit for a better looking display that's HTML-safe
        echo htmlentities(preg_replace('/[\r\n]+/', '', $str), ENT_QUOTES, 'UTF-8')."<br>\n";
        break;
      case 'echo':
      default:
        //Just echoes exactly what was received
        echo $str;
    }
  }

  /**
   * Initialize the class so that the data is in a known state.
   * @access public
   * @return SMTP
   */
  public function __construct() {
    $this->smtp_conn = 0;
    $this->error = null;
    $this->helo_rply = null;

    $this->do_debug = 0;
  }

  /////////////////////////////////////////////////
  // CONNECTION FUNCTIONS
  /////////////////////////////////////////////////

  /**
   * Connect to an SMTP server
   *
   * SMTP CODE SUCCESS: 220
   * SMTP CODE FAILURE: 421
   * @access public
   * @param string $host SMTP server IP or host name
   * @param int $port The port number to connect to, or use the default port if not specified
   * @param int $timeout How long to wait for the connection to open
   * @param array $options An array of options compatible with stream_context_create()
   * @return bool
   */
  public function Connect($host, $port = 0, $timeout = 30, $options = array()) {
    // Clear errors to avoid confusion
    $this->error = null;

    // Make sure we are __not__ connected
    if($this->connected()) {
      // Already connected, generate error
      $this->error = array('error' => 'Already connected to a server');
      return false;
    }

    if(empty($port)) {
      $port = $this->SMTP_PORT;
    }

    // Connect to the SMTP server
    $errno = 0;
    $errstr = '';
    $socket_context = stream_context_create($options);
    //Need to suppress errors here as connection failures can be handled at a higher level
    $this->smtp_conn = @stream_socket_client($host.":".$port, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $socket_context);

    // Verify we connected properly
    if(empty($this->smtp_conn)) {
      $this->error = array('error' => 'Failed to connect to server',
                           'errno' => $errno,
                           'errstr' => $errstr);
      if($this->do_debug >= 1) {
        $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ": $errstr ($errno)");
      }
      return false;
    }

    // SMTP server can take longer to respond, give longer timeout for first read
    // Windows does not have support for this timeout function
    if(substr(PHP_OS, 0, 3) != 'WIN') {
      $max = ini_get('max_execution_time');
      if ($max != 0 && $timeout > $max) { // Don't bother if unlimited
        @set_time_limit($timeout);
      }
      stream_set_timeout($this->smtp_conn, $timeout, 0);
    }

    // get any announcement
    $announce = $this->get_lines();

    if($this->do_debug >= 2) {
      $this->edebug('SMTP -> FROM SERVER:' . $announce);
    }

    return true;
  }

  /**
   * Initiate a TLS communication with the server.
   *
   * SMTP CODE 220 Ready to start TLS
   * SMTP CODE 501 Syntax error (no parameters allowed)
   * SMTP CODE 454 TLS not available due to temporary reason
   * @access public
   * @return bool success
   */
  public function StartTLS() {
    $this->error = null; # to avoid confusion

    if(!$this->connected()) {
      $this->error = array('error' => 'Called StartTLS() without being connected');
      return false;
    }

    $this->client_send('STARTTLS' . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply, 0, 3);

    if($this->do_debug >= 2) {
      $this->edebug('SMTP -> FROM SERVER:' . $rply);
    }

    if($code != 220) {
      $this->error =
         array('error'     => 'STARTTLS not accepted from server',
               'smtp_code' => $code,
               'smtp_msg'  => substr($rply, 4));
      if($this->do_debug >= 1) {
        $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
      }
      return false;
    }

    // Begin encrypted connection
    if(!stream_socket_enable_crypto($this->smtp_conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
      return false;
    }

    return true;
  }

  /**
   * Performs SMTP authentication.  Must be run after running the
   * Hello() method.  Returns true if successfully authenticated.
   * @access public
   * @param string $username
   * @param string $password
   * @param string $authtype
   * @param string $realm
   * @param string $workstation
   * @return bool
   */
  public function Authenticate($username, $password, $authtype='LOGIN', $realm='', $workstation='') {
    if (empty($authtype)) {
      $authtype = 'LOGIN';
    }

    switch ($authtype) {
      case 'PLAIN':
        // Start authentication
        $this->client_send('AUTH PLAIN' . $this->CRLF);

        $rply = $this->get_lines();
        $code = substr($rply, 0, 3);

        if($code != 334) {
          $this->error =
            array('error' => 'AUTH not accepted from server',
                  'smtp_code' => $code,
                  'smtp_msg' => substr($rply, 4));
          if($this->do_debug >= 1) {
            $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
          }
          return false;
        }
        // Send encoded username and password
          $this->client_send(base64_encode("\0".$username."\0".$password) . $this->CRLF);

        $rply = $this->get_lines();
        $code = substr($rply, 0, 3);

        if($code != 235) {
          $this->error =
            array('error' => 'Authentication not accepted from server',
                  'smtp_code' => $code,
                  'smtp_msg' => substr($rply, 4));
          if($this->do_debug >= 1) {
            $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
          }
          return false;
        }
        break;
      case 'LOGIN':
        // Start authentication
        $this->client_send('AUTH LOGIN' . $this->CRLF);

        $rply = $this->get_lines();
        $code = substr($rply, 0, 3);

        if($code != 334) {
          $this->error =
            array('error' => 'AUTH not accepted from server',
                  'smtp_code' => $code,
                  'smtp_msg' => substr($rply, 4));
          if($this->do_debug >= 1) {
            $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
          }
          return false;
        }

        // Send encoded username
        $this->client_send(base64_encode($username) . $this->CRLF);

        $rply = $this->get_lines();
        $code = substr($rply, 0, 3);

        if($code != 334) {
          $this->error =
            array('error' => 'Username not accepted from server',
                  'smtp_code' => $code,
                  'smtp_msg' => substr($rply, 4));
          if($this->do_debug >= 1) {
            $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
          }
          return false;
        }

        // Send encoded password
        $this->client_send(base64_encode($password) . $this->CRLF);

        $rply = $this->get_lines();
        $code = substr($rply, 0, 3);

        if($code != 235) {
          $this->error =
            array('error' => 'Password not accepted from server',
                  'smtp_code' => $code,
                  'smtp_msg' => substr($rply, 4));
          if($this->do_debug >= 1) {
            $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
          }
          return false;
        }
        break;
      case 'NTLM':
        /*
         * ntlm_sasl_client.php
         ** Bundled with Permission
         **
         ** How to telnet in windows: http://technet.microsoft.com/en-us/library/aa995718%28EXCHG.65%29.aspx
         ** PROTOCOL Documentation http://curl.haxx.se/rfc/ntlm.html#ntlmSmtpAuthentication
         */
        require_once 'extras/ntlm_sasl_client.php';
        $temp = new stdClass();
        $ntlm_client = new ntlm_sasl_client_class;
        if(! $ntlm_client->Initialize($temp)){//let's test if every function its available
            $this->error = array('error' => $temp->error);
            if($this->do_debug >= 1) {
                $this->edebug('You need to enable some modules in your php.ini file: ' . $this->error['error']);
            }
            return false;
        }
        $msg1 = $ntlm_client->TypeMsg1($realm, $workstation);//msg1

        $this->client_send('AUTH NTLM ' . base64_encode($msg1) . $this->CRLF);

        $rply = $this->get_lines();
        $code = substr($rply, 0, 3);

        if($code != 334) {
            $this->error =
                array('error' => 'AUTH not accepted from server',
                      'smtp_code' => $code,
                      'smtp_msg' => substr($rply, 4));
            if($this->do_debug >= 1) {
                $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
            }
            return false;
        }

        $challenge = substr($rply, 3);//though 0 based, there is a white space after the 3 digit number....//msg2
        $challenge = base64_decode($challenge);
        $ntlm_res = $ntlm_client->NTLMResponse(substr($challenge, 24, 8), $password);
        $msg3 = $ntlm_client->TypeMsg3($ntlm_res, $username, $realm, $workstation);//msg3
        // Send encoded username
        $this->client_send(base64_encode($msg3) . $this->CRLF);

        $rply = $this->get_lines();
        $code = substr($rply, 0, 3);

        if($code != 235) {
            $this->error =
                array('error' => 'Could not authenticate',
                      'smtp_code' => $code,
                      'smtp_msg' => substr($rply, 4));
            if($this->do_debug >= 1) {
                $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
            }
            return false;
        }
        break;
      case 'CRAM-MD5':
        // Start authentication
        $this->client_send('AUTH CRAM-MD5' . $this->CRLF);

        $rply = $this->get_lines();
        $code = substr($rply, 0, 3);

        if($code != 334) {
          $this->error =
            array('error' => 'AUTH not accepted from server',
                  'smtp_code' => $code,
                  'smtp_msg' => substr($rply, 4));
          if($this->do_debug >= 1) {
            $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
          }
          return false;
        }

        // Get the challenge
        $challenge = base64_decode(substr($rply, 4));

        // Build the response
        $response = $username . ' ' . $this->hmac($challenge, $password);

        // Send encoded credentials
        $this->client_send(base64_encode($response) . $this->CRLF);

        $rply = $this->get_lines();
        $code = substr($rply, 0, 3);

        if($code != 235) {
          $this->error =
            array('error' => 'Credentials not accepted from server',
                  'smtp_code' => $code,
                  'smtp_msg' => substr($rply, 4));
          if($this->do_debug >= 1) {
            $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
          }
          return false;
        }
        break;
    }
    return true;
  }

  /**
   * Works like hash_hmac('md5', $data, $key) in case that function is not available
   * @access protected
   * @param string $data
   * @param string $key
   * @return string
   */
  protected function hmac($data, $key) {
      if (function_exists('hash_hmac')) {
          return hash_hmac('md5', $data, $key);
      }

      // The following borrowed from http://php.net/manual/en/function.mhash.php#27225

      // RFC 2104 HMAC implementation for php.
      // Creates an md5 HMAC.
      // Eliminates the need to install mhash to compute a HMAC
      // Hacked by Lance Rushing

      $b = 64; // byte length for md5
      if (strlen($key) > $b) {
          $key = pack('H*', md5($key));
      }
      $key  = str_pad($key, $b, chr(0x00));
      $ipad = str_pad('', $b, chr(0x36));
      $opad = str_pad('', $b, chr(0x5c));
      $k_ipad = $key ^ $ipad ;
      $k_opad = $key ^ $opad;

      return md5($k_opad  . pack('H*', md5($k_ipad . $data)));
  }

  /**
   * Returns true if connected to a server otherwise false
   * @access public
   * @return bool
   */
  public function Connected() {
    if(!empty($this->smtp_conn)) {
      $sock_status = stream_get_meta_data($this->smtp_conn);
      if($sock_status['eof']) {
        // the socket is valid but we are not connected
        if($this->do_debug >= 1) {
            $this->edebug('SMTP -> NOTICE: EOF caught while checking if connected');
        }
        $this->Close();
        return false;
      }
      return true; // everything looks good
    }
    return false;
  }

  /**
   * Closes the socket and cleans up the state of the class.
   * It is not considered good to use this function without
   * first trying to use QUIT.
   * @access public
   * @return void
   */
  public function Close() {
    $this->error = null; // so there is no confusion
    $this->helo_rply = null;
    if(!empty($this->smtp_conn)) {
      // close the connection and cleanup
      fclose($this->smtp_conn);
      $this->smtp_conn = 0;
    }
  }

  /////////////////////////////////////////////////
  // SMTP COMMANDS
  /////////////////////////////////////////////////

  /**
   * Issues a data command and sends the msg_data to the server
   * finializing the mail transaction. $msg_data is the message
   * that is to be send with the headers. Each header needs to be
   * on a single line followed by a <CRLF> with the message headers
   * and the message body being seperated by and additional <CRLF>.
   *
   * Implements rfc 821: DATA <CRLF>
   *
   * SMTP CODE INTERMEDIATE: 354
   *     [data]
   *     <CRLF>.<CRLF>
   *     SMTP CODE SUCCESS: 250
   *     SMTP CODE FAILURE: 552, 554, 451, 452
   * SMTP CODE FAILURE: 451, 554
   * SMTP CODE ERROR  : 500, 501, 503, 421
   * @access public
   * @param string $msg_data
   * @return bool
   */
  public function Data($msg_data) {
    $this->error = null; // so no confusion is caused

    if(!$this->connected()) {
      $this->error = array(
              'error' => 'Called Data() without being connected');
      return false;
    }

    $this->client_send('DATA' . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply, 0, 3);

    if($this->do_debug >= 2) {
      $this->edebug('SMTP -> FROM SERVER:' . $rply);
    }

    if($code != 354) {
      $this->error =
        array('error' => 'DATA command not accepted from server',
              'smtp_code' => $code,
              'smtp_msg' => substr($rply, 4));
      if($this->do_debug >= 1) {
        $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
      }
      return false;
    }

    /* the server is ready to accept data!
     * according to rfc 821 we should not send more than 1000
     * including the CRLF
     * characters on a single line so we will break the data up
     * into lines by \r and/or \n then if needed we will break
     * each of those into smaller lines to fit within the limit.
     * in addition we will be looking for lines that start with
     * a period '.' and append and additional period '.' to that
     * line. NOTE: this does not count towards limit.
     */

    // normalize the line breaks so we know the explode works
    $msg_data = str_replace("\r\n", "\n", $msg_data);
    $msg_data = str_replace("\r", "\n", $msg_data);
    $lines = explode("\n", $msg_data);

    /* we need to find a good way to determine is headers are
     * in the msg_data or if it is a straight msg body
     * currently I am assuming rfc 822 definitions of msg headers
     * and if the first field of the first line (':' sperated)
     * does not contain a space then it _should_ be a header
     * and we can process all lines before a blank "" line as
     * headers.
     */

    $field = substr($lines[0], 0, strpos($lines[0], ':'));
    $in_headers = false;
    if(!empty($field) && !strstr($field, ' ')) {
      $in_headers = true;
    }

    $max_line_length = 998; // used below; set here for ease in change

    while(list(, $line) = @each($lines)) {
      $lines_out = null;
      if($line == '' && $in_headers) {
        $in_headers = false;
      }
      // ok we need to break this line up into several smaller lines
      while(strlen($line) > $max_line_length) {
        $pos = strrpos(substr($line, 0, $max_line_length), ' ');

        // Patch to fix DOS attack
        if(!$pos) {
          $pos = $max_line_length - 1;
          $lines_out[] = substr($line, 0, $pos);
          $line = substr($line, $pos);
        } else {
          $lines_out[] = substr($line, 0, $pos);
          $line = substr($line, $pos + 1);
        }

        /* if processing headers add a LWSP-char to the front of new line
         * rfc 822 on long msg headers
         */
        if($in_headers) {
          $line = "\t" . $line;
        }
      }
      $lines_out[] = $line;

      // send the lines to the server
      while(list(, $line_out) = @each($lines_out)) {
        if(strlen($line_out) > 0)
        {
          if(substr($line_out, 0, 1) == '.') {
            $line_out = '.' . $line_out;
          }
        }
        $this->client_send($line_out . $this->CRLF);
      }
    }

    // message data has been sent
    $this->client_send($this->CRLF . '.' . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply, 0, 3);

    if($this->do_debug >= 2) {
      $this->edebug('SMTP -> FROM SERVER:' . $rply);
    }

    if($code != 250) {
      $this->error =
        array('error' => 'DATA not accepted from server',
              'smtp_code' => $code,
              'smtp_msg' => substr($rply, 4));
      if($this->do_debug >= 1) {
        $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
      }
      return false;
    }
    return true;
  }

  /**
   * Sends the HELO command to the smtp server.
   * This makes sure that we and the server are in
   * the same known state.
   *
   * Implements from rfc 821: HELO <SP> <domain> <CRLF>
   *
   * SMTP CODE SUCCESS: 250
   * SMTP CODE ERROR  : 500, 501, 504, 421
   * @access public
   * @param string $host
   * @return bool
   */
  public function Hello($host = '') {
    $this->error = null; // so no confusion is caused

    if(!$this->connected()) {
      $this->error = array(
            'error' => 'Called Hello() without being connected');
      return false;
    }

    // if hostname for HELO was not specified send default
    if(empty($host)) {
      // determine appropriate default to send to server
      $host = 'localhost';
    }

    // Send extended hello first (RFC 2821)
    if(!$this->SendHello('EHLO', $host)) {
      if(!$this->SendHello('HELO', $host)) {
        return false;
      }
    }

    return true;
  }

  /**
   * Sends a HELO/EHLO command.
   * @access protected
   * @param string $hello
   * @param string $host
   * @return bool
   */
  protected function SendHello($hello, $host) {
    $this->client_send($hello . ' ' . $host . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply, 0, 3);

    if($this->do_debug >= 2) {
      $this->edebug('SMTP -> FROM SERVER: ' . $rply);
    }

    if($code != 250) {
      $this->error =
        array('error' => $hello . ' not accepted from server',
              'smtp_code' => $code,
              'smtp_msg' => substr($rply, 4));
      if($this->do_debug >= 1) {
        $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
      }
      return false;
    }

    $this->helo_rply = $rply;

    return true;
  }

  /**
   * Starts a mail transaction from the email address specified in
   * $from. Returns true if successful or false otherwise. If True
   * the mail transaction is started and then one or more Recipient
   * commands may be called followed by a Data command.
   *
   * Implements rfc 821: MAIL <SP> FROM:<reverse-path> <CRLF>
   *
   * SMTP CODE SUCCESS: 250
   * SMTP CODE SUCCESS: 552, 451, 452
   * SMTP CODE SUCCESS: 500, 501, 421
   * @access public
   * @param string $from
   * @return bool
   */
  public function Mail($from) {
    $this->error = null; // so no confusion is caused

    if(!$this->connected()) {
      $this->error = array(
              'error' => 'Called Mail() without being connected');
      return false;
    }

    $useVerp = ($this->do_verp ? ' XVERP' : '');
    $this->client_send('MAIL FROM:<' . $from . '>' . $useVerp . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply, 0, 3);

    if($this->do_debug >= 2) {
      $this->edebug('SMTP -> FROM SERVER:' . $rply);
    }

    if($code != 250) {
      $this->error =
        array('error' => 'MAIL not accepted from server',
              'smtp_code' => $code,
              'smtp_msg' => substr($rply, 4));
      if($this->do_debug >= 1) {
        $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
      }
      return false;
    }
    return true;
  }

  /**
   * Sends the quit command to the server and then closes the socket
   * if there is no error or the $close_on_error argument is true.
   *
   * Implements from rfc 821: QUIT <CRLF>
   *
   * SMTP CODE SUCCESS: 221
   * SMTP CODE ERROR  : 500
   * @access public
   * @param bool $close_on_error
   * @return bool
   */
  public function Quit($close_on_error = true) {
    $this->error = null; // so there is no confusion

    if(!$this->connected()) {
      $this->error = array(
              'error' => 'Called Quit() without being connected');
      return false;
    }

    // send the quit command to the server
    $this->client_send('quit' . $this->CRLF);

    // get any good-bye messages
    $byemsg = $this->get_lines();

    if($this->do_debug >= 2) {
      $this->edebug('SMTP -> FROM SERVER:' . $byemsg);
    }

    $rval = true;
    $e = null;

    $code = substr($byemsg, 0, 3);
    if($code != 221) {
      // use e as a tmp var cause Close will overwrite $this->error
      $e = array('error' => 'SMTP server rejected quit command',
                 'smtp_code' => $code,
                 'smtp_rply' => substr($byemsg, 4));
      $rval = false;
      if($this->do_debug >= 1) {
        $this->edebug('SMTP -> ERROR: ' . $e['error'] . ': ' . $byemsg);
      }
    }

    if(empty($e) || $close_on_error) {
      $this->Close();
    }

    return $rval;
  }

  /**
   * Sends the command RCPT to the SMTP server with the TO: argument of $to.
   * Returns true if the recipient was accepted false if it was rejected.
   *
   * Implements from rfc 821: RCPT <SP> TO:<forward-path> <CRLF>
   *
   * SMTP CODE SUCCESS: 250, 251
   * SMTP CODE FAILURE: 550, 551, 552, 553, 450, 451, 452
   * SMTP CODE ERROR  : 500, 501, 503, 421
   * @access public
   * @param string $to
   * @return bool
   */
  public function Recipient($to) {
    $this->error = null; // so no confusion is caused

    if(!$this->connected()) {
      $this->error = array(
              'error' => 'Called Recipient() without being connected');
      return false;
    }

    $this->client_send('RCPT TO:<' . $to . '>' . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply, 0, 3);

    if($this->do_debug >= 2) {
      $this->edebug('SMTP -> FROM SERVER:' . $rply);
    }

    if($code != 250 && $code != 251) {
      $this->error =
        array('error' => 'RCPT not accepted from server',
              'smtp_code' => $code,
              'smtp_msg' => substr($rply, 4));
      if($this->do_debug >= 1) {
        $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
      }
      return false;
    }
    return true;
  }

  /**
   * Sends the RSET command to abort and transaction that is
   * currently in progress. Returns true if successful false
   * otherwise.
   *
   * Implements rfc 821: RSET <CRLF>
   *
   * SMTP CODE SUCCESS: 250
   * SMTP CODE ERROR  : 500, 501, 504, 421
   * @access public
   * @return bool
   */
  public function Reset() {
    $this->error = null; // so no confusion is caused

    if(!$this->connected()) {
      $this->error = array('error' => 'Called Reset() without being connected');
      return false;
    }

    $this->client_send('RSET' . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply, 0, 3);

    if($this->do_debug >= 2) {
      $this->edebug('SMTP -> FROM SERVER:' . $rply);
    }

    if($code != 250) {
      $this->error =
        array('error' => 'RSET failed',
              'smtp_code' => $code,
              'smtp_msg' => substr($rply, 4));
      if($this->do_debug >= 1) {
        $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
      }
      return false;
    }

    return true;
  }

  /**
   * Starts a mail transaction from the email address specified in
   * $from. Returns true if successful or false otherwise. If True
   * the mail transaction is started and then one or more Recipient
   * commands may be called followed by a Data command. This command
   * will send the message to the users terminal if they are logged
   * in and send them an email.
   *
   * Implements rfc 821: SAML <SP> FROM:<reverse-path> <CRLF>
   *
   * SMTP CODE SUCCESS: 250
   * SMTP CODE SUCCESS: 552, 451, 452
   * SMTP CODE SUCCESS: 500, 501, 502, 421
   * @access public
   * @param string $from
   * @return bool
   */
  public function SendAndMail($from) {
    $this->error = null; // so no confusion is caused

    if(!$this->connected()) {
      $this->error = array(
          'error' => 'Called SendAndMail() without being connected');
      return false;
    }

    $this->client_send('SAML FROM:' . $from . $this->CRLF);

    $rply = $this->get_lines();
    $code = substr($rply, 0, 3);

    if($this->do_debug >= 2) {
      $this->edebug('SMTP -> FROM SERVER:' . $rply);
    }

    if($code != 250) {
      $this->error =
        array('error' => 'SAML not accepted from server',
              'smtp_code' => $code,
              'smtp_msg' => substr($rply, 4));
      if($this->do_debug >= 1) {
        $this->edebug('SMTP -> ERROR: ' . $this->error['error'] . ': ' . $rply);
      }
      return false;
    }
    return true;
  }

  /**
   * This is an optional command for SMTP that this class does not
   * support. This method is here to make the RFC821 Definition
   * complete for this class and __may__ be implimented in the future
   *
   * Implements from rfc 821: TURN <CRLF>
   *
   * SMTP CODE SUCCESS: 250
   * SMTP CODE FAILURE: 502
   * SMTP CODE ERROR  : 500, 503
   * @access public
   * @return bool
   */
  public function Turn() {
    $this->error = array('error' => 'This method, TURN, of the SMTP '.
                                    'is not implemented');
    if($this->do_debug >= 1) {
      $this->edebug('SMTP -> NOTICE: ' . $this->error['error']);
    }
    return false;
  }

  /**
  * Sends data to the server
  * @param string $data
  * @access public
  * @return Integer number of bytes sent to the server or FALSE on error
  */
  public function client_send($data) {
      if ($this->do_debug >= 1) {
          $this->edebug("CLIENT -> SMTP: $data");
      }
      return fwrite($this->smtp_conn, $data);
  }

  /**
  * Get the current error
  * @access public
  * @return array
  */
  public function getError() {
    return $this->error;
  }

  /////////////////////////////////////////////////
  // INTERNAL FUNCTIONS
  /////////////////////////////////////////////////

  /**
   * Read in as many lines as possible
   * either before eof or socket timeout occurs on the operation.
   * With SMTP we can tell if we have more lines to read if the
   * 4th character is '-' symbol. If it is a space then we don't
   * need to read anything else.
   * @access protected
   * @return string
   */
  protected function get_lines() {
    $data = '';
    $endtime = 0;
    /* If for some reason the fp is bad, don't inf loop */
    if (!is_resource($this->smtp_conn)) {
      return $data;
    }
    stream_set_timeout($this->smtp_conn, $this->Timeout);
    if ($this->Timelimit > 0) {
      $endtime = time() + $this->Timelimit;
    }
    while(is_resource($this->smtp_conn) && !feof($this->smtp_conn)) {
      $str = @fgets($this->smtp_conn, 515);
      if($this->do_debug >= 4) {
        $this->edebug("SMTP -> get_lines(): \$data was \"$data\"");
        $this->edebug("SMTP -> get_lines(): \$str is \"$str\"");
      }
      $data .= $str;
      if($this->do_debug >= 4) {
        $this->edebug("SMTP -> get_lines(): \$data is \"$data\"");
      }
      // if 4th character is a space, we are done reading, break the loop
      if(substr($str, 3, 1) == ' ') { break; }
      // Timed-out? Log and break
      $info = stream_get_meta_data($this->smtp_conn);
      if ($info['timed_out']) {
        if($this->do_debug >= 4) {
          $this->edebug('SMTP -> get_lines(): timed-out (' . $this->Timeout . ' seconds)');
        }
        break;
      }
      // Now check if reads took too long
      if ($endtime) {
        if (time() > $endtime) {
          if($this->do_debug >= 4) {
            $this->edebug('SMTP -> get_lines(): timelimit reached (' . $this->Timelimit . ' seconds)');
          }
          break;
        }
      }
    }
    return $data;
  }

}
