<?php
/**
 * This uses the PHPMailer class to check that a connection can be made to an SMTP server via SSL
 */

//Import the PHPMailer SMTP class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

$mail = new PHPMailer();
$mail->SMTPDebug = 3;
$mail->Host = 'mail.example.com';
$mail->Port = 465;
$mail->SMTPAuth = true;
$mail->Username = 'username';
$mail->Password = 'password';
$mail->SMTPSecure = 'ssl';

try {
    if (!$mail->smtpConnect()) {
        throw new Exception($mail->ErrorInfo);
    } else {
        echo "Connected ok!";
    }
} catch (Exception $e) {
    echo 'SMTP error: ' . $e->getMessage(), "\n";
}
