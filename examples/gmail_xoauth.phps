<?php

/**
 * This example shows settings to use when sending via Google's Gmail servers.
 */

namespace PHPMailer\PHPMailer;

// Give alias to the League Provider Classes that may be used
use League\OAuth2\Client\Provider\Google as Google;
use Stevenmaguire\OAuth2\Client\Provider\Microsoft as Microsoft;
use Hayageek\OAuth2\Client\Provider\Yahoo as Yahoo;


//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

require '../vendor/autoload.php';

//Load dependencies from composer
//If this causes an error, run 'composer install'
require '../vendor/autoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailer;

//Tell PHPMailer to use SMTP
$mail->isSMTP();

//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 0;

//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';

//Set the hostname of the mail server
$mail->Host = 'smtp.gmail.com';

//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$mail->Port = 587;

//Set the encryption system to use - ssl (deprecated) or tls
$mail->SMTPSecure = 'tls';

//Whether to use SMTP authentication
$mail->SMTPAuth = true;

//Set AuthType
$mail->AuthType = 'XOAUTH2';

// Fill all necessary Authentication details here
$email = 'someone@gmail.com';
$clientId = 'RANDOMCHARS-----duv1n2.apps.googleusercontent.com';
$clientSecret = 'RANDOMCHARS-----lGyjPcRtvP';

//Obtained By running get_oauth_token.php after setting up APP in Google Developer Console.
//Set Redirect URI in Developer Console as [https/http]://<yourdomain>/<folder>/get_oauth_token.php
// eg: http://localhost/phpmail/get_oauth_token.php
$refreshToken = 'RANDOMCHARS-----DWxgOvPT003r-yFUV49TQYag7_Aod7y0';

//Change the Class Name depending on the Provider Used
if (!isset($provider)) {
    $provider = new Google([
        'clientId' => $clientId,
        'clientSecret' => $clientSecret
    ]);
}

$mail->setOAuth(new OAuthProvider\Base(
        [
    'provdier' => $provider,
    'clientId' => $clientId,
    'clientSecret' => $clientSecret,
    'refreshToken' => $refreshToken,
    'userName' => $email]
));

//Set who the message is to be sent from
//For gmail, this generally needs to be the same as the user you logged in as
$mail->setFrom('someone@gmail.com', 'First Last');

//Set who the message is to be sent to
$mail->addAddress('someone@gmail.com', 'John Doe');

//Set the subject line
$mail->Subject = 'PHPMailer GMail SMTP test';

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));

//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';

//Attach an image file
$mail->addAttachment('images/phpmailer_mini.png');

$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
);

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
