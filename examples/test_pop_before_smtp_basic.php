<html>
<head>
<title>POP before SMTP Test</title>
</head>
<body>

<?php
require_once('../class.phpmailer.php');
require_once('../class.pop3.php'); // required for POP before SMTP

$pop = new POP3();
$pop->Authorise('pop3.yourdomain.com', 110, 30, 'username', 'password', 1);

$mail = new PHPMailer();

$body             = file_get_contents('contents.html');
$body             = eregi_replace("[\]",'',$body);

$mail->IsSMTP();
$mail->SMTPDebug = 2;
$mail->Host     = 'pop3.yourdomain.com';

$mail->SetFrom('name@yourdomain.com', 'First Last');

$mail->AddReplyTo("name@yourdomain.com","First Last");

$mail->Subject    = "PHPMailer Test Subject via POP before SMTP, basic";

$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

$mail->MsgHTML($body);

$address = "whoto@otherdomain.com";
$mail->AddAddress($address, "John Doe");

$mail->AddAttachment("images/phpmailer.gif");      // attachment
$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment


if(!$mail->Send()) {
  echo "Mailer Error: " . $mail->ErrorInfo;
} else {
  echo "Message sent!";
}

?>

</body>
</html>
