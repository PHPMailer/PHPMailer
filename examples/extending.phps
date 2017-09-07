<?php
/**
 * This example shows how to extend PHPMailer to simplify your coding.
 * If PHPMailer doesn't do something the way you want it to, or your code
 * contains too much boilerplate, don't edit the library files,
 * create a subclass instead and customise that.
 * That way all your changes will be retained when PHPMailer is updated.
 */

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

/**
 * Use PHPMailer as a base class and extend it
 */
class myPHPMailer extends PHPMailer
{
    /**
     * myPHPMailer constructor.
     *
     * @param bool|null $exceptions
     * @param string    $body A default HTML message body
     */
    public function __construct($exceptions, $body = '')
    {
        //Don't forget to do this or other things may not be set correctly!
        parent::__construct($exceptions);
        //Set a default 'From' address
        $this->setFrom('joe@example.com', 'Joe User');
        //Send via SMTP
        $this->isSMTP();
        //Equivalent to setting `Host`, `Port` and `SMTPSecure` all at once
        $this->Host = 'tls://smtp.example.com:587';
        //Set an HTML and plain-text body, import relative image references
        $this->msgHTML($body, './images/');
        //Show debug output
        $this->SMTPDebug = 2;
        //Inject a new debug output handler
        $this->Debugoutput = function ($str, $level) {
            echo "Debug level $level; message: $str\n";
        };
    }

    //Extend the send function
    public function send()
    {
        $this->Subject = '[Yay for me!] ' . $this->Subject;
        $r = parent::send();
        echo "I sent a message with subject " . $this->Subject;

        return $r;
    }
}

//Now creating and sending a message becomes simpler when you use this class in your app code
try {
    //Instantiate your new class, making use of the new `$body` parameter
    $mail = new myPHPMailer(true, '<strong>This is the message body</strong>');
    // Now you only need to set things that are different from the defaults you defined
    $mail->addAddress('jane@example.com', 'Jane User');
    $mail->Subject = 'Here is the subject';
    $mail->addAttachment(__FILE__, 'myPHPMailer.php');
    $mail->send(); //no need to check for errors - the exception handler will do it
} catch (Exception $e) {
    //Note that this is catching the PHPMailer Exception class, not the global \Exception type!
    echo "Caught a " . get_class($e) . ": " . $e->getMessage();
}
