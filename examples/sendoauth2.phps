<?php

/**
 * This example uses the SendOauth2 wrapper to support OAuth2 (and Basic) authentication for both Microsoft
 * 365 Exchange email and Google Gmail.
 * Client secrets and X.509 certificates are supported for Exchange. Client secrets are supported for Gmail.
 * Authorization_code grant flow and client_credentials (i.e. application) grant flow for SMTP are supported for
 * Exchange. Authorization_code grant flow is supported for Gmail.
 * Appropriate scopes (client permissions) and 'provider' overrides are added automatically.
 *
 * Install with Composer from the decomplexity/SendOauth2 repo.
 *
 * SendOauth2 can be also be invoked using less (or even no) arguments - see the repo for details.
 *
 * Needs PHPMailer >=6.6.0 that added support for oauthTokenProvider
 *
 * (The next release [V4] of the wrapper will replace TheLeague's Google provider by Google's own GoogleOauthClient;
 * this will provide support for Google's version of client credentials (Service Accounts) and client certificates)
 */

//Import SendOauth2B class into the global namespace
use decomplexity\SendOauth2\SendOauth2B;
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.office365.com';                   //Set the SMTP server (smtp.gmail.com for Gmail)
    $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    $mail->Username   = 'user@example.com';                     //SMTP username
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable implicit TLS encryption
    $mail->Port       = 465;                                    //TCP port to connect to
    $mail->AuthType   = 'XOAUTH2';                              // Set AuthType to use XOAUTH2

    //Sender and recipients
    $mail->setFrom('from@example.com', 'Mailer');               // 'Header' From address with optional sender name
    $mail->addAddress('joe@example.net', 'Joe User');           //Add a recipient

    //Authentication
    $oauthTokenProvider = new SendOauth2B(
        ['mail' => $mail,                                                 // PHPMailer instance
                'tenant'                      => 'long string',           // tenant GUID or domain name. Null for Gmail
                'clientId'                    => 'long string',
                'clientSecret'                => 'long string',           // or null if using a certificate
                'clientCertificatePrivateKey' => 'extremely long string', // or null if using a clientSecret
                'clientCertificateThumbprint' => 'long string',           // or null if using a clientSecret
                'serviceProvider'             => 'Microsoft',             // or Google
                'authTypeSetting'             =>  $mail->AuthType,        // is set above - or insert here as 'XOAUTH2'
                'mailSMTPAddress'             => 'me@mydomain.com',       // Envelope/mailFrom/reverse-path From address
                'hostedDomain'                => 'mydomain.com',          // Google only (and optional)
                'refreshToken'                => 'very long string',
                'grantTypeValue'              => 'authorization_code',    // or 'client_credentials' (Microsoft only)
                 ]
    );
    /**
      * If an argument (above) has a null value, the argument can be omitted altogether.
      * ClientCertificatePrivateKey should include the -----BEGIN PRIVATE KEY----- and  -----END PRIVATE KEY-----
      */

    $mail->setOAuth($oauthTokenProvider);                                 //Pass OAuthTokenProvider to PHPMailer

    //Content
    $mail->isHTML(true);                                                 //Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
