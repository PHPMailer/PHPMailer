<?php
/**
 * This example shows sending mail per receiver and reduce MIME encode.
 */

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

require '../vendor/autoload.php';


$receiver_count = 1000;

$receiver_list = [];
for ($i=0 ; $i<$receiver_count ; ++$i)
    $receiver_list[] = 'whoto'.$i.'@example.com';

echo "Genrate $receiver_count receivers: " . count($receiver_list) . "\n";

echo "Sending $receiver_count mails with creating a new PHPMailer instance: ";

$start = microtime(true);
foreach ($receiver_list as $receiver) {

    //Create a new PHPMailer instance
    $mail = new PHPMailer;
    //Tell PHPMailer to use SMTP
    $mail->isSMTP();
    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;
    //Set the hostname of the mail server
    $mail->Host = 'mail.example.com';
    //Set the SMTP port number - likely to be 25, 465 or 587
    $mail->Port = 25;
    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;
    //Username to use for SMTP authentication
    $mail->Username = 'yourname@example.com';
    //Password to use for SMTP authentication
    $mail->Password = 'yourpassword';
    //Set who the message is to be sent from
    $mail->setFrom('from@example.com', 'First Last');
    //Set an alternative reply-to address
    $mail->addReplyTo('replyto@example.com', 'First Last');
    //Set who the message is to be sent to
    $mail->addAddress($receiver);
    //Set the subject line
    $mail->Subject = 'Hi '.$receiver.'!';
    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    $mail->msgHTML(str_replace( '<h1>This is a test of PHPMailer.</h1>', '<h1>Hi '.$receiver.'</h1>', file_get_contents('contents.html')), __DIR__);
    //Replace the plain text body with one created manually
    $mail->AltBody = 'This is a plain-text message body for '.$receiver;
    //Attach an image file
    $mail->addAttachment('images/phpmailer_mini.png');

    //Build the mail content
    $mail->preSend(); 
    unset($mail);
}
$cost = microtime(true) - $start;

echo $cost . " sec\n";

echo "Sending $receiver_count mails with only one PHPMailer instance: ";

$start = microtime(true);
{
    //Create a new PHPMailer instance
    $mail = new PHPMailer;
    //Tell PHPMailer to use SMTP
    $mail->isSMTP();
    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;
    //Set the hostname of the mail server
    $mail->Host = 'mail.example.com';
    //Set the SMTP port number - likely to be 25, 465 or 587
    $mail->Port = 25;
    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;
    //Username to use for SMTP authentication
    $mail->Username = 'yourname@example.com';
    //Password to use for SMTP authentication
    $mail->Password = 'yourpassword';
    //Set who the message is to be sent from
    $mail->setFrom('from@example.com', 'First Last');
    //Set an alternative reply-to address
    $mail->addReplyTo('replyto@example.com', 'First Last');

    //Attach an image file
    $mail->addAttachment('images/phpmailer_mini.png');

    foreach ($receiver_list as $receiver) {
        //Reset addresses
        $mail->clearAddresses();
        //Set who the message is to be sent to
        $mail->addAddress($receiver);
        //Set the subject line
        $mail->Subject = 'Hi '.$receiver.'!';
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $mail->msgHTML(str_replace( '<h1>This is a test of PHPMailer.</h1>', '<h1>Hi '.$receiver.'</h1>', file_get_contents('contents.html')), __DIR__);
        //Replace the plain text body with one created manually
        $mail->AltBody = 'This is a plain-text message body for '.$receiver;
 
        //Build the mail content
        $mail->preSend(); 
    }
}
$cost = microtime(true) - $start;

echo $cost . " sec\n";
//
// ---
//
echo "Sending $receiver_count mails with creating a new PHPMailer instance and MIMECache: ";

$start = microtime(true);
$cacheLookupTable = [];
foreach ($receiver_list as $receiver) {
    //Create a new PHPMailer instance
    $mail = new PHPMailer;
    //Tell PHPMailer to use SMTP
    $mail->isSMTP();
    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;
    //Set the hostname of the mail server
    $mail->Host = 'mail.example.com';
    //Set the SMTP port number - likely to be 25, 465 or 587
    $mail->Port = 25;
    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;
    //Username to use for SMTP authentication
    $mail->Username = 'yourname@example.com';
    //Password to use for SMTP authentication
    $mail->Password = 'yourpassword';
    //Set who the message is to be sent from
    $mail->setFrom('from@example.com', 'First Last');
    //Set an alternative reply-to address
    $mail->addReplyTo('replyto@example.com', 'First Last');
    //Set who the message is to be sent to
    $mail->addAddress($receiver);
    //Set the subject line
    $mail->Subject = 'Hi '.$receiver.'!';
    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    $mail->msgHTML(str_replace( '<h1>This is a test of PHPMailer.</h1>', '<h1>Hi '.$receiver.'</h1>', file_get_contents('contents.html')), __DIR__);
    //Replace the plain text body with one created manually
    $mail->AltBody = 'This is a plain-text message body for '.$receiver;
    //Attach an image file
    $mail->addAttachment('images/phpmailer_mini.png');

    //Set Reduce MIME Encode Cache Store
    $mail->MIMECache = &$cacheLookupTable;

    //Build the mail content
    $mail->preSend(); 
    unset($mail);
}
$cost = microtime(true) - $start;

echo $cost . " sec\n";
//
// ---
//
echo "Sending $receiver_count mails with only one PHPMailer instance and MIMECache: ";

$start = microtime(true);
$cacheLookupTable = [];
{
    //Create a new PHPMailer instance
    $mail = new PHPMailer;
    //Tell PHPMailer to use SMTP
    $mail->isSMTP();
    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;
    //Set the hostname of the mail server
    $mail->Host = 'mail.example.com';
    //Set the SMTP port number - likely to be 25, 465 or 587
    $mail->Port = 25;
    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;
    //Username to use for SMTP authentication
    $mail->Username = 'yourname@example.com';
    //Password to use for SMTP authentication
    $mail->Password = 'yourpassword';
    //Set who the message is to be sent from
    $mail->setFrom('from@example.com', 'First Last');
    //Set an alternative reply-to address
    $mail->addReplyTo('replyto@example.com', 'First Last');
    //Attach an image file
    $mail->addAttachment('images/phpmailer_mini.png');

    //Set Reduce MIME Encode Cache Store
    $mail->MIMECache = &$cacheLookupTable;

    foreach ($receiver_list as $receiver) {
        //Reset addresses
        $mail->clearAddresses();
        //Set who the message is to be sent to
        $mail->addAddress($receiver);
        //Set the subject line
        $mail->Subject = 'Hi '.$receiver.'!';
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $mail->msgHTML(str_replace( '<h1>This is a test of PHPMailer.</h1>', '<h1>Hi '.$receiver.'</h1>', file_get_contents('contents.html')), __DIR__);
        //Replace the plain text body with one created manually
        $mail->AltBody = 'This is a plain-text message body for '.$receiver;
 
        //Build the mail content
        $mail->preSend(); 
    }
}
$cost = microtime(true) - $start;

echo $cost . " sec\n";
