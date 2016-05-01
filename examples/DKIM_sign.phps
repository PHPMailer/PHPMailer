<?php
/**
 * This example shows sending a DKIM-signed message with PHPMailer.
 * More info about DKIM can be found here: http://www.dkim.org/info/dkim-faq.html
 */

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;

require '../vendor/autoload.php';

//Usual setup
$mail = new PHPMailer;
$mail->setFrom('from@example.com', 'First Last');
$mail->addReplyTo('replyto@example.com', 'First Last');
$mail->addAddress('whoto@example.com', 'John Doe');
$mail->Subject = 'PHPMailer mail() test';
$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
$mail->AltBody = 'This is a plain-text message body';

//See the DKIM_gen_keys.phps script for making a key pair -
//here we assume you've already done that.
//Path to your private key:
$privatekeyfile = 'dkim_private.pem';

//Put your domain in here
$mail->DKIM_domain = 'example.com';
//Put the path to your private key file in here
$mail->DKIM_private = $privatekeyfile;
//Set the selector
$mail->DKIM_selector = 'phpmailer';
//Put your private key's passphrase in here if it has one
//Leave it blank otherwise.
$mail->DKIM_passphrase = '';

//When you send, the DKIM settings will be used to sign the message
//if (!$mail->send()) {
//    echo "Mailer Error: " . $mail->ErrorInfo;
//} else {
//    echo "Message sent!";
//}
