<?php

//error_reporting(E_ALL);
error_reporting(E_STRICT);

date_default_timezone_set('America/Toronto');
//date_default_timezone_set(date_default_timezone_get());

include_once('class.phpmailer.php');
//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

$mail             = new PHPMailer();

$body             = $mail->getFile('contents.html');
$body             = eregi_replace("[\]",'',$body);

$mail->IsSMTP(); // telling the class to use SMTP
$mail->Host       = "mail.worxteam.com"; // SMTP server

$mail->From       = "name@yourdomain.com";
$mail->FromName   = "First Last";

$mail->Subject    = "PHPMailer Test Subject via smtp";

$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

$mail->MsgHTML($body);

$mail->AddAddress("whoto@otherdomain.com", "John Doe");

$mail->AddAttachment("images/phpmailer.gif");             // attachment

if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}

?>
