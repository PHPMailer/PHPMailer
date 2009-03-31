<html>
<head>
<title>PHPMailer - SMTP (Gmail) advanced test</title>
</head>
<body>

<?php

//error_reporting(E_ALL);
error_reporting(E_STRICT);

date_default_timezone_set('America/Toronto');

require_once('../class.phpmailer.php');
//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

class phpmailerAppException extends Exception {
  public function errorMessage() {
    $errorMsg = '<strong>' . $this->getMessage() . "</strong><br />";
    return $errorMsg;
  }
}

try {
  $address = "whoto@otherdomain.com";
  if (function_exists('filter_var')) { //Introduced in PHP 5.2
    if(filter_var($address, FILTER_VALIDATE_EMAIL) === FALSE) {
      throw new phpmailerAppException("Email address " . $to . " is invalid -- aborting!<br />");
    }
  } else {
    if ( preg_match('/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/', $address) === false ) {
      throw new phpmailerAppException("Email address " . $to . " is invalid -- aborting!<br />");
    }
  }

  $mail             = new PHPMailer();

  if ($body = file_get_contents('contents.html')) {
    $body             = eregi_replace("[\]",'',$body);
  } else {
    throw new phpmailerAppException("Cannot find file 'contents.html' -- aborting!<br />");
  }

  $mail->IsSMTP(); // telling the class to use SMTP
  $mail->Host       = "mail.yourdomain.com"; // SMTP server
  $mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
                                             // 1 = errors and messages
                                             // 2 = messages only
  $mail->SMTPAuth   = true;                  // enable SMTP authentication
  $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
  $mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
  $mail->Port       = 465;                   // set the SMTP port for the GMAIL server
  $mail->Username   = "yourusername@gmail.com";  // GMAIL username
  $mail->Password   = "yourpassword";            // GMAIL password

  $mail->From       = "name@yourdomain.com";
  $mail->FromName   = "First Last";
  $mail->AddReplyTo("name@yourdomain.com","First Last");

  $mail->Subject    = "PHPMailer Test Subject via smtp, advanced with no authentication";

  $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

  $mail->MsgHTML($body);

  $mail->AddAddress($address, "John Doe");

  $mail->AddAttachment("images/phpmailer.gif");      // attachment
  $mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

  if(!$mail->Send()) {
    throw new phpmailerAppException("Mailer Error (" . $address . ") " . $mail->ErrorInfo . "<br />");
  } else {
    echo "Message sent!";
  }
} catch (phpmailerAppException $e) {
  echo $e->errorMessage();
  exit();
}

?>

</body>
</html>
