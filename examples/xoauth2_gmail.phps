<?php
/**
 * This example shows settings to use when sending via Google's Gmail servers using XOAUTH2.
 */

namespace PHPMailer\PHPMailer;

date_default_timezone_set('Etc/UTC');

require '../vendor/autoload.php';

$mail = new PHPMailer;
$mail->isSMTP();
$mail->SMTPDebug = 2;
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPSecure = 'tls';
$mail->SMTPAuth = true;
$mail->Subject = 'PHPMailer GMail SMTP test';
$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
$mail->setFrom('sender@gmail.com', 'Test');
$mail->addAddress('receiver@hotmail.com', 'Test');

//send the message, check for errors

if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
