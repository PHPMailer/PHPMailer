<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>PHPMailer - Exceptions test</title>
</head>
<body>
<?php
require '../class.phpmailer.php';

//Create a new PHPMailer instance
//Passing true to the constructor enables the use of exceptions for error handling
$mail = new PHPMailer(true);
try {
  //Set who the message is to be sent from
  $mail->SetFrom('from@example.com', 'First Last');
  //Set an alternative reply-to address
  $mail->AddReplyTo('replyto@example.com','First Last');
  //Set who the message is to be sent to
  $mail->AddAddress('whoto@example.com', 'John Doe');
  //Set the subject line
  $mail->Subject = 'PHPMailer Exceptions test';
  //Read an HTML message body from an external file, convert referenced images to embedded,
  //and convert the HTML into a basic plain-text alternative body
  $mail->MsgHTML(file_get_contents('contents.html'), dirname(__FILE__));
  //Replace the plain text body with one created manually
  $mail->AltBody = 'This is a plain-text message body';
  //Attach an image file
  $mail->AddAttachment('images/phpmailer_mini.gif');
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
