<html>
<head>
<title>PHPMailer - Sendmail advanced test</title>
</head>
<body>

<?php

require_once('../class.phpmailer.php');

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
  $mail             = new PHPMailer(); // defaults to using php "mail()"

  $mail->IsSendmail(); // telling the class to use SendMail transport

  if ($body = file_get_contents('contents.html')) {
    $body             = eregi_replace("[\]",'',$body);
  } else {
    throw new phpmailerAppException("Email address " . $to . " is invalid -- aborting!<br />");
  }

  $mail->AddReplyTo("name@yourdomain.com","First Last");

  $mail->From       = "name@yourdomain.com";
  $mail->FromName   = "First Last";
  $mail->AddReplyTo("name@yourdomain.com","First Last");

  $mail->AddAddress($address, "John Doe");

  $mail->Subject    = "PHPMailer Test Subject via Sendmail, advanced";

  $mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

  $mail->MsgHTML($body);

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
