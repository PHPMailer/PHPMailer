<?php
/*
*	This is a simple example to send mail with phpmailer quickly, using a nice and simple function.
*/

function send_mail($data, $recipients, $subject, $body) { 
	/* 	
	*	Using parameters:
	
	*	$data as Array('host'=>host, 'user'=>username, 'pass'=>password123, 'name'=>Lucas), 
	*	$recipients as Array(), 
	*	$subject as String,
	*	$body as String(html doc);
	*/
	require("phpmailer/class.phpmailer.php"); 	   // Require the awesome class.phpmailer file
	
	$mail = new PHPMailer();
	$mail->isSMTP();  							   // Set mailer to use SMTP
	$mail->Host = $data['host'];				   // Specify main and backup SMTP servers
	$mail->SMTPAuth = true;                 	   // Enable SMTP authentication
	$mail->Username = $data['user'];			   // SMTP username
	$mail->Password = $data['pass'];			   // SMTP password
	$mail->From = $data['user'];				   
	$mail->FromName = $data['name'];

	foreach ($recipients as $recipient => $name) { // Add all recipients
		$mail->AddAddress($recipient, $name);	   // Add a recipient
	}

	$mail->IsHTML(true);					 	   // Set email format to HTML
	$mail->Subject = $subject;					   // Here is the subject
	$mail->Body ='<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>'.$subject.'</title></head><body>'.$body.'</body></html>';							// Mail HTML document
	$sent = $mail->Send();
	$mail->ClearAllRecipients();
	$mail->ClearAttachments();
return $sent;
}
?> 