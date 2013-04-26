<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>PHPMailer - POP-before-SMTP test</title>
</head>
<body>
<?php
require '../class.phpmailer.php';
require '../class.pop3.php';

//Create a new POP client instance
$pop = new POP3();
//Authenticate via POP
$pop->Authorise('pop3.yourdomain.com', 110, 30, 'username', 'password', 1);
//Now you should be clear to submit messages over SMTP for a while
//Only applies if your host supports POP-before-SMTP

//Create a new PHPMailer instance
//Passing true to the constructor enables the use of exceptions for error handling
$mail = new PHPMailer(true);
try {
  $mail->IsSMTP();
  //Enable SMTP debugging
  // 0 = off (for production use)
  // 1 = client messages
  // 2 = client and server messages
  $mail->SMTPDebug  = 2;
  //Ask for HTML-friendly debug output
  $mail->Debugoutput = 'html';
  //Set the hostname of the mail server
  $mail->Host       = "mail.example.com";
  //Set the SMTP port number - likely to be 25, 465 or 587
  $mail->Port       = 25;
  //Whether to use SMTP authentication
  $mail->SMTPAuth   = false;
  //Set who the message is to be sent from
  $mail->SetFrom('from@example.com', 'First Last');
  //Set an alternative reply-to address
  $mail->AddReplyTo('replyto@example.com','First Last');
  //Set who the message is to be sent to
  $mail->AddAddress('whoto@example.com', 'John Doe');
  //Set the subject line
  $mail->Subject = 'PHPMailer POP-before-SMTP test';
  //Read an HTML message body from an external file, convert referenced images to embedded,
  //and convert the HTML into a basic plain-text alternative body
  $mail->MsgHTML(file_get_contents('contents.html'), dirname(__FILE__));
  //Replace the plain text body with one created manually
  $mail->AltBody = 'This is a plain-text message body';
  //Attach an image file
  $mail->AddAttachment('images/phpmailer-mini.gif');
  //Send the message
  //Note that we don't need check the response from this because it will throw an exception if it has trouble
  $mail->Send();
  echo "Message sent!";
} catch (phpmailerException $e) {
  echo $e->errorMessage(); //Pretty error messages from PHPMailer
} catch (Exception $e) {
  echo $e->getMessage(); //Boring error messages from anything else!
}
?>
</body>
</html>
