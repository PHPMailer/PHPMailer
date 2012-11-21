<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>PHPMailer Test Page</title>
<script type="text/javascript" src="scripts/shCore.js"></script>
<script type="text/javascript" src="scripts/shBrushPhp.js"></script>
<link type="text/css" rel="stylesheet" href="styles/shCore.css"/>
<link type="text/css" rel="stylesheet" href="styles/shThemeDefault.css"/>
<script type="text/javascript">
  SyntaxHighlighter.config.clipboardSwf = 'scripts/clipboard.swf';
  SyntaxHighlighter.all();
</script>
</head>
<body >
<?php

echo 'Current PHP version: ' . phpversion() . "<br />";

if ( substr(phpversion(),0,1) < 5 ) { echo exit("ERROR: Wrong PHP version"); }

$CFG['smtp_debug']        = 1;
$CFG['smtp_server']       = 'mail.yourserver.com';
$CFG['smtp_port']         = '25';
$CFG['smtp_authenticate'] = 'true';
$CFG['smtp_username']     = 'name@yourserver.com';
$CFG['smtp_password']     = 'yourpassword';

if ( $_POST['submit'] == "Submit" ) {

  class phpmailerAppException extends Exception {
    public function errorMessage() {
      $errorMsg = '<strong>' . $this->getMessage() . "</strong><br />";
      return $errorMsg;
    }
  }

  try {
    $to = $_POST['To_Email'];
    if(filter_var($to, FILTER_VALIDATE_EMAIL) === FALSE) {
      throw new phpmailerAppException("Email address " . $to . " is invalid -- aborting!<br />");
    }
  } catch (phpmailerAppException $e) {
    echo $e->errorMessage();
    return false;
  }

  require_once("../class.phpmailer.php");

  $mail = new PHPMailer();

  if ( $_POST['Message'] == '' ) {
    $body             = file_get_contents('contents.html');
  } else {
    $body = $_POST['Message'];
  }

  if ( $_POST['test_type'] == "smtp" ) {
    $mail->IsSMTP();  // telling the class to use SMTP
    $mail->SMTPDebug  = $_POST['smtp_debug'];
    $mail->SMTPAuth   = $_POST['smtp_authenticate'];     // enable SMTP authentication
    $mail->Port       = $_POST['smtp_port'];             // set the SMTP port
    $mail->Host       = $_POST['smtp_server'];           // SMTP server
    $mail->Username   = $_POST['authenticate_username']; // SMTP account username
    $mail->Password   = $_POST['authenticate_password']; // SMTP account password
  } elseif ( $_POST['test_type'] == "mail" ) {
    $mail->IsMail();      // telling the class to use PHP's Mail()
  } elseif ( $_POST['test_type'] == "sendmail" ) {
    $mail->IsSendmail();  // telling the class to use Sendmail
  } elseif ( $_POST['test_type'] == "qmail" ) {
    $mail->IsQmail();     // telling the class to use Qmail
  }

  if ( $_POST['From_Name'] != '' ) {
    $mail->AddReplyTo($_POST['From_Email'],$_POST['From_Name']);
    $mail->From       = $_POST['From_Email'];
    $mail->FromName   = $_POST['From_Name'];
  } else {
    $mail->AddReplyTo($_POST['From_Email']);
    $mail->From       = $_POST['From_Email'];
    $mail->FromName   = $_POST['From_Email'];
  }

  if ( $_POST['To_Name'] != '' ) {
    $mail->AddAddress($to,$_POST['To_Name']);
  } else {
    $mail->AddAddress($to);
  }

  if ( $_POST['bcc_Email'] != '' ) {
    $indiBCC = explode(" ", $_POST['bcc_Email']);
    foreach ($indiBCC as $key => $value) {
      $mail->AddBCC($value);
    }
  }

  if ( $_POST['cc_Email'] != '' ) {
    $indiCC = explode(" ", $_POST['cc_Email']);
    foreach ($indiCC as $key => $value) {
      $mail->AddCC($value);
    }
  }

  $mail->Subject  = $_POST['Subject'] . ' (PHPMailer test using ' . strtoupper($_POST['test_type']) . ')';

  // below can be found at http://www.chuggnutt.com/html2text
  //  bundled in ./extras/
  require_once('../class.html2text.inc');
  $h2t =& new html2text($body);
  $mail->AltBody = $h2t->get_text();
  //$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
  $mail->WordWrap   = 80; // set word wrap

  $mail->MsgHTML($body);

  // $mail->IsHTML(true); // send as HTML

  $mail->AddAttachment("images/aikido.gif", "aikido.gif");  // optional name
  $mail->AddAttachment("images/phpmailer.gif", "phpmailer.gif");  // optional name

  try {
    if ( !$mail->Send() ) {
      $error = "Unable to send to: " . $to . "<br />";
      throw new phpmailerAppException($error);
    } else {
      echo 'Message has been sent using ' . strtoupper($_POST['test_type']) . "<br /><br />";
    }
  }
  catch (phpmailerAppException $e) {
    $errorMsg[] = $e->errorMessage();
  }

  if ( count($errorMsg) > 0 ) {
    foreach ($errorMsg as $key => $value) {
      $thisError = $key + 1;
      echo $thisError . ': ' . $value;
    }
  }
  ?>
  <form method="POST" enctype="multipart/form-data">
  <?php $value = ( $_POST['From_Name'] != '' ) ? $_POST['From_Name'] : ''; ?>
  <input type="hidden" name="From_Name" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['From_Email'] != '' ) ? $_POST['From_Email'] : ''; ?>
  <input type="hidden" name="From_Email" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['To_Name'] != '' ) ? $_POST['To_Name'] : ''; ?>
  <input type="hidden" name="To_Name" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['To_Email'] != '' ) ? $_POST['To_Email'] : ''; ?>
  <input type="hidden" name="To_Email" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['cc_Email'] != '' ) ? $_POST['cc_Email'] : ''; ?>
  <input type="hidden" name="cc_Email" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['bcc_Email'] != '' ) ? $_POST['bcc_Email'] : ''; ?>
  <input type="hidden" name="bcc_Email" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['Subject'] != '' ) ? $_POST['Subject'] : ''; ?>
  <input type="hidden" name="Subject" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['Message'] != '' ) ? $_POST['Message'] : ''; ?>
  <input type="hidden" name="Message" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['test_type'] != '' ) ? $_POST['test_type'] : 'mail'; ?>
  <input type="hidden" name="test_type" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['smtp_debug'] != '' ) ? $_POST['smtp_debug'] : $CFG['smtp_debug']; ?>
  <input type="hidden" name="smtp_debug" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['smtp_server'] != '' ) ? $_POST['smtp_server'] : $CFG['smtp_server']; ?>
  <input type="hidden" name="smtp_server" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['smtp_port'] != '' ) ? $_POST['smtp_port'] : $CFG['smtp_port']; ?>
  <input type="hidden" name="smtp_port" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['smtp_authenticate'] != '' ) ? $_POST['smtp_authenticate'] : $CFG['smtp_authenticate']; ?>
  <input type="hidden" name="smtp_authenticate" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['authenticate_username'] != '' ) ? $_POST['authenticate_username'] : $CFG['smtp_username']; ?>
  <input type="hidden" name="authenticate_username" value="<?php echo $value; ?>">
  <?php $value = ( $_POST['authenticate_password'] != '' ) ? $_POST['authenticate_password'] : $CFG['smtp_password']; ?>
  <input type="hidden" name="authenticate_password" value="<?php echo $value; ?>">
  <input type="submit" value="Start Over" name="submit">
  </form><br />
  <br />
  Script:<br />
<pre class="brush: php;">
class phpmailerAppException extends Exception {
  public function errorMessage() {
    $errorMsg = '<strong>' . $this->getMessage() . "</strong><br />";
    return $errorMsg;
  }
}

try {
  $to = <?php echo $_POST['To_Email']; ?>;
  if(filter_var($to, FILTER_VALIDATE_EMAIL) === FALSE) {
    throw new phpmailerAppException("Email address " . $to . " is invalid -- aborting!<br />");
  }
} catch (phpmailerAppException $e) {
  echo $e->errorMessage();
  return false;
}

require_once("../class.phpmailer.php");

$mail = new PHPMailer();

<?php
if ( $_POST['Message'] == '' ) {
  echo '$body             = file_get_contents(\'contents.html\');' . "\n";
} else {
  echo '$body = ' . $_POST['Message'] . "\n";
}

echo "\n";

if ( $_POST['test_type'] == "smtp" ) {
  echo '$mail->IsSMTP();  // telling the class to use SMTP' . "\n";
  echo '$mail->SMTPDebug  = ' . $_POST['smtp_debug'] . "\n";
  echo '$mail->SMTPAuth   = ' . $_POST['smtp_authenticate'];     // enable SMTP authentication' . "\n";
  echo '$mail->Port       = ' . $_POST['smtp_port'];             // set the SMTP port' . "\n";
  echo '$mail->Host       = ' . $_POST['smtp_server'];           // SMTP server' . "\n";
  echo '$mail->Username   = ' . $_POST['authenticate_username']; // SMTP account username' . "\n";
  echo '$mail->Password   = ' . $_POST['authenticate_password']; // SMTP account password' . "\n";
} elseif ( $_POST['test_type'] == "mail" ) {
  echo '$mail->IsMail();      // telling the class to use PHP\'s Mail()' . "\n";
} elseif ( $_POST['test_type'] == "sendmail" ) {
  echo '$mail->IsSendmail();  // telling the class to use Sendmail' . "\n";
} elseif ( $_POST['test_type'] == "qmail" ) {
  echo '$mail->IsQmail();     // telling the class to use Qmail' . "\n";
}
?>

$mail->AddReplyTo('<?php echo $_POST['From_Email']; ?>','<?php echo $_POST['From_Name']; ?>');

$mail->From       = '<?php echo $_POST['From_Email']; ?>';
$mail->FromName   = '<?php echo $_POST['From_Name']; ?>';

<?php
if ( $_POST['To_Name'] != '' ) {
  ?>
$mail->AddAddress('<?php echo $to; ?>','<?php echo $_POST['To_Name']; ?>');
  <?php
} else {
  ?>
$mail->AddAddress('<?php echo $to; ?>');
  <?php
}
if ( $_POST['bcc_Email'] != '' ) {
  $indiBCC = explode(" ", $_POST['bcc_Email']);
  foreach ($indiBCC as $key => $value) {
echo '$mail->AddBCC(\'' . $value . '\');<br />';
  }
}

if ( $_POST['cc_Email'] != '' ) {
  $indiCC = explode(" ", $_POST['cc_Email']);
  foreach ($indiCC as $key => $value) {
echo '$mail->AddCC(\'' . $value . '\');<br />';
  }
}
?>

$mail->Subject  = <?php echo $_POST['Subject']; ?> (PHPMailer test using <?php echo strtoupper($_POST['test_type']); ?>)

require_once('../class.html2text.inc');
$h2t =& new html2text($body);
$mail->AltBody = $h2t->get_text();
$mail->WordWrap   = 80; // set word wrap

$mail->MsgHTML($body);

$mail->AddAttachment("images/aikido.gif", "aikido.gif");  // optional name
$mail->AddAttachment("images/phpmailer.gif", "phpmailer.gif");  // optional name

try {
  if ( !$mail->Send() ) {
    $error = "Unable to send to: " . $to . "<br />";
    throw new phpmailerAppException($error);
  } else {
    echo 'Message has been sent using <?php echo strtoupper($_POST['test_type']); ?><br /><br />';
  }
} catch (phpmailerAppException $e) {
  $errorMsg[] = $e->errorMessage();
}

if ( count($errorMsg) > 0 ) {
  foreach ($errorMsg as $key => $value) {
    $thisError = $key + 1;
    echo $thisError . ': ' . $value;
  }
}
</pre>



  <?php
} else {
  ?>
  <style>
  body {
    font-family: Arial, Helvetica, Sans-Serif;
    font-size: 11px;
  }
  td {
    font-size: 11px;
  }
  td.colleft {
    align: right;
    text-align: right;
    width: 30%;
  }
  td.colrite {
    text-align: left;
    width: 70%;
  }
  </style>
  <form method="POST" enctype="multipart/form-data">
  <table border="1" width="900" cellspacing="0" cellpadding="5" style="border-collapse: collapse" bgcolor="#C0C0C0">
    <tr>
      <td valign="top";><strong>Message</strong><br /><br />
        <table border="1" width="450" cellspacing="0" cellpadding="5" style="border-collapse: collapse;" bgcolor="#FFFFFF">
          <tr>
            <td class="colleft">From Name</td>
            <?php $value = ( $_POST['From_Name'] != '' ) ? $_POST['From_Name'] : ''; ?>
            <td class="colrite"><input type="text" name="From_Name" value="<?php echo $value; ?>" style="width:99%;"></td>
          </tr>
          <tr>
            <td class="colleft">From Email Address</td>
            <?php $value = ( $_POST['From_Email'] != '' ) ? $_POST['From_Email'] : ''; ?>
            <td class="colrite"><input type="text" name="From_Email" value="<?php echo $value; ?>" style="width:99%;"></td>
          </tr>
          <tr>
            <td class="colleft">To Name</td>
            <?php $value = ( $_POST['To_Name'] != '' ) ? $_POST['To_Name'] : ''; ?>
            <td class="colrite"><input type="text" name="To_Name" value="<?php echo $value; ?>" style="width:99%;"></td>
          </tr>
          <tr>
            <td class="colleft">To Email Address</td>
            <?php $value = ( $_POST['To_Email'] != '' ) ? $_POST['To_Email'] : ''; ?>
            <td class="colrite"><input type="text" name="To_Email" value="<?php echo $value; ?>" style="width:99%;"></td>
          </tr>
          <tr>
            <td class="colleft">cc Email Addresses <small>(separate with commas)</small></td>
            <?php $value = ( $_POST['cc_Email'] != '' ) ? $_POST['cc_Email'] : ''; ?>
            <td class="colrite"><input type="text" name="cc_Email" value="<?php echo $value; ?>" style="width:99%;"></td>
          </tr>
          <tr>
            <td class="colleft">bcc Email Addresses <small>(separate with commas)</small></td>
            <?php $value = ( $_POST['bcc_Email'] != '' ) ? $_POST['bcc_Email'] : ''; ?>
            <td class="colrite"><input type="text" name="bcc_Email" value="<?php echo $value; ?>" style="width:99%;"></td>
          </tr>
          <tr>
            <td class="colleft">Subject</td>
            <?php $value = ( $_POST['Subject'] != '' ) ? $_POST['Subject'] : ''; ?>
            <td class="colrite"><input type="text" name="Subject" value="<?php echo $value; ?>" style="width:99%;"></td>
          </tr>
          <tr>
            <td class="colleft">Message<br /><small>If blank, will use content.html</small></td>
            <?php $value = ( $_POST['Message'] != '' ) ? $_POST['Message'] : ''; ?>
            <td class="colrite"><textarea name="Message" style="width:99%;height:50px;"><?php echo $value; ?></textarea></td>
          </tr>
        </table>
      </td>
      <td valign="top"><strong>Mail Test Specs</strong><br /><br />
        <table border="1" width="450" cellspacing="0" cellpadding="5" style="border-collapse: collapse;" bgcolor="#FFFFFF">
          <tr>
            <td class="colleft">Test Type</td>
            <td class="colrite"><table>
                <tr>
                  <td><input type="radio" name="test_type" value="mail" <?php echo ( $_POST['test_type'] == 'mail') ? 'checked' : ''; ?>></td>
                  <td>Mail()</td>
                </tr>
                <tr>
                  <td><input type="radio" name="test_type" value="sendmail" <?php echo ( $_POST['test_type'] == 'sendmail') ? 'checked' : ''; ?>></td>
                  <td>Sendmail</td>
                </tr>
                <tr>
                  <td><input type="radio" name="test_type" value="qmail" <?php echo ( $_POST['test_type'] == 'qmail') ? 'checked' : ''; ?>></td>
                  <td>Qmail</td>
                </tr>
                <tr>
                  <td><input type="radio" name="test_type" value="smtp" <?php echo ( $_POST['test_type'] == 'smtp') ? 'checked' : ''; ?>></td>
                  <td>SMTP</td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
        If SMTP test:<br />
        <table border="1" width="450" cellspacing="0" cellpadding="5" style="border-collapse: collapse;" bgcolor="#FFFFFF">
          <tr>
            <td class="colleft">SMTP Debug ?</td>
            <?php $value = ( $_POST['smtp_debug'] != '' ) ? $_POST['smtp_debug'] : $CFG['smtp_debug']; ?>
            <td class="colrite"><select size="1" name="smtp_debug">
              <option <?php echo ( $value == '0') ? 'selected' : ''; ?> value="0">0 - Disabled</option>
              <option <?php echo ( $value == '1') ? 'selected' : ''; ?> value="1">1 - Errors and Messages</option>
              <option <?php echo ( $value == '2') ? 'selected' : ''; ?> value="2">2 - Messages only</option>
              </select></td>
          </tr>
          <tr>
            <td class="colleft">SMTP Server</td>
            <?php $value = ( $_POST['smtp_server'] != '' ) ? $_POST['smtp_server'] : $CFG['smtp_server']; ?>
            <td class="colrite"><input type="text" name="smtp_server" value="<?php echo $value; ?>" style="width:99%;"></td>
          </tr>
          <tr>
            <td class="colleft">SMTP Port</td>
            <?php $value = ( $_POST['smtp_port'] != '' ) ? $_POST['smtp_port'] : $CFG['smtp_port']; ?>
            <td class="colrite"><input type="text" name="smtp_port" value="<?php echo $value; ?>" style="width:99%;"></td>
          </tr>
          <tr>
            <td class="colleft">SMTP Authenticate ?</td>
            <?php $value = ( $_POST['smtp_authenticate'] != '' ) ? $_POST['smtp_authenticate'] : $CFG['smtp_authenticate']; ?>
            <td class="colrite"><input type="checkbox" name="smtp_authenticate" <?php if ($value!=''){ echo "checked";} ?> value="<?php echo $value; ?>"></td>
          </tr>
          <tr>
            <td class="colleft">Authenticate Username</td>
            <?php $value = ( $_POST['authenticate_username'] != '' ) ? $_POST['authenticate_username'] : $CFG['smtp_username']; ?>
            <td class="colrite"><input type="text" name="authenticate_username" value="<?php echo $value; ?>" style="width:99%;"></td>
          </tr>
          <tr>
            <td class="colleft">Authenticate Password</td>
            <?php $value = ( $_POST['authenticate_password'] != '' ) ? $_POST['authenticate_password'] : $CFG['smtp_password']; ?>
            <td class="colrite"><input type="password" name="authenticate_password" value="<?php echo $value; ?>" style="width:99%;"></td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
  <br />
  Test will include two attachments, plus one of the attachments is used as an inline graphic in the message body.<br />
  <br />
  <input type="submit" value="Submit" name="submit">
  </form>
  <?php
}

?>
