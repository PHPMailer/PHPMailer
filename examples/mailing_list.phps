<?php

error_reporting(E_STRICT|E_ALL);

date_default_timezone_set('Etc/UTC');

require '../class.phpmailer.php';

$mail                = new PHPMailer();

$body                = file_get_contents('contents.html');

$mail->IsSMTP();
$mail->Host          = 'smtp.example.com';
$mail->SMTPAuth      = true;
$mail->SMTPKeepAlive = true;                  // SMTP connection will not close after each email sent, reduces SMTP overhead
$mail->Host          = 'mail.example.com';
$mail->Port          = 25;
$mail->Username      = 'yourname@example.com';
$mail->Password      = 'yourpassword';
$mail->SetFrom('list@example.com', 'List manager');
$mail->AddReplyTo('list@example.com', 'List manager');

$mail->Subject       = "PHPMailer Simple database mailing list test";

//Connect to the database and select the recipients from your mailing list that have not yet been sent to
//You'll need to alter this to match your database
$mysql = mysql_connect('localhost', 'username', 'password');
mysql_select_db('mydb', $mysql);
$result = mysql_query("SELECT full_name, email, photo FROM mailinglist WHERE sent = false", $mysql);

while ($row = mysql_fetch_array($result)) {
  $mail->AltBody    = 'To view the message, please use an HTML compatible email viewer!';
  $mail->MsgHTML($body);
  $mail->AddAddress($row['email'], $row['full_name']);
  $mail->AddStringAttachment($row['photo'], 'YourPhoto.jpg'); //Assumes the image data is stored in the DB

  if(!$mail->Send()) {
    echo "Mailer Error (" . str_replace("@", "&#64;", $row["email"]) . ') ' . $mail->ErrorInfo . '<br />';
    break; //Abandon sending
  } else {
    echo "Message sent to :" . $row['full_name'] . ' (' . str_replace("@", "&#64;", $row['email']) . ')<br />';
    //Mark it as sent in the DB
    mysql_query("UPDATE mailinglist SET sent = true WHERE email = '".mysql_real_escape_string($row['email'], $mysql)."'");
  }
  // Clear all addresses and attachments for next loop
  $mail->ClearAddresses();
  $mail->ClearAttachments();
}
