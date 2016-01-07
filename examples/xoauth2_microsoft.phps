<?php

/**

 * This example shows settings to use when sending via Outlook/Microsoft Live servers using XOAUTH2.

 */

namespace PHPMailer\PHPMailer;



date_default_timezone_set('Etc/UTC');



require '../vendor/autoload.php';



//Load dependencies from composer

//If this causes an error, run 'composer install'

require '../vendor/autoload.php';



//Create a new Microsoft-specific PHPMailer instance

$mail = new PHPMailerOAuthMicrosoft;



$mail->isSMTP();

$mail->SMTPDebug = 2;

$mail->Host = 'smtp-mail.outlook.com';

$mail->Port = 587;

$mail->SMTPSecure = 'tls';

$mail->SMTPAuth = true;



//Set AuthType

$mail->AuthType = 'XOAUTH2';



//User Email to use for SMTP authentication - Who authorised to access Outlook mail

$mail->oauthUserEmail = "sender@hotmail.com";



//Obtained From https://account.live.com/developers/applications/index

$mail->oauthClientId = "{YOUR_CLIENT_ID}";



//Obtained From https://account.live.com/developers/applications/index

$mail->oauthClientSecret = "{YOUR_CLIENT_SECRET}";



//Obtained By running get_oauth_token.php 

$mail->oauthRefreshToken = "{OAUTH_REFRESH_TOKEN}";



$mail->setFrom('sender@hotmail.com', 'Test');

$mail->addAddress('receiver@gmail.com', 'Test');

$mail->Subject = 'PHPMailer hotmail XOAUTH2 SMTP test';

$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));

if (!$mail->send()) {

    echo "Mailer Error: " . $mail->ErrorInfo;

} else {

    echo "Message sent!";

}

