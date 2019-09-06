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

//Sample Images from https://sample-videos.com/download-sample-jpg-image.php
// $ curl https://sample-videos.com/img/Sample-jpg-image-1mb.jpg > /tmp/1mb.jpg
// $ curl https://sample-videos.com/img/Sample-jpg-image-500kb.jpg > /tmp/500kb.jpg
// $ curl https://sample-videos.com/img/Sample-jpg-image-200kb.jpg > /tmp/200kb.jpg
// $ curl https://sample-videos.com/img/Sample-jpg-image-100kb.jpg > /tmp/100kb.jpg
// $ curl https://sample-videos.com/img/Sample-jpg-image-50kb.jpg > /tmp/50kb.jpg
//
$image_files = array(
    '1.8kb' => 'phpmailer_mini.png',
    '5.7kb' => 'images/phpmailer.png',
    '50kb' => '/tmp/50kb.jpg',
    '100kb' => '/tmp/100kb.jpg',
    '200kb' => '/tmp/200kb.jpg',
    '500kb' => '/tmp/500kb.jpg',
);

$receiver_count = 200;

$receiver_list = [];
for ($i=0 ; $i<$receiver_count ; ++$i)
    $receiver_list[] = 'whoto'.$i.'@example.com';

echo "Genrate $receiver_count receivers: " . count($receiver_list) . "\n";

$results = [];
$test_count_per_run = 10;

echo "Sending $receiver_count mails with creating a new PHPMailer instance:\n";
{
    foreach ($image_files as $filesize => $image_file) {
        echo "\tWith $filesize attachment, path = $image_file\n";
        $total_time = 0;
        for ($i=1; $i<=$test_count_per_run ; ++$i) {
            $start = microtime(true);
            foreach ($receiver_list as $receiver) {
                $mail = new PHPMailer;
                $mail->isSMTP();
                $mail->SMTPDebug = 0;
                $mail->Host = 'mail.example.com';
                $mail->Port = 25;
                $mail->SMTPAuth = true;
                $mail->Username = 'yourname@example.com';
                $mail->Password = 'yourpassword';
                $mail->setFrom('from@example.com', 'First Last');
                $mail->addReplyTo('replyto@example.com', 'First Last');
                $mail->addAddress($receiver);
                $mail->Subject = 'Hi '.$receiver.'!';
                $mail->msgHTML(str_replace( '<h1>This is a test of PHPMailer.</h1>', '<h1>Hi '.$receiver.'</h1>', file_get_contents('contents.html')), __DIR__);
                $mail->AltBody = 'This is a plain-text message body for '.$receiver;
    
                //Attach an image file
                $mail->addAttachment($image_file);
    
                //Build the mail content
                $mail->preSend(); 
                unset($mail);
            }
            $cost = microtime(true) - $start;
            $total_time += $cost;
        }
        $avg_time = $total_time / floatval($test_count_per_run);
        echo sprintf("\t\ttest $test_count_per_run runs, avg. time: %.5f seconds\n", $avg_time );
    
        if (!isset($results[$filesize]))
            $results[$filesize] = [];
        $results[$filesize]['method1'] = $avg_time;
    }
}

echo "Sending $receiver_count mails with only one PHPMailer instance: \n";
{
    foreach ($image_files as $filesize => $image_file) {
        echo "\tWith $filesize attachment, path = $image_file\n";
        $total_time = 0;
        for ($i=1; $i<=$test_count_per_run ; ++$i) {
            //echo "\t\tRun $i: ";
            $start = microtime(true);

            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host = 'mail.example.com';
            $mail->Port = 25;
            $mail->SMTPAuth = true;
            $mail->Username = 'yourname@example.com';
            $mail->Password = 'yourpassword';
            $mail->setFrom('from@example.com', 'First Last');
            $mail->addReplyTo('replyto@example.com', 'First Last');

            foreach ($receiver_list as $receiver) {
                       $mail->clearAddresses();
                $mail->addAddress($receiver);
                $mail->Subject = 'Hi '.$receiver.'!';
                $mail->msgHTML(str_replace( '<h1>This is a test of PHPMailer.</h1>', '<h1>Hi '.$receiver.'</h1>', file_get_contents('contents.html')), __DIR__);
                $mail->AltBody = 'This is a plain-text message body for '.$receiver;
    
                //Attach an image file
                $mail->addAttachment($image_file);
    
                //Build the mail content
                $mail->preSend(); 
            }
            $cost = microtime(true) - $start;
            //echo "$cost sec\n";
            $total_time += $cost;
        }
        $avg_time = $total_time / floatval($test_count_per_run);
        echo sprintf("\t\ttest $test_count_per_run runs, avg. time: %.5f seconds\n", $avg_time );
    
        if (!isset($results[$filesize]))
            $results[$filesize] = [];
        $results[$filesize]['method2'] = $avg_time;
    }
}

echo "Sending $receiver_count mails with creating a new PHPMailer instance and MIMECache: \n";
{
    foreach ($image_files as $filesize => $image_file) {
        echo "\tWith $filesize attachment, path = $image_file\n";
        $total_time = 0;
        for ($i=1; $i<=$test_count_per_run ; ++$i) {
            //echo "\t\tRun $i: ";
            $start = microtime(true);
            $cacheLookupTable = [];
            foreach ($receiver_list as $receiver) {
                $mail = new PHPMailer;
                $mail->isSMTP();
                $mail->SMTPDebug = 0;
                $mail->Host = 'mail.example.com';
                $mail->Port = 25;
                $mail->SMTPAuth = true;
                $mail->Username = 'yourname@example.com';
                $mail->Password = 'yourpassword';
                $mail->setFrom('from@example.com', 'First Last');
                $mail->addReplyTo('replyto@example.com', 'First Last');
                $mail->addAddress($receiver);
                $mail->Subject = 'Hi '.$receiver.'!';
                $mail->msgHTML(str_replace( '<h1>This is a test of PHPMailer.</h1>', '<h1>Hi '.$receiver.'</h1>', file_get_contents('contents.html')), __DIR__);
                $mail->AltBody = 'This is a plain-text message body for '.$receiver;
    
                //Attach an image file
                $mail->addAttachment($image_file);

                $mail->MIMECache = &$cacheLookupTable;
    
                //Build the mail content
                $mail->preSend(); 
                unset($mail);
            }
            $cost = microtime(true) - $start;
            //echo "$cost sec\n";
            $total_time += $cost;
        }
        $avg_time = $total_time / floatval($test_count_per_run);
        echo sprintf("\t\ttest $test_count_per_run runs, avg. time: %.5f seconds\n", $avg_time );
    
        if (!isset($results[$filesize]))
            $results[$filesize] = [];
        $results[$filesize]['method3'] = $avg_time;
    }

}
echo "Sending $receiver_count mails with only one PHPMailer instance and MIMECache: \n";
{
    foreach ($image_files as $filesize => $image_file) {
        echo "\tWith $filesize attachment, path = $image_file\n";
        $total_time = 0;
        for ($i=1; $i<=$test_count_per_run ; ++$i) {
            //echo "\t\tRun $i: ";
            $start = microtime(true);
            $cacheLookupTable = [];

            $mail = new PHPMailer;
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host = 'mail.example.com';
            $mail->Port = 25;
            $mail->SMTPAuth = true;
            $mail->Username = 'yourname@example.com';
            $mail->Password = 'yourpassword';
            $mail->setFrom('from@example.com', 'First Last');
            $mail->addReplyTo('replyto@example.com', 'First Last');

            foreach ($receiver_list as $receiver) {
                       $mail->clearAddresses();
                $mail->addAddress($receiver);
                $mail->Subject = 'Hi '.$receiver.'!';
                $mail->msgHTML(str_replace( '<h1>This is a test of PHPMailer.</h1>', '<h1>Hi '.$receiver.'</h1>', file_get_contents('contents.html')), __DIR__);
                $mail->AltBody = 'This is a plain-text message body for '.$receiver;
    
                //Attach an image file
                $mail->addAttachment($image_file);

                $mail->MIMECache = &$cacheLookupTable;
    
                //Build the mail content
                $mail->preSend(); 
            }
            $cost = microtime(true) - $start;
            //echo "$cost sec\n";
            $total_time += $cost;
        }
        $avg_time = $total_time / floatval($test_count_per_run);
        echo sprintf("\t\ttest $test_count_per_run runs, avg. time: %.5f seconds\n", $avg_time );
    
        if (!isset($results[$filesize]))
            $results[$filesize] = [];
        $results[$filesize]['method4'] = $avg_time;
    }
}
//
// Print results
echo "\n";
echo "Size\tMethod1\tMethod2\tMethod3\tMethod4\n";
foreach($results as $filesize => $test_method_result) {
    echo "$filesize";
    foreach($test_method_result as $value)
        echo sprintf("\t%3.4f", $value);
    echo "\n";
}
