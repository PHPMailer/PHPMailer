<?php
/**
 * This example shows settings to use when sending via Google's Gmail servers.
 */

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

require '../PHPMailerAutoload.php';

//Load dependencies from composer
//If this causes an error, run 'composer install'
require '../vendor/autoload.php';

//Create a new Gmail-specific PHPMailer instance
$mail = new PHPMailerOAuthGoogle;

$mail->isSMTP();
$mail->SMTPDebug = 0;
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPSecure = 'tls';
$mail->SMTPAuth = true;

//Set AuthType
$mail->AuthType = 'XOAUTH2';

//User Email to use for SMTP authentication -  Who authorised to access Google mail
$mail->oauthUserEmail = 'sender@gmail.com';

//Obtained From Google Developer Console
$mail->oauthClientId = '{YOUR_APP_CLIENT_ID}';

//Obtained From Google Developer Console
$mail->oauthClientSecret = '{YOUR_APP_CLIENT_SECRET}';

//Obtained By running get_oauth_token.php after setting up APP in Google Developer Console.
//Set Redirect URI in Developer Console as [https/http]://<yourdomain>/<folder>/get_oauth_token.php
// eg: http://localhost/phpmail/get_oauth_token.php
$mail->oauthRefreshToken = '{OAUTH_TOKEN_FROM_GOOGLE}';

$mail->setFrom('sender@gmail.com', 'test test');
$mail->addAddress('whoto@example.com', 'John Doe');
$mail->Subject = 'PHPMailer GMail XOAUTH2 SMTP test';
$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
if (!$mail->send()) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message sent!';
}
