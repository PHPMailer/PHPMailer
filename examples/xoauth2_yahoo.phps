<?php
/**
 * This example shows settings to use when sending via Yahoo servers using XOAUTH2.
 */
namespace PHPMailer\PHPMailer;

date_default_timezone_set('Etc/UTC');

//Load dependencies from composer
//If this causes an error, run 'composer install'
require '../vendor/autoload.php';

//Create a new Yahoo-specific PHPMailer instance
$mail = new PHPMailer;
$mail->isSMTP();
$mail->SMTPDebug = 2;
$mail->Host = 'smtp.mail.yahoo.com';
$mail->Port = 587;
$mail->SMTPSecure = 'tls';
$mail->SMTPAuth = true;

//Set AuthType
$mail->AuthType = 'XOAUTH2';

//User Email to use for SMTP authentication - Who authorised to send Yahoo mail
$mail->oauthUserEmail = 'sender@yahoo.com';

//Obtained From https://developer.yahoo.com/apps/
$mail->oauthClientId = '{YAHOO_CLIENT_ID}';

//Obtained From https://developer.yahoo.com/apps/
$mail->oauthClientSecret = '{CLIENT_SECRET}'';

// eg: http://localhost/phpmail/get_oauth_token.php
$mail->oauthRefreshToken = '{REFRESH_TOKEN}';

$mail->setFrom('sender@yahoo.com', 'test');
$mail->addAddress('receiver@gmail.com', 'test');
$mail->Subject = 'PHPMailer Yahoo XOAUTH2 SMTP test';
$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
if (!$mail->send()) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message sent!';
}
