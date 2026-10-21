<?php

/**
 * This example shows how to send via Microsoft Outlook's servers using XOAUTH2 authentication
 * using the thenetworg/oauth2-azure client to provide the OAuth2 token.
 */

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
//Use the TheNetworg OAuth2 provider class
use TheNetworg\OAuth2\Client\Provider\Azure;

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

//Load dependencies from composer
//If this causes an error, run 'composer install'
require '../vendor/autoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailer();

//Tell PHPMailer to use SMTP
$mail->isSMTP();

//Enable SMTP debugging
//SMTP::DEBUG_OFF = off (for production use)
//SMTP::DEBUG_CLIENT = client messages
//SMTP::DEBUG_SERVER = client and server messages
$mail->SMTPDebug = SMTP::DEBUG_SERVER;

//Set the hostname of the mail server
$mail->Host = 'smtp.office365.com';

//Set the SMTP port number:
// - 465 for SMTP with implicit TLS, a.k.a. RFC8314 SMTPS or
// - 587 for SMTP+STARTTLS
$mail->Port = 587;

//Set the encryption mechanism to use:
// - SMTPS (implicit TLS on port 465) or
// - STARTTLS (explicit TLS on port 587)
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

//Whether to use SMTP authentication
$mail->SMTPAuth = true;

//Set AuthType to use XOAUTH2
$mail->AuthType = 'XOAUTH2';

//Start Option 1: Use client id/client secret for authentication
//Fill in authentication details here
//Either the microsoft account owner, or the user that gave consent
$email = 'someone@somemicrosoftaccount.com';
$clientId = 'RANDOMCHARS-----duv1n2TS';
$clientSecret = 'RANDOMCHARS-----lGyjPcRtvP';
$tenantId = 'RANDOMCHARS-----HSFTAOIlagss';

//Obtained from azure - see https://github.com/thenetworg/oauth2-azure
$refreshToken = 'RANDOMCHARS-----DWxgOvPT003r-yFUV49TQYag7_Aod7y0';

//Create a new OAuth2 provider instance
$provider = new Azure(
    [
        'clientId' => $clientId,
        'tenant' => $tenantId,
        'clientSecret' => $clientSecret
    ]
);
//Pass the OAuth provider instance to PHPMailer            
$oauth = new OAuth(
    [
        'provider' => $provider,
        'clientId' => $clientId,
        'refreshToken' => $refreshToken,
        'clientSecret' => $clientSecret,
        'userName' => $email
    ]
);
$mail->setOAuth($oauth);
//End Option 1

//Option 2: Use Client Key/Client Certificate for authentication
//Set up the other oauth library as per its documentation
//Then create the wrapper class that implementations OAuthTokenProvider
$email = 'someone@somemicrosoftaccount.com';
$clientId = 'RANDOMCHARS-----duv1n2TS';
$clientSecret = 'RANDOMCHARS-----lGyjPcRtvP';
$tenantId = 'RANDOMCHARS-----HSFTAOIlagss';
$refreshToken = 'RANDOMCHARS-----DWxgOvPT003r-yFUV49TQYag7_Aod7y0';
//Pass the key pair. See https://github.com/thenetworg/oauth2-azure
//The Fingerprint/Thumbprint needs to be uploaded to Azure Portal
$clientKey = 'RANDOMCHARS........';
$clientThumbprint = 'RANDOMCHARS............';
//Create a new OAuth2 provider instance.
$provider = new Azure(
    [
        'clientId' => $clientid,
        'tenant' => $tenantid,
        'clientCertificatePrivateKey' => $clientKey,
        'clientCertificateThumbprint' => $clientThumbprint
    ]
);
//Pass the OAuth provider instance to PHPMailer
$oauth = new OAuth(
    [
        'provider' => $provider,
        'clientId' => $clientId,
        'refreshToken' => $refreshToken,
        'userName' => $email
    ]
);

$mail->setOAuth($oauth);

//End Option 2

//Set who the message is to be sent from
//For Outlook, this generally needs to be the same as the user you logged in as
$mail->setFrom($email, 'First Last');

//Set who the message is to be sent to
$mail->addAddress('someone@someserver.com', 'John Doe');

//Set the subject line
$mail->Subject = 'PHPMailer Outlook XOAUTH2 SMTP test';

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->CharSet = PHPMailer::CHARSET_UTF8;
$mail->msgHTML(file_get_contents('contentsutf8.html'), __DIR__);

//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';

//Attach an image file
$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors
if (!$mail->send()) {
    echo 'Mailer Error: ' . $mail->ErrorInfo;
} else {
    echo 'Message sent!';
}
//check to see if a new refresh token has been issued
$newRefreshToken = $oauth->getOauthToken()->getRefreshToken();
if ($newRefreshToken !== $refreshToken && newRefreshToken !== "") {
//save the new refresh token
}
