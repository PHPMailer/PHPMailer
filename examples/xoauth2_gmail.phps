<?php

/**

 * This example shows settings to use when sending via Google's Gmail servers using XOAUTH2.

 */

namespace PHPMailer\PHPMailer;



//SMTP needs accurate times, and the PHP time zone MUST be set

//This should be done in your php.ini, but this is how to do it if you don't have access to that

date_default_timezone_set('Etc/UTC');



require '../vendor/autoload.php';



//Create a new PHPMailer instance

$mail = new PHPMailerOAuth;



//Tell PHPMailer to use SMTP

$mail->isSMTP();



//Enable SMTP debugging

// 0 = off (for production use)

// 1 = client messages

// 2 = client and server messages

$mail->SMTPDebug = 2;



//Ask for HTML-friendly debug output

//$mail->Debugoutput = 'html';



//Set the hostname of the mail server

$mail->Host = 'smtp.gmail.com';



//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission

$mail->Port = 587;



//Set the encryption system to use - ssl (deprecated) or tls

$mail->SMTPSecure = 'tls';



//Whether to use SMTP authentication

$mail->SMTPAuth = true;



//Set AuthTYpe

$mail->AuthType = 'XOAUTH2';



//UserEmail to use for SMTP authentication - Use the same Email used in Google Developer Console

$mail->oauthUserEmail = "marcus.bointon@gmail.com";



//Obtained From Google Developer Console

$mail->oauthClientId = "237644427849-g8d0pnkd1jh3idcjdbopvkse2hvj0tdp.apps.googleusercontent.com";



//Obtained From Google Developer Console

$mail->oauthClientSecret = "mklHhrns6eF-qjwuiLpSB4DL";



//Obtained By running get_oauth_token.php after setting up APP in Google Developer Console.

//Set Redirect URI in Developer Console as [https/http]://<yourdomain>/<folder>/get_oauth_token.php

// eg: http://localhost/phpmail/get_oauth_token.php

$mail->oauthRefreshToken = "1/7Jt8_RHX86Pk09VTfQd4O_ZqKbmuV7HpMNz-rqJ4KdQMEudVrK5jSpoR30zcRFq6";



$mail->SMTPOptions = [

    'ssl' => [

        'verify_peer' => false,

        'verify_peer_name' => false,

        'allow_self_signed' => true

    ]

];



//Set who the message is to be sent from

$mail->setFrom('marcus.bointon@gmail.com', 'First Last');



//Set who the message is to be sent to

$mail->addAddress('marcus@synchromedia.co.uk', 'John Doe');



//Set the subject line

$mail->Subject = 'PHPMailer GMail SMTP test';



//Read an HTML message body from an external file, convert referenced images to embedded,

//convert HTML into a basic plain-text alternative body

$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));



//send the message, check for errors

if (!$mail->send()) {

    echo "Mailer Error: " . $mail->ErrorInfo;

} else {

    echo "Message sent!";

}

