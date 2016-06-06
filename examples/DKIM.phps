<?php
/**
 * This example shows how to use DKIM message authentication with PHPMailer.
 * There's more to using DKIM than just this code - check out this article:
 * @link https://yomotherboard.com/how-to-setup-email-server-dkim-keys/
 * See also the DKIM code in the PHPMailer unit tests,
 * which shows how to make a key pair from PHP.
 */

require '../PHPMailerAutoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailer;
//Set who the message is to be sent from
$mail->setFrom('from@example.com', 'First Last');
//Set an alternative reply-to address
$mail->addReplyTo('replyto@example.com', 'First Last');
//Set who the message is to be sent to
$mail->addAddress('whoto@example.com', 'John Doe');
//Set the subject line
$mail->Subject = 'PHPMailer DKIM test';
//This should be the same as the domain of your From address
$mail->DKIM_domain = 'example.com';
//Path to your private key file
$mail->DKIM_private = 'dkim_private.pem';
//Set this to your own selector
$mail->DKIM_selector = 'phpmailer';
//If your private key has a passphrase, set it here
$mail->DKIM_passphrase = '';
//The identity you're signing as - usually your From address
$mail->DKIM_identity = $mail->From;

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
