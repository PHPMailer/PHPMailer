<html>
<head>
<title>POP before SMTP Test</title>
</head>
<body>

<?php
require_once('../class.phpmailer.php');
require_once('../class.pop3.php'); // required for POP before SMTP

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

  $pop = new POP3();
  $pop->Authorise('pop3.yourdomain.com', 110, 30, 'username', 'password', 1);

  $mail = new PHPMailer();

  if ($body = file_get_contents('contents.html')) {
    $body             = eregi_replace("[\]",'',$body);
  } else {
    throw new phpmailerAppException("Email address " . $to . " is invalid -- aborting!<br />");
  }

  $mail->IsSMTP();
  $mail->SMTPDebug = 2;
  $mail->Host     = 'pop3.yourdomain.com';

  $mail->From       = "name@yourdomain.com";
  $mail->FromName   = "First Last";
  $mail->AddReplyTo("name@yourdomain.com","First Last");

  $mail->Subject    = "PHPMailer Test Subject via POP before SMTP, basic";

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
