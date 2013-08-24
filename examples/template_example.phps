<?php
// Mail notifications settings
$mailerFrom = "your service";
$mailerFromEmail = "noreply@yourservice.com";


include "lib/Mailer.php";

Mailer::Send("test@test.com","test subject", "test", Array("username" => "testuser", "password" => "testpass"));

?>
