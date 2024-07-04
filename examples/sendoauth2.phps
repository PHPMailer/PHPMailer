<?php

/**
 * The SendOauth2 wrapper supports OAuth2 and Basic authorization/authentication for
 * Microsoft 365 Exchange email and Google Gmail. Both TheLeague's Google provider + client
 * and Google's 'official' GoogleAPI client are supported. The wrapper supports any authentication
 * mechanism provided by these systems: authorization_code grant and client_credentials grant
 * (aka Google 'service accounts'), client secrets and X.509 certificates, $_SESSION 'state'
 * and PKCE code exchanges, and creation on the fly of GoogleAPI's .json credentials files.
 * Appropriate scopes (client permissions) and 'provider' overrides are added automatically.
 *
 * The wrapper is installed with Composer from the decomplexity/SendOauth2 repo; see its README.
 *
 * The wrapper can also be invoked using fewer (or even no) arguments; this is for those websites
 * that use PHPMailer in several places. See the repo for details.
 */

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
// Import SendOauth2B class
use decomplexity\SendOauth2\SendOauth2B;

// Uncomment the next two lines to display PHP errors
// error_reporting(E_ALL);
// ini_set("display_errors", 1);

// Load Composer's autoloader
require 'vendor/autoload.php';

// Set timezone for SMTP
date_default_timezone_set('Etc/UTC');

// Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();                                            // Use SMTP
    $mail->SMTPDebug  = SMTP::DEBUG_OFF;                        // Set DEBUG_LOWLEVEL for SMTP diagnostics
    $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable implicit TLS encryption
    $mail->Port       = 587;                                    // TCP port; MSFT doesn't like 465
    $mail->AuthType   = 'XOAUTH2';                              // Set AuthType to use XOAUTH2 ('LOGIN' for Basic auth)

    // Sender and recipients
    $mail->setFrom('from@example.com', 'Mailer');               // 'Header' From address with optional sender name
    $mail->addAddress('joe@example.net', 'Joe User');           // Add a To: recipient

    /**
     * Authenticate
     * Note that any ClientCertificatePrivateKey should include the -----BEGIN PRIVATE KEY----- and
     *  -----END PRIVATE KEY-----
     */

    $oauthTokenProvider = new SendOauth2B(
        [
            'mail' => $mail,                                          // PHPMailer instance
            'clientId'                    => 'long string',           // for Google service account, Unique ID
            'clientSecret'                => 'long string',           // or null if using a certificate
            'clientCertificatePrivateKey' => 'ultra long string',     // or null if using a clientSecret
            'clientCertificateThumbprint' => 'long string',           // or null if using a clientSecret
            'serviceProvider'             => 'Microsoft',             // literal: also 'Google' or 'GoogleAPI'
            'authTypeSetting'             =>  $mail->AuthType,        // is set above - or insert here as 'XOAUTH2'
            'mailSMTPAddress'             => 'me@mydomain.com',       // Envelope/mailFrom/reverse-path From address
            'refreshToken'                => 'very long string',      // null if grantType is 'client_credentials'
            'grantType'                   => 'authorization_code',    // or 'client_credentials'

            'tenant'                      => 'long string',           // MSFT tenant GUID. Null for Gmail

            'hostedDomain'                => 'mydomain.com',          // Any Google (and optional). Null for MSFT

            'projectID'                   => 'string',                // GoogleAPI only. Else null
            'serviceAccountName'          => 'string',                // GoogleAPI service account only. Else null
            'impersonate'                 => 'you@mydomain.com',      // Google API service account only. Else null
                                                                      // default to 'mailSMTPAddress', must be
                                                                      // a Google Wspace email adddress, not @gmail
            'gmailXoauth2Credentials'     => 'your credentials.json', // File name - defaults to:
                                                                      // gmail-xoauth2-credentials.json
            'writeGmailCredentialsFile'   => 'yes' or 'no',           // Defaults to 'yes'; meaning the
                                                                      // credentials json is dynamically created
         ]
    );


    $mail->setOAuth($oauthTokenProvider);                                 // Pass OAuthTokenProvider to PHPMailer
    $mail->Host    = 'smtp.office365.com';                                // Set SMTP server (smtp.gmail.com for Gmail)

    // Content
    $mail->isHTML(true);                                                  // Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ' . htmlspecialchars($mail->ErrorInfo, ENT_QUOTES);
}
