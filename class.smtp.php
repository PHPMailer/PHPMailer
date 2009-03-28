<?php
/*~ class.smtp.php
.---------------------------------------------------------------------------.
|  Software: PHPMailer - PHP email class                                    |
|   Version: 5.0.0                                                          |
|   Contact: via sourceforge.net support pages (also www.codeworxtech.com)  |
|      Info: http://phpmailer.sourceforge.net                               |
|   Support: http://sourceforge.net/projects/phpmailer/                     |
| ------------------------------------------------------------------------- |
|    Author: Andy Prevost (project admininistrator)                         |
|    Author: Brent R. Matzelle (original founder)                           |
| Copyright (c) 2004-2009, Andy Prevost. All Rights Reserved.               |
| Copyright (c) 2001-2003, Brent R. Matzelle                                |
| ------------------------------------------------------------------------- |
|   License: Distributed under the Lesser General Public License (LGPL)     |
|            http://www.gnu.org/copyleft/lesser.html                        |
| This program is distributed in the hope that it will be useful - WITHOUT  |
| ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or     |
| FITNESS FOR A PARTICULAR PURPOSE.                                         |
| ------------------------------------------------------------------------- |
| We offer a number of paid services (www.codeworxtech.com):                |
| - Web Hosting on highly optimized fast and secure servers                 |
| - Technology Consulting                                                   |
| - Oursourcing (highly qualified programmers and graphic designers)        |
'---------------------------------------------------------------------------'

/**
 * SMTP is rfc 821 compliant and implements all the rfc 821 SMTP
 * commands except TURN which will always return a not implemented
 * error. SMTP also provides some utility methods for sending mail
 * to an SMTP server.
 * @package PHPMailer
 * @author Andy Prevost
 */

class smtpException extends Exception {
  public function errorMessage() {
    $errorMsg = '<strong>' . $this->getMessage() . "</strong><br />";
    return $errorMsg;
  }
}

class SMTP {
  /**
   *  SMTP server port
   *  @var int
   */
  public $SMTP_PORT = 25;

  /**
   *  SMTP reply line ending
   *  @var string
   */
  public $CRLF = "\r\n";

  /**
   *  Sets whether debugging is turned on
   *  @var bool
   */
  public $do_debug;       // the level of debug to perform

  /**
   *  Sets VERP use on/off (default is off)
   *  @var bool
   */
  public $do_verp = false;

  /**
   *  Sets error message to pass to PHPMailer
   *  @var string
   */
  public $smtpErrorMessage = array();

  /**#@+
   * @access private
   */
  private $smtp_conn;      // the socket to the server
  private $error;          // error if any on the last call
  private $helo_rply;      // the reply the server sent to us for HELO
  private $exceptions = false; //Whether to throw external exceptions
  /**#@-*/

  /**
   * Initialize the class so that the data is in a known state.
   * @access public
   * @return void
   */
  public function __construct($exceptions = false) {
	$this->exceptions = ($exceptions == true);
	$this->smtp_conn = 0;
    $this->error     = array();
    $this->helo_rply = NULL;
    $this->do_debug  = 0;
  }

  /*************************************************************
   *                    CONNECTION FUNCTIONS                  *
   ***********************************************************/

  /**
   * Connect to the server specified on the port specified.
   * If the port is not specified use the default SMTP_PORT.
   * If tval is specified then a connection will try and be
   * established with the server for that number of seconds.
   * If tval is not specified the default is 30 seconds to
   * try on the connection.
   *
   * SMTP CODE SUCCESS: 220
   * SMTP CODE FAILURE: 421
   * @access public
   * @return bool
   */
  public function Connect($host,$port=0,$tval=30) {
    $this->error = NULL;

    try {
    /* make sure we are NOT connected */
      if ($this->connected() ) {
        /* already connected! - throw exception that we are already connected */
        throw new smtpException("Already connected to a server");
      }

    if ( empty($port) ) {
      $port = $this->SMTP_PORT;
    }

    /* connect to the smtp server */
      $this->smtp_conn = @fsockopen($host,    // the host of the server
                                   $port,    // the port to use
                                   $errno,   // error number if any
                                   $errstr,  // error message if any
                                   $tval);   // give up after ? secs - default is 30 seconds
      /* verify we connected properly */
      if ( $this->smtp_conn === false || $this->smtp_conn === 0 ) {
        $displayErrorString = '';
        if ( empty($errstr) ) {
          $smtp_err_str = null;
          switch($errno){
            case -3:  $smtp_err_str = "Socket creation failed"; break;
            case -4:  $smtp_err_str = "DNS lookup failure"; break;
            case -5:  $smtp_err_str = "Connection refused or timed out"; break;
            case 1:   $smtp_err_str = "Invalid host"; break;
            case 111: $smtp_err_str = "Connection refused"; break;
            case 113: $smtp_err_str = "No route to host"; break;
            case 110: $smtp_err_str = "Connection timed out"; break;
            case 104: $smtp_err_str = "Connection reset by client"; break;
            default:  $smtp_err_str = "Unknown: connection failed"; break;
          }
          if ( !empty($smtp_err_str) ) {
            $displayErrorString = ' (' . $smtp_err_str . ')';
          }
        } elseif ( !empty($errstr) ) {
          $displayErrorString = ' (' . $errstr . ')';
        }
        $error = 'Failed to connect to server. Error: ' . $errno . $displayErrorString;
        if($this->do_debug >= 1) {
          echo "SMTP -> ERROR: " . $this->error . '<br />' . $this->CRLF;
        }
        throw new smtpException($error);
      }
     /* sometimes the SMTP server takes a little longer to respond
     * so we will give it a longer timeout for the first read
     */
      if ( !stream_set_timeout($this->smtp_conn, $tval) ) {
        throw new smtpException('Extended time out failed.');
      }
 
    /* get any announcement stuff */
    $announce = $this->get_lines();
    if ( $this->do_debug >= 2 ) {
      echo "SMTP -> FROM SERVER:" . $this->CRLF . $announce . '<br />';
    }

    return true;
   }
    catch (smtpException $e) {
		$this->error = $e->getMessage();
		$this->smtpErrorMessage[] = $e->errorMessage();
		if ($this->exceptions) {
			throw $e;
		}
		echo $e->errorMessage();
    }
	return false;
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
    $this->error = null;

    try {
      if (!$this->connected()) {
        throw new smtpException('Called StartTLS() without being connected.');
      }
      fputs($this->smtp_conn,"STARTTLS" . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . $this->CRLF . '<br />' . $rply . '<br />';
      }
      if ( $code != 220 ) {
        throw new smtpException('STARTTLS not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')');
      }
 
    //Begin encrypted connection
    if (!stream_socket_enable_crypto($this->smtp_conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
		throw new smtpException('Failed to enable crypto on socket');
    }
   } catch (smtpException $e) {
		$this->error = $e->getMessage();
		$this->smtpErrorMessage[] = $e->errorMessage();
		if ($this->exceptions) {
			throw $e;
		}
		echo $e->errorMessage();
		if ( $this->do_debug >= 1 ) {
			echo "SMTP -> ERROR: " . $e->errorMessage() . '<br />' . $this->CRLF;
		}
		return false;
	}

    return true;
  }

  /**
   * Performs SMTP authentication.  Must be run after running the
   * Hello() method.  Returns true if successfully authenticated.
   * @access public
   * @return bool
   */
  public function Authenticate($username, $password) {
    $this->error = null;

    // Start authentication
    try {
		//Send AUTH command
      fputs($this->smtp_conn,"AUTH LOGIN" . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $code != 334 ) {
        throw new smtpException('AUTH not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')');
      }
	  
	  //Send username
      fputs($this->smtp_conn, base64_encode($username) . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $code != 334 ) {
        throw new smtpException('Username not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')');
      }

    // Send encoded password
      fputs($this->smtp_conn, base64_encode($password) . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $code != 235 ) {
        throw new smtpException('Password not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')');
      }
   } catch (smtpException $e) {
		$this->error = $e->getMessage();
		$this->smtpErrorMessage[] = $e->errorMessage();
		if ($this->exceptions) {
			throw $e;
		}
		echo $e->errorMessage();
		if ( $this->do_debug >= 1 ) {
			echo "SMTP -> ERROR: " . $e->errorMessage() . '<br />' . $this->CRLF;
		}
		return false;
	}
    return true;
  }

  /**
   * Returns true if connected to a server otherwise false
   * @access public
   * @return bool
   */
  public function Connected() {
    $this->error = null;

    try {
      if ( $this->smtp_conn !== false && $this->smtp_conn != 0  ) {
        $sock_status = socket_get_status($this->smtp_conn);
        if ( $sock_status["eof"] ) {
          // odd situation - socket is valid but not connected anymore
          throw new smtpException('NOTICE: EOF caught while checking if connected');
        }
      } else {
        return false;
      }
	  } catch (smtpException $e) {
		$this->Close();
		$this->error = $e->getMessage();
		$this->smtpErrorMessage[] = $e->errorMessage();
		if ($this->exceptions) {
			throw $e;
		}
		echo $e->errorMessage();
		if ( $this->do_debug >= 1 ) {
			echo "SMTP -> ERROR: " . $e->errorMessage() . '<br />' . $this->CRLF;
		}
		return false;
	}
    
    return true; // everything looks good
  }

  /**
   * Closes the socket and cleans up the state of the class.
   * It is not considered good to use this function without
   * first trying to use QUIT.
   * @access public
   * @return void
   */
  public function Close() {
    $this->error = null;

    $this->helo_rply = null;
    if ( !empty($this->smtp_conn) ) {
      // close the connection and cleanup
      fclose($this->smtp_conn);
      $this->smtp_conn = 0;
    }
  }

  /***************************************************************
   *                        SMTP COMMANDS                       *
   *************************************************************/

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
   *     SMTP CODE FAILURE: 552,554,451,452
   * SMTP CODE FAILURE: 451,554
   * SMTP CODE ERROR  : 500,501,503,421
   * @access public
   * @return bool
   */
  public function Data($msg_data) {
    $this->error = null;

    try {
      if (!$this->connected()) {
        throw new smtpException('ERROR: Called Data() without being connected');
      }
   
      fputs($this->smtp_conn,"DATA" . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . '<br />' . $this->CRLF . $rply . '<br />' . $this->CRLF;
      }
      if ( $code != 354 ) {
        throw new smtpException('DATA command not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')');
      }

    /* the server is ready to accept data!
     * according to rfc 821 we should not send more than 1000
     * including the CRLF
     * characters on a single line so we will break the data up
     * into lines by \r and/or \n then if needed we will break
     * each of those into smaller lines to fit within the limit.
     * in addition we will be looking for lines that start with
     * a period '.' and append and additional period '.' to that
     * line. NOTE: this does not count towards are limit.
     */

    // normalize the line breaks so we know the explode works
    $msg_data = str_replace("\r\n","\n",$msg_data);
    $msg_data = str_replace("\r","\n",$msg_data);
    $lines = explode("\n",$msg_data);

    /* we need to find a good way to determine if headers are
     * in the msg_data or if it is a straight msg body
     * currently assuming rfc 822 definitions of msg headers
     * and if the first field of the first line (':' separated)
     * does not contain a space then it _should_ be a header
     * and we can process all lines before a blank "" line as
     * headers.
     */
    $field = substr($lines[0],0,strpos($lines[0],":"));
    $in_headers = false;
    if ( !empty($field) && !strstr($field," ") ) {
      $in_headers = true;
    }

    $max_line_length = 998; // used below; set here for ease in change

    while(list(,$line) = @each($lines)) {
      $lines_out = null;
      if ( $line == "" && $in_headers ) {
        $in_headers = false;
      }
      // ok we need to break this line up into several smaller lines
      while(strlen($line) > $max_line_length) {
        $pos = strrpos(substr($line,0,$max_line_length)," ");

        // Patch to fix DOS attack
        if ( !$pos ) {
          $pos = $max_line_length - 1;
          $lines_out[] = substr($line,0,$pos);
          $line = substr($line,$pos);
        } else {
          $lines_out[] = substr($line,0,$pos);
          $line = substr($line,$pos + 1);
        }

        /* if we are processing headers we need to
         * add a LWSP-char to the front of the new line
         * rfc 822 on long msg headers
         */
        if ( $in_headers ) {
          $line = "\t" . $line;
        }
      }
      $lines_out[] = $line;

      // now send the lines to the server
      while(list(,$line_out) = @each($lines_out)) {
        if ( strlen($line_out) > 0 ) {
          if ( substr($line_out, 0, 1) == "." ) {
            $line_out = "." . $line_out;
          }
        }
        fputs($this->smtp_conn,$line_out . $this->CRLF);
      }
    }

      // message data has been sent so lets end
      fputs($this->smtp_conn, $this->CRLF . "." . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . '<br />' . $this->CRLF . $rply . '<br />' . $this->CRLF;
      }
      if ( $code != 250 ) {
         throw new smtpException('DATA not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')');
      }
 	  } catch (smtpException $e) {
		$this->Close();
		$this->error = $e->getMessage();
		$this->smtpErrorMessage[] = $e->errorMessage();
		if ($this->exceptions) {
			throw $e;
		}
		echo $e->errorMessage();
		if ( $this->do_debug >= 1 ) {
         echo "SMTP -> ERROR: " . $this->error . " (" . $rply . ')<br />' . $this->CRLF;
		}
		return false;
	}
    return true;
  }

  /**
   * Expand takes the name and asks the server to list all the
   * people who are members of the _list_. Expand will return
   * back and array of the result or false if an error occurs.
   * Each value in the array returned has the format of:
   *     [ <full-name> <sp> ] <path>
   * The definition of <path> is defined in rfc 821
   *
   * Implements rfc 821: EXPN <SP> <string> <CRLF>
   *
   * SMTP CODE SUCCESS: 250
   * SMTP CODE FAILURE: 550
   * SMTP CODE ERROR  : 500,501,502,504,421
   * @access public
   * @return string array
   */
  public function Expand($name) {
    $this->error = null;

    try {
      if (!$this->connected()) {
        throw new smtpException('ERROR: Called Expand() without being connected');
      }
      fputs($this->smtp_conn,"EXPN " . $name . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . '<br />' . $this->CRLF . $rply . '<br />' . $this->CRLF;
      }
      if ( $code != 250 ) {
        throw new smtpException('EXPN not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')');
      }

    // parse the reply and place in our array to return to user
    $entries = explode($this->CRLF,$rply);
    while(list(,$l) = @each($entries)) {
      $list[] = substr($l,4);
    }

    return $list;
 	  } catch (smtpException $e) {
		$this->Close();
		$this->error = $e->getMessage();
		$this->smtpErrorMessage[] = $e->errorMessage();
		if ($this->exceptions) {
			throw $e;
		}
		echo $e->errorMessage();
		if ( $this->do_debug >= 1 ) {
         echo "SMTP -> ERROR: " . $this->error . " (" . $rply . ')<br />' . $this->CRLF;
		}
		return false;
	}
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
   * @return bool
   */
  public function Hello($host="") {
    $this->error = NULL;

    try {
      if ( !$this->connected() ) {
        throw new smtpException('ERROR: Called Hello() without being connected');
      }
    // if hostname for HELO was not specified determine a suitable one to send
    if ( empty($host) ) {
      // default to send to the server
      $host = "localhost";
    }
       // Send extended hello first (RFC 2821)
      if ( !$this->SendHello("EHLO", $host) ) {
        if ( !$this->SendHello("HELO", $host) ) {
          throw new smtpException('ERROR: EHLO and/or HELO not accepted by server');
        }
      }

    return true;
	  } catch (smtpException $e) {
		$this->Close();
		$this->error = $e->getMessage();
		$this->smtpErrorMessage[] = $e->errorMessage();
		if ($this->exceptions) {
			throw $e;
		}
		echo $e->errorMessage();
		return false;
	}
  }

  /**
   * Sends a HELO/EHLO command.
   * @access private
   * @return bool
   */
  private function SendHello($hello, $host) {
    $this->error = null;

    try {
      fputs($this->smtp_conn, $hello . " " . $host . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER: " . '<br />' . $this->CRLF . $rply . '<br />' . $this->CRLF;
      }
      if ( $code != 250 ) {
        throw new smtpException($hello . ' not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')');
      }
    $this->helo_rply = $rply;

    return true;
  	  } catch (smtpException $e) {
		$this->Close();
		$this->error = $e->getMessage();
		$this->smtpErrorMessage[] = $e->errorMessage();
		if ($this->exceptions) {
			throw $e;
		}
		echo $e->errorMessage();
        if ( $this->do_debug >= 1 ) {
          echo "SMTP -> ERROR: " . $this->error . " (" . $rply . ')<br />' . $this->CRLF;
        }
		return false;
	}
}

  /**
   * Gets help information on the keyword specified. If the keyword
   * is not specified then returns generic help, ussually contianing
   * A list of keywords that help is available on. This function
   * returns the results back to the user. It is up to the user to
   * handle the returned data. If an error occurs then false is
   * returned with $this->error set appropiately.
   *
   * Implements rfc 821: HELP [ <SP> <string> ] <CRLF>
   *
   * SMTP CODE SUCCESS: 211,214
   * SMTP CODE ERROR  : 500,501,502,504,421
   * @access public
   * @return string
   */
  public function Help($keyword="") {
    $this->error = null;

    try {
      if ( !$this->connected() ) {
        $this->error = 'ERROR: Called Help() without being connected';
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    $extra = "";
    if ( !empty($keyword) ) {
      $extra = " " . $keyword;
    }

    try {
      fputs($this->smtp_conn,"HELP" . $extra . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . $this->CRLF . $rply;
      }
      if ( $code != 211 && $code != 214 ) {
        $this->error = 'HELP not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')';
        if ( $this->do_debug >= 1 ) {
          echo "SMTP -> ERROR: " . $this->error . " (" . $rply . ')<br />' . $this->CRLF;
        }
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    return $rply;
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
   * SMTP CODE SUCCESS: 552,451,452
   * SMTP CODE SUCCESS: 500,501,421
   * @access public
   * @return bool
   */
  public function Mail($from) {
    $this->error = null;

    try {
      if ( !$this->connected() ) {
        $this->error = 'ERROR: Called Mail() without being connected';
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    try {
      $useVerp = ($this->do_verp ? "XVERP" : "");
      fputs($this->smtp_conn,"MAIL FROM:<" . $from . ">" . $useVerp . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . '<br />' . $this->CRLF . $rply . '<br />' . $this->CRLF;
      }
      if ( $code != 250 ) {
        $this->error = 'MAIL not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')';
        if ( $this->do_debug >= 1 ) {
          echo "SMTP -> ERROR: " . $this->error . " (" . $rply . ')<br />' . $this->CRLF;
        }
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }
    return true;
  }

  /**
   * Sends the command NOOP to the SMTP server.
   *
   * Implements from rfc 821: NOOP <CRLF>
   *
   * SMTP CODE SUCCESS: 250
   * SMTP CODE ERROR  : 500, 421
   * @access public
   * @return bool
   */
  public function Noop() {
    $this->error = null;

    try {
      if ( !$this->connected() ) {
        $this->error = 'ERROR: Called Noop() without being connected';
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    try {
      fputs($this->smtp_conn,"NOOP" . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . '<br />' . $this->CRLF . $rply . '<br />' . $this->CRLF;
      }
      if ( $code != 250 ) {
        $this->error = 'NOOP not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')';
        if ( $this->do_debug >= 1 ) {
          echo "SMTP -> ERROR: " . $this->error . " (" . $rply . '<br />' . $this->CRLF;
        }
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
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
   * @return bool
   */
  public function Quit($close_on_error=true) {
    $this->error = null;

    try {
      if ( !$this->connected() ) {
        $this->error = 'ERROR: Called Quit() without being connected';
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    try {
      // send the quit command to the server
      fputs($this->smtp_conn,"quit" . $this->CRLF);

      // get any good-bye messages
      $byemsg = $this->get_lines();

      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . '<br />' . $this->CRLF . $byemsg . '<br />' . $this->CRLF;
      }

      $rval = true;
      $e = null;

      $code = substr($byemsg,0,3);
      if ( $code != 221 ) {
        $rval = false;
        $this->error = 'QUIT command not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')';
        $e = $this->error;
        if ( $this->do_debug >= 1 ) {
          echo "SMTP -> ERROR: " . $e["error"] . " (" . $byemsg . ')<br />' . $this->CRLF;
        }
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    if ( empty($e) || $close_on_error ) {
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
   * SMTP CODE SUCCESS: 250,251
   * SMTP CODE FAILURE: 550,551,552,553,450,451,452
   * SMTP CODE ERROR  : 500,501,503,421
   * @access public
   * @return bool
   */
  public function Recipient($to) {
    $this->error = null;

    try {
      if ( !$this->connected() ) {
        $this->error = 'ERROR: Called Recipient() without being connected';
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    try {
      fputs($this->smtp_conn,"RCPT TO:<" . $to . ">" . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . '<br />' . $this->CRLF . $rply . '<br />' . $this->CRLF;
      }
      if ( $code != 250 && $code != 251 ) {
        $this->error = 'RCPT command not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')';
        if ( $this->do_debug >= 1 ) {
          echo "SMTP -> ERROR: " . $this->error . " (" . $rply . ')<br />' . $this->CRLF;
        }
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
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
   * SMTP CODE ERROR  : 500,501,504,421
   * @access public
   * @return bool
   */
  public function Reset() {
    $this->error = null;

    try {
      if ( !$this->connected() ) {
        $this->error = 'ERROR: Called Reset() without being connected';
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    try {
      fputs($this->smtp_conn,"RSET" . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . '<br />' . $this->CRLF . $rply . '<br />' . $this->CRLF;
      }
      if ( $code != 250 ) {
        $this->error = 'RSET failed. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')';
        if ( $this->do_debug >= 1 ) {
          echo "SMTP -> ERROR: " . $this->error . " (" . $rply . ')<br />' . $this->CRLF;
        }
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
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
   * in.
   *
   * Implements rfc 821: SEND <SP> FROM:<reverse-path> <CRLF>
   *
   * SMTP CODE SUCCESS: 250
   * SMTP CODE SUCCESS: 552,451,452
   * SMTP CODE SUCCESS: 500,501,502,421
   * @access public
   * @return bool
   */
  public function Send($from) {
    $this->error = null;

    try {
      if ( !$this->connected() ) {
        $this->error = 'ERROR: Called Send() without being connected';
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    try {
      fputs($this->smtp_conn,"SEND FROM:" . $from . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . '<br />' . $this->CRLF . $rply . '<br />' . $this->CRLF;
      }
      if ( $code != 250 ) {
        $this->error = 'SEND not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')';
        if ( $this->do_debug >= 1 ) {
          echo "SMTP -> ERROR: " . $this->error . " (" . $rply . ')<br />' . $this->CRLF;
        }
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
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
   * SMTP CODE SUCCESS: 552,451,452
   * SMTP CODE SUCCESS: 500,501,502,421
   * @access public
   * @return bool
   */
  public function SendAndMail($from) {
    $this->error = null;

    try {
      if ( !$this->connected() ) {
        $this->error = 'ERROR: Called SendAndMail() without being connected';
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    try {
      fputs($this->smtp_conn,"SAML FROM:" . $from . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . '<br />' . $this->CRLF . $rply . '<br />' . $this->CRLF;
      }
      if ( $code != 250 ) {
        $this->error = 'SAML not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')';
        if ( $this->do_debug >= 1 ) {
          echo "SMTP -> ERROR: " . $this->error . " (" . $rply . ')<br />' . $this->CRLF;
        }
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
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
   * in or mail it to them if they are not.
   *
   * Implements rfc 821: SOML <SP> FROM:<reverse-path> <CRLF>
   *
   * SMTP CODE SUCCESS: 250
   * SMTP CODE SUCCESS: 552,451,452
   * SMTP CODE SUCCESS: 500,501,502,421
   * @access public
   * @return bool
   */
  public function SendOrMail($from) {
    $this->error = null;

    try {
      if ( !$this->connected() ) {
        $this->error = 'ERROR: Called SendOrMail() without being connected';
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    try {
      fputs($this->smtp_conn,"SOML FROM:" . $from . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . '<br />' . $this->CRLF . $rply . '<br />' . $this->CRLF;
      }
      if ( $code != 250 ) {
        $this->error = 'SOML not accepted. Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')';
        if ( $this->do_debug >= 1 ) {
          echo "SMTP -> ERROR: " . $this->error . " (" . $rply . ')<br />' . $this->CRLF;
        }
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    return true;
  }

  /**
   * Verifies that the name is recognized by the server.
   * Returns false if the name could not be verified otherwise
   * the response from the server is returned.
   *
   * Implements rfc 821: VRFY <SP> <string> <CRLF>
   *
   * SMTP CODE SUCCESS: 250,251
   * SMTP CODE FAILURE: 550,551,553
   * SMTP CODE ERROR  : 500,501,502,421
   * @access public
   * @return int
   */
  public function Verify($name) {
    $this->error = null;

    try {
      if ( !$this->connected() ) {
        $this->error = 'ERROR: Called Verify() without being connected';
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    try {
      fputs($this->smtp_conn,"VRFY " . $name . $this->CRLF);
      $rply = $this->get_lines();
      $code = substr($rply,0,3);
      if ( $this->do_debug >= 2 ) {
        echo "SMTP -> FROM SERVER:" . '<br />' . $this->CRLF . $rply . '<br />' . $this->CRLF;
      }
      if ( $code != 250 && $code != 251 ) {
        $this->error = "VRFY failed on name '$name' " . 'Server returned code: ' . $code . ' (' . trim(substr($rply,4)) . ')';
        if ( $this->do_debug >= 1 ) {
          echo "SMTP -> ERROR: " . $this->error . " (" . $rply . ')<br />' . $this->CRLF;
        }
        throw new smtpException($this->error);
      }
    } catch (smtpException $e) {
      $this->smtpErrorMessage[] = $e->errorMessage();
      echo $e->errorMessage();
      return false;
    }

    return $rply;
  }

  /*******************************************************************
   *                       INTERNAL FUNCTIONS                       *
   ******************************************************************/

  /**
   * Read in as many lines as possible
   * either before eof or socket timeout occurs on the operation.
   * With SMTP we can tell if we have more lines to read if the
   * 4th character is '-' symbol. If it is a space then we don't
   * need to read anything else.
   * @access private
   * @return string
   */
  private function get_lines() {
    $data = "";
    while($str = @fgets($this->smtp_conn,515)) {
      if ( $this->do_debug >= 4 ) {
        echo "SMTP -> get_lines(): \$data was \"$data\"" . '<br />' . $this->CRLF;
        echo "SMTP -> get_lines(): \$str is \"$str\"" . '<br />' . $this->CRLF;
      }
      $data .= $str;
      if ( $this->do_debug >= 4 ) {
        echo "SMTP -> get_lines(): \$data is \"$data\"" . '<br />' . $this->CRLF;
      }
      // if the 4th character is a space then we are done reading
      // so just break the loop
      if ( substr($str,3,1) == " " ) {
        break;
      }
    }
    return $data;
  }

}

?>