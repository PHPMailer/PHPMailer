<?php
require_once ("PHPMailer/class.phpmailer.php");

class Mailer {
    public function Send ($to, $subject, $tpl, $data = null) {
        global $mailerHost, $mailerUser, $mailerPassword, $mailerFrom, $mailerFromEmail;

        if (empty($mailerHost))
            $mailerHost = "localhost";

        if (empty ($to)) {
            echo "'To' field is empty";
            return false;
        }
        // Retrieve the email template required
        $tpl = preg_replace('/[^a-z0-9 \.]/i', '', $tpl);
        $message = file_get_contents ("tpl/email/$tpl.html");
        if (empty ($message)) {
            echo "$tpl not found";
            return false;
        }

        // Replace the % with the actual information
        if ($data) {
            foreach ($data as $key => $val)
                $message = str_replace ("%{$key}%", $val, $message);
        }

        $mail = new PHPMailer();
        $mail->IsSMTP();
        // This is the SMTP mail server
        $mail->Host = $mailerHost;

        // Remove these next 3 lines if you dont need SMTP authentication
        if (!empty ($mailerUser) && !empty ($mailerPassword)) {
            $mail->SMTPAuth = true;
            $mail->Username = $mailerUser;
            $mail->Password = $mailerPassword;
        }

        // Set who the email is coming from
        $mail->SetFrom ($mailerFromEmail, $mailerFrom);

        // Set who the email is sending to
        $mail->AddAddress($to);

        // Set the subject
        $mail->Subject = $subject;

        //Set the message
        $mail->MsgHTML($message);
        //$mail->AltBody(strip_tags($message));

        // Send the email
        if(!$mail->Send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
            return false;
        }
        return true;
    }
}
?>
