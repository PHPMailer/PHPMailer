<?php
/**
 * This example shows settings to use when sending via Outlook/Microsoft Live servers.
 */

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

require '../PHPMailerAutoload.php';


//Load dependnecies from composer
//If this causes an error, run 'composer install'
require '../vendor/autoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailerOAuthMicrosoft;

//Tell PHPMailer to use SMTP
$mail->isSMTP();

//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 2;

//Ask for HTML-friendly debug output
$mail->Debugoutput = 'text';

//Set the hostname of the mail server
$mail->Host = 'smtp-mail.outlook.com';

//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
$mail->Port = 587;

//Set the encryption system to use - ssl (deprecated) or tls
$mail->SMTPSecure = 'tls';

//Whether to use SMTP authentication
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



//Set who the message is to be sent from
//For gmail, this generally needs to be the same as the user you logged in as
$mail->setFrom('sender@hotmail.com', 'Test');

//Set who the message is to be sent to
$mail->addAddress('receiver@gmail.com', 'Test');

//Set the subject line
$mail->Subject = 'PHPMailer GMail SMTP test.Gmail Final';

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));

$mail->msgHTML("<html><body>Hello 1</body></html>");

//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';

//Attach an image file
$mail->addAttachment('images/phpmailer_mini.png');

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
