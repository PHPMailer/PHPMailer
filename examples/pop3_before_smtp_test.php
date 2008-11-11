<html>
<head>
<title>POP before SMTP Test</title>
</head>

<body>

<pre>
<?php
  require 'class.phpmailer.php';
  require 'class.pop3.php';

  $pop = new POP3();
  $pop->Authorise('pop3.example.com', 110, 30, 'mailer', 'password', 1);

  $mail = new PHPMailer();

  $mail->IsSMTP();
  $mail->SMTPDebug = 2;
  $mail->IsHTML(false);

  $mail->Host     = 'relay.example.com';

  $mail->From     = 'mailer@example.com';
  $mail->FromName = 'Example Mailer';

  $mail->Subject  =  'My subject';
  $mail->Body     =  'Hello world';
  $mail->AddAddress('name@anydomain.com', 'First Last');

  if (!$mail->Send())
  {
    echo $mail->ErrorInfo;
  }
?>
</pre>

</body>
</html>
