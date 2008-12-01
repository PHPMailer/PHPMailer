<?php

include_once('../class.phpmailer.php');

$mail    = new PHPMailer();
$mail->isSMTP();

// $mail->SMTPDebug = true;

$body    = $mail->getFile('contents.html');

$body    = eregi_replace("[\]",'',$body);
$subject = eregi_replace("[\]",'',$subject);

$mail->From     = "rabbitt@tranquillo.net";
$mail->FromName = "Carl P. Corliss";

$mail->Subject = "PHPMailer Test Subject";

$mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

$mail->MsgHTML($body);

for ($i = 1; $i <= 50; $i++) {
	$mail->AddAddress("rabbitt+test-$i@gmail.com", "Carl P. Corliss (test #$i)");
	$mail->AddCC("rabbitt+test-".($i+50)."@tranquillo.net", "Carl P. Corliss (test #".($i+50).")");
}

if(!$mail->Send()) {
  echo 'Failed to send mail';
} else {
  echo 'Mail sent';
}

