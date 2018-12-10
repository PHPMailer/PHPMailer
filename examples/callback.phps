<?php
/**
 * This example shows how to use a callback function from PHPMailer.
 */

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

/**
 * Example PHPMailer callback function.
 * This is a global function, but you can also pass a closure (or any other callable)
 * to the `action_function` property.
 *
 * @param bool   $result  result of the send action
 * @param array  $to      email address of the recipient
 * @param array  $cc      cc email addresses
 * @param array  $bcc     bcc email addresses
 * @param string $subject the subject
 * @param string $body    the email body
 */
function callbackAction($result, $to, $cc, $bcc, $subject, $body)
{
    echo "Message subject: \"$subject\"\n";
    foreach ($to as $address) {
        echo "Message to {$address[1]} <{$address[0]}>\n";
    }
    foreach ($cc as $address) {
        echo "Message CC to {$address[1]} <{$address[0]}>\n";
    }
    foreach ($bcc as $toaddress) {
        echo "Message BCC to {$toaddress[1]} <{$toaddress[0]}>\n";
    }
    if ($result) {
        echo "Message sent successfully\n";
    } else {
        echo "Message send failed\n";
    }
}

require_once '../vendor/autoload.php';

$mail = new PHPMailer;

try {
    $mail->isMail();
    $mail->setFrom('you@example.com', 'Your Name');
    $mail->addAddress('jane@example.com', 'Jane Doe');
    $mail->addCC('john@example.com', 'John Doe');
    $mail->Subject = 'PHPMailer Test Subject';
    $mail->msgHTML(file_get_contents('../examples/contents.html'));
    // optional - msgHTML will create an alternate automatically
    $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
    $mail->addAttachment('images/phpmailer_mini.png'); // attachment
    $mail->action_function = 'callbackAction';
    $mail->send();
} catch (Exception $e) {
    echo $e->errorMessage();
}

//Alternative approach using a closure
try {
    $mail->action_function = function ($result, $to, $cc, $bcc, $subject, $body) {
        if ($result) {
            echo "Message sent successfully\n";
        } else {
            echo "Message send failed\n";
        }
    };
    $mail->send();
} catch (Exception $e) {
    echo $e->errorMessage();
}
