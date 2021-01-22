<?php 

require 'PHPMailer-master/PHPMailerAutoload.php';

$mail = new PHPMailer;
//$mail->SMTPDebug = 3;                               // Enable verbose debug output

$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = 'smtp.mandrillapp.com';  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = 'julio@versibarani.com.com';                 // SMTP username
$mail->Password = 'Nando137@';                           // SMTP password
$mail->Port = 587;                                    // TCP port to connect to

$mail->From = 'vernando111@gmail.com';
$mail->FromName = 'Test phpmailer';
$mail->addAddress('who_are_you_sending@to.com');               // Name is optional

$mail->isHTML(true);                                  // Set email format to HTML

$mail->Subject = 'Here is the subject';
$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

if(!$mail->send()) {
    echo 'Message could not be sent.';
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message has been sent';
}
