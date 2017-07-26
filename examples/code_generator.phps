<?php
/*
 * A web form that both generates and uses PHPMailer code.
 * revised, updated and corrected 27/02/2013
 * by matt.sturdy@gmail.com
 */
require '../PHPMailerAutoload.php';

$CFG['smtp_debug'] = 2; //0 == off, 1 for client output, 2 for client and server
$CFG['smtp_debugoutput'] = 'html';
$CFG['smtp_server'] = 'localhost';
$CFG['smtp_port'] = '25';
$CFG['smtp_authenticate'] = false;
$CFG['smtp_username'] = 'name@example.com';
$CFG['smtp_password'] = 'yourpassword';
$CFG['smtp_secure'] = 'None';

$from_name = (isset($_POST['From_Name'])) ? $_POST['From_Name'] : '';
$from_email = (isset($_POST['From_Email'])) ? $_POST['From_Email'] : '';
$to_name = (isset($_POST['To_Name'])) ? $_POST['To_Name'] : '';
$to_email = (isset($_POST['To_Email'])) ? $_POST['To_Email'] : '';
$cc_email = (isset($_POST['cc_Email'])) ? $_POST['cc_Email'] : '';
$bcc_email = (isset($_POST['bcc_Email'])) ? $_POST['bcc_Email'] : '';
$subject = (isset($_POST['Subject'])) ? $_POST['Subject'] : '';
$message = (isset($_POST['Message'])) ? $_POST['Message'] : '';
$test_type = (isset($_POST['test_type'])) ? $_POST['test_type'] : 'smtp';
$smtp_debug = (isset($_POST['smtp_debug'])) ? $_POST['smtp_debug'] : $CFG['smtp_debug'];
$smtp_server = (isset($_POST['smtp_server'])) ? $_POST['smtp_server'] : $CFG['smtp_server'];
$smtp_port = (isset($_POST['smtp_port'])) ? $_POST['smtp_port'] : $CFG['smtp_port'];
$smtp_secure = strtolower((isset($_POST['smtp_secure'])) ? $_POST['smtp_secure'] : $CFG['smtp_secure']);
$smtp_authenticate = (isset($_POST['smtp_authenticate'])) ?
    $_POST['smtp_authenticate'] : $CFG['smtp_authenticate'];
$authenticate_password = (isset($_POST['authenticate_password'])) ?
    $_POST['authenticate_password'] : $CFG['smtp_password'];
$authenticate_username = (isset($_POST['authenticate_username'])) ?
    $_POST['authenticate_username'] : $CFG['smtp_username'];

// storing all status output from the script to be shown to the user later
$results_messages = array();

// $example_code represents the "final code" that we're using, and will
// be shown to the user at the end.
$example_code = "\nrequire_once '../PHPMailerAutoload.php';";
$example_code .= "\n\n\$results_messages = array();";

$mail = new PHPMailer(true);  //PHPMailer instance with exceptions enabled
$mail->CharSet = 'utf-8';
ini_set('default_charset', 'UTF-8');
$mail->Debugoutput = $CFG['smtp_debugoutput'];
$example_code .= "\n\n\$mail = new PHPMailer(true);";
$example_code .= "\n\$mail->CharSet = 'utf-8';";
$example_code .= "\nini_set('default_charset', 'UTF-8');";

class phpmailerAppException extends phpmailerException
{
}

$example_code .= "\n\nclass phpmailerAppException extends phpmailerException {}";
$example_code .= "\n\ntry {";

// Convert a string to its JavaScript representation.
function JSString($s) {
  static $from = array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"');
  static $to = array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\\"');
  return is_null($s)? 'null': '"' . str_replace($from, $to, "$s") . '"';
}

try {
    if (isset($_POST["submit"]) && $_POST['submit'] == "Submit") {
        $to = $to_email;
        if (!PHPMailer::validateAddress($to)) {
            throw new phpmailerAppException("Email address " . $to . " is invalid -- aborting!");
        }

        $example_code .= "\n\$to = '" . addslashes($to_email) . "';";
        $example_code .= "\nif(!PHPMailer::validateAddress(\$to)) {";
        $example_code .= "\n  throw new phpmailerAppException(\"Email address \" . " .
            "\$to . \" is invalid -- aborting!\");";
        $example_code .= "\n}";

        switch ($test_type) {
            case 'smtp':
                $mail->isSMTP(); // telling the class to use SMTP
                $mail->SMTPDebug = (integer)$smtp_debug;
                $mail->Host = $smtp_server; // SMTP server
                $mail->Port = (integer)$smtp_port; // set the SMTP port
                if ($smtp_secure) {
                    $mail->SMTPSecure = strtolower($smtp_secure);
                }
                $mail->SMTPAuth = array_key_exists('smtp_authenticate', $_POST); // enable SMTP authentication?
                if (array_key_exists('smtp_authenticate', $_POST)) {
                    $mail->Username = $authenticate_username; // SMTP account username
                    $mail->Password = $authenticate_password; // SMTP account password
                }

                $example_code .= "\n\$mail->isSMTP();";
                $example_code .= "\n\$mail->SMTPDebug  = " . (integer) $smtp_debug . ";";
                $example_code .= "\n\$mail->Host       = \"" . addslashes($smtp_server) . "\";";
                $example_code .= "\n\$mail->Port       = \"" . addslashes($smtp_port) . "\";";
                $example_code .= "\n\$mail->SMTPSecure = \"" . addslashes(strtolower($smtp_secure)) . "\";";
                $example_code .= "\n\$mail->SMTPAuth   = " . (array_key_exists(
                    'smtp_authenticate',
                    $_POST
                ) ? 'true' : 'false') . ";";
                if (array_key_exists('smtp_authenticate', $_POST)) {
                    $example_code .= "\n\$mail->Username   = \"" . addslashes($authenticate_username) . "\";";
                    $example_code .= "\n\$mail->Password   = \"" . addslashes($authenticate_password) . "\";";
                }
                break;
            case 'mail':
                $mail->isMail(); // telling the class to use PHP's mail()
                $example_code .= "\n\$mail->isMail();";
                break;
            case 'sendmail':
                $mail->isSendmail(); // telling the class to use Sendmail
                $example_code .= "\n\$mail->isSendmail();";
                break;
            case 'qmail':
                $mail->isQmail(); // telling the class to use Qmail
                $example_code .= "\n\$mail->isQmail();";
                break;
            default:
                throw new phpmailerAppException('Invalid test_type provided');
        }

        try {
            if ($_POST['From_Name'] != '') {
                $mail->addReplyTo($from_email, $from_name);
                $mail->setFrom($from_email, $from_name);

                $example_code .= "\n\$mail->addReplyTo(\"" .
                    addslashes($from_email) . "\", \"" . addslashes($from_name) . "\");";
                $example_code .= "\n\$mail->setFrom(\"" .
                    addslashes($from_email) . "\", \"" . addslashes($from_name) . "\");";
            } else {
                $mail->addReplyTo($from_email);
                $mail->setFrom($from_email, $from_email);

                $example_code .= "\n\$mail->addReplyTo(\"" . addslashes($from_email) . "\");";
                $example_code .= "\n\$mail->setFrom(\"" .
                    addslashes($from_email) . "\", \"" . addslashes($from_email) . "\");";
            }

            if ($_POST['To_Name'] != '') {
                $mail->addAddress($to, $to_name);
                $example_code .= "\n\$mail->addAddress(\"$to\", \"" . addslashes($to_name) . "\");";
            } else {
                $mail->addAddress($to);
                $example_code .= "\n\$mail->addAddress(\"$to\");";
            }

            if ($_POST['bcc_Email'] != '') {
                $indiBCC = explode(" ", $bcc_email);
                foreach ($indiBCC as $key => $value) {
                    $mail->addBCC($value);
                    $example_code .= "\n\$mail->addBCC(\"" . addslashes($value) . "\");";
                }
            }

            if ($_POST['cc_Email'] != '') {
                $indiCC = explode(" ", $cc_Email);
                foreach ($indiCC as $key => $value) {
                    $mail->addCC($value);
                    $example_code .= "\n\$mail->addCC(\"" . addslashes($value) . "\");";
                }
            }
        } catch (phpmailerException $e) { //Catch all kinds of bad addressing
            throw new phpmailerAppException($e->getMessage());
        }
        $mail->Subject = $subject . ' (PHPMailer test using ' . strtoupper($test_type) . ')';
        $example_code .= "\n\$mail->Subject  = \"" . addslashes($subject) .
            ' (PHPMailer test using ' . addslashes(strtoupper($test_type)) . ')";';

        if ($_POST['Message'] == '') {
            $body = file_get_contents('contents.html');
        } else {
            $body = $message;
        }

        $example_code .= "\n\$body = <<<'EOT'\n$body\nEOT;";

        $mail->WordWrap = 78; // set word wrap to the RFC2822 limit
        $mail->msgHTML($body, dirname(__FILE__), true); //Create message bodies and embed images

        $example_code .= "\n\$mail->WordWrap = 78;";
        $example_code .= "\n\$mail->msgHTML(\$body, dirname(__FILE__), true); //Create message bodies and embed images";

        $mail->addAttachment('images/phpmailer_mini.png', 'phpmailer_mini.png'); // optional name
        $mail->addAttachment('images/phpmailer.png', 'phpmailer.png'); // optional name
        $example_code .= "\n\$mail->addAttachment('images/phpmailer_mini.png'," .
            "'phpmailer_mini.png');  // optional name";
        $example_code .= "\n\$mail->addAttachment('images/phpmailer.png', 'phpmailer.png');  // optional name";

        $example_code .= "\n\ntry {";
        $example_code .= "\n  \$mail->send();";
        $example_code .= "\n  \$results_messages[] = \"Message has been sent using " .
            addslashes(strtoupper($test_type)) . "\";";
        $example_code .= "\n}";
        $example_code .= "\ncatch (phpmailerException \$e) {";
        $example_code .= "\n  throw new phpmailerAppException('Unable to send to: ' . \$to. ': '.\$e->getMessage());";
        $example_code .= "\n}";

        try {
            $mail->send();
            $results_messages[] = "Message has been sent using " . strtoupper($test_type);
        } catch (phpmailerException $e) {
            throw new phpmailerAppException("Unable to send to: " . $to . ': ' . $e->getMessage());
        }
    }
} catch (phpmailerAppException $e) {
    $results_messages[] = $e->errorMessage();
}
$example_code .= "\n}";
$example_code .= "\ncatch (phpmailerAppException \$e) {";
$example_code .= "\n  \$results_messages[] = \$e->errorMessage();";
$example_code .= "\n}";
$example_code .= "\n\nif (count(\$results_messages) > 0) {";
$example_code .= "\n  echo \"<h2>Run results</h2>\\n\";";
$example_code .= "\n  echo \"<ul>\\n\";";
$example_code .= "\nforeach (\$results_messages as \$result) {";
$example_code .= "\n  echo \"<li>\$result</li>\\n\";";
$example_code .= "\n}";
$example_code .= "\necho \"</ul>\\n\";";
$example_code .= "\n}";
?><!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>PHPMailer Test Page</title>
    <script type="text/javascript" src="scripts/shCore.js"></script>
    <script type="text/javascript" src="scripts/shBrushPhp.js"></script>
    <link type="text/css" rel="stylesheet" href="styles/shCore.css">
    <link type="text/css" rel="stylesheet" href="styles/shThemeDefault.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 1em;
            padding: 1em;
        }

        table {
            margin: 0 auto;
            border-spacing: 0;
            border-collapse: collapse;
        }

        table.column {
            border-collapse: collapse;
            background-color: #FFFFFF;
            padding: 0.5em;
            width: 35em;
        }

        td {
            font-size: 1em;
            padding: 0.1em 0.25em;
            -moz-border-radius: 1em;
            -webkit-border-radius: 1em;
            border-radius: 1em;
        }

        td.colleft {
            text-align: right;
            width: 35%;
        }

        td.colrite {
            text-align: left;
            width: 65%;
        }

        fieldset {
            padding: 1em 1em 1em 1em;
            margin: 0 2em;
            border-radius: 1.5em;
            -webkit-border-radius: 1em;
            -moz-border-radius: 1em;
        }

        fieldset.inner {
            width: 40%;
        }

        fieldset:hover, tr:hover {
            background-color: #fafafa;
        }

        legend {
            font-weight: bold;
            font-size: 1.1em;
        }

        div.column-left {
            float: left;
            width: 45em;
            height: 31em;
        }

        div.column-right {
            display: inline;
            width: 45em;
            max-height: 31em;
        }

        input.radio {
            float: left;
        }

        div.radio {
            padding: 0.2em;
        }
    </style>
    <script>
        SyntaxHighlighter.config.clipboardSwf = 'scripts/clipboard.swf';
        SyntaxHighlighter.all();

        function startAgain() {
            var post_params = {
                "From_Name": <?php echo JSString($from_name); ?>,
                "From_Email": <?php echo JSString($from_email); ?>,
                "To_Name": <?php echo JSString($to_name); ?>,
                "To_Email": <?php echo JSString($to_email); ?>,
                "cc_Email": <?php echo JSString($cc_email); ?>,
                "bcc_Email": <?php echo JSString($bcc_email); ?>,
                "Subject": <?php echo JSString($subject); ?>,
                "Message": <?php echo JSString($message); ?>,
                "test_type": <?php echo JSString($test_type); ?>,
                "smtp_debug": <?php echo JSString($smtp_debug); ?>,
                "smtp_server": <?php echo JSString($smtp_server); ?>,
                "smtp_port": <?php echo JSString($smtp_port); ?>,
                "smtp_secure": <?php echo JSString($smtp_secure); ?>,
                "smtp_authenticate": <?php echo JSString($smtp_authenticate); ?>,
                "authenticate_username": <?php echo JSString($authenticate_username); ?>,
                "authenticate_password": <?php echo JSString($authenticate_password); ?>
            };

            var resetForm = document.createElement("form");
            resetForm.setAttribute("method", "POST");
            resetForm.setAttribute("path", "index.php");

            for (var k in post_params) {
                var h = document.createElement("input");
                h.setAttribute("type", "hidden");
                h.setAttribute("name", k);
                h.setAttribute("value", post_params[k]);
                resetForm.appendChild(h);
            }

            document.body.appendChild(resetForm);
            resetForm.submit();
        }

        function showHideDiv(test, element_id) {
            var ops = {"smtp-options-table": "smtp"};

            if (test == ops[element_id]) {
                document.getElementById(element_id).style.display = "block";
            } else {
                document.getElementById(element_id).style.display = "none";
            }
        }
    </script>
</head>
<body>
<?php
if (version_compare(PHP_VERSION, '5.0.0', '<')) {
    echo 'Current PHP version: ' . phpversion() . "<br>";
    echo exit("ERROR: Wrong PHP version. Must be PHP 5 or above.");
}

if (count($results_messages) > 0) {
    echo '<h2>Run results</h2>';
    echo '<ul>';
    foreach ($results_messages as $result) {
        echo "<li>$result</li>";
    }
    echo '</ul>';
}

if (isset($_POST["submit"]) && $_POST["submit"] == "Submit") {
    echo "<button type=\"submit\" onclick=\"startAgain();\">Start Over</button><br>\n";
    echo "<br><span>Script:</span>\n";
    echo "<pre class=\"brush: php;\">\n";
    echo htmlentities($example_code);
    echo "\n</pre>\n";
    echo "\n<hr style=\"margin: 3em;\">\n";
}
?>
<form method="POST" enctype="multipart/form-data">
    <div>
        <div class="column-left">
            <fieldset>
                <legend>Mail Details</legend>
                <table border="1" class="column">
                    <tr>
                        <td class="colleft">
                            <label for="From_Name"><strong>From</strong> Name</label>
                        </td>
                        <td class="colrite">
                            <input type="text" id="From_Name" name="From_Name" value="<?php echo htmlentities($from_name); ?>"
                                   style="width:95%;" autofocus placeholder="Your Name">
                        </td>
                    </tr>
                    <tr>
                        <td class="colleft">
                            <label for="From_Email"><strong>From</strong> Email Address</label>
                        </td>
                        <td class="colrite">
                            <input type="text" id="From_Email" name="From_Email" value="<?php echo htmlentities($from_email); ?>"
                                   style="width:95%;" required placeholder="Your.Email@example.com">
                        </td>
                    </tr>
                    <tr>
                        <td class="colleft">
                            <label for="To_Name"><strong>To</strong> Name</label>
                        </td>
                        <td class="colrite">
                            <input type="text" id="To_Name" name="To_Name" value="<?php echo htmlentities($to_name); ?>"
                                   style="width:95%;" placeholder="Recipient's Name">
                        </td>
                    </tr>
                    <tr>
                        <td class="colleft">
                            <label for="To_Email"><strong>To</strong> Email Address</label>
                        </td>
                        <td class="colrite">
                            <input type="text" id="To_Email" name="To_Email" value="<?php echo htmlentities($to_email); ?>"
                                   style="width:95%;" required placeholder="Recipients.Email@example.com">
                        </td>
                    </tr>
                    <tr>
                        <td class="colleft">
                            <label for="cc_Email"><strong>CC Recipients</strong><br>
                                <small>(separate with commas)</small>
                            </label>
                        </td>
                        <td class="colrite">
                            <input type="text" id="cc_Email" name="cc_Email" value="<?php echo htmlentities($cc_email); ?>"
                                   style="width:95%;" placeholder="cc1@example.com, cc2@example.com">
                        </td>
                    </tr>
                    <tr>
                        <td class="colleft">
                            <label for="bcc_Email"><strong>BCC Recipients</strong><br>
                                <small>(separate with commas)</small>
                            </label>
                        </td>
                        <td class="colrite">
                            <input type="text" id="bcc_Email" name="bcc_Email" value="<?php echo htmlentities($bcc_email); ?>"
                                   style="width:95%;" placeholder="bcc1@example.com, bcc2@example.com">
                        </td>
                    </tr>
                    <tr>
                        <td class="colleft">
                            <label for="Subject"><strong>Subject</strong></label>
                        </td>
                        <td class="colrite">
                            <input type="text" name="Subject" id="Subject" value="<?php echo htmlentities($subject); ?>"
                                   style="width:95%;" placeholder="Email Subject">
                        </td>
                    </tr>
                    <tr>
                        <td class="colleft">
                            <label for="Message"><strong>Message</strong><br>
                                <small>If blank, will use content.html</small>
                            </label>
                        </td>
                        <td class="colrite">
                            <textarea name="Message" id="Message" style="width:95%;height:5em;"
                                      placeholder="Body of your email"><?php echo htmlentities($message); ?></textarea>
                        </td>
                    </tr>
                </table>
                <div style="margin:1em 0;">Test will include two attachments.</div>
            </fieldset>
        </div>
        <div class="column-right">
            <fieldset class="inner"> <!-- SELECT TYPE OF MAIL -->
                <legend>Mail Test Specs</legend>
                <table border="1" class="column">
                    <tr>
                        <td class="colleft">Test Type</td>
                        <td class="colrite">
                            <div class="radio">
                                <label for="radio-mail">Mail()</label>
                                <input class="radio" type="radio" name="test_type" value="mail" id="radio-mail"
                                       onclick="showHideDiv(this.value, 'smtp-options-table');"
                                       <?php echo ($test_type == 'mail') ? 'checked' : ''; ?>
                                       required>
                            </div>
                            <div class="radio">
                                <label for="radio-sendmail">Sendmail</label>
                                <input class="radio" type="radio" name="test_type" value="sendmail" id="radio-sendmail"
                                       onclick="showHideDiv(this.value, 'smtp-options-table');"
                                       <?php echo ($test_type == 'sendmail') ? 'checked' : ''; ?>
                                       required>
                            </div>
                            <div class="radio">
                                <label for="radio-qmail">Qmail</label>
                                <input class="radio" type="radio" name="test_type" value="qmail" id="radio-qmail"
                                       onclick="showHideDiv(this.value, 'smtp-options-table');"
                                       <?php echo ($test_type == 'qmail') ? 'checked' : ''; ?>
                                       required>
                            </div>
                            <div class="radio">
                                <label for="radio-smtp">SMTP</label>
                                <input class="radio" type="radio" name="test_type" value="smtp" id="radio-smtp"
                                       onclick="showHideDiv(this.value, 'smtp-options-table');"
                                       <?php echo ($test_type == 'smtp') ? 'checked' : ''; ?>
                                       required>
                            </div>
                        </td>
                    </tr>
                </table>
                <div id="smtp-options-table" style="margin:1em 0 0 0;
<?php if ($test_type != 'smtp') {
    echo "display: none;";
} ?>">
                    <span style="margin:1.25em 0; display:block;"><strong>SMTP Specific Options:</strong></span>
                    <table border="1" class="column">
                        <tr>
                            <td class="colleft"><label for="smtp_debug">SMTP Debug ?</label></td>
                            <td class="colrite">
                                <select size="1" id="smtp_debug" name="smtp_debug">
                                    <option <?php echo ($smtp_debug == '0') ? 'selected' : ''; ?> value="0">
                                        0 - Disabled
                                    </option>
                                    <option <?php echo ($smtp_debug == '1') ? 'selected' : ''; ?> value="1">
                                        1 - Client messages
                                    </option>
                                    <option <?php echo ($smtp_debug == '2') ? 'selected' : ''; ?> value="2">
                                        2 - Client and server messages
                                    </option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="colleft"><label for="smtp_server">SMTP Server</label></td>
                            <td class="colrite">
                                <input type="text" id="smtp_server" name="smtp_server"
                                       value="<?php echo htmlentities($smtp_server); ?>" style="width:95%;"
                                       placeholder="smtp.server.com">
                            </td>
                        </tr>
                        <tr>
                            <td class="colleft" style="width: 5em;"><label for="smtp_port">SMTP Port</label></td>
                            <td class="colrite">
                                <input type="text" name="smtp_port" id="smtp_port" size="3"
                                       value="<?php echo htmlentities($smtp_port); ?>" placeholder="Port">
                            </td>
                        </tr>
                        <tr>
                            <td class="colleft"><label for="smtp_secure">SMTP Security</label></td>
                            <td>
                                <select size="1" name="smtp_secure" id="smtp_secure">
                                    <option <?php echo ($smtp_secure == 'none') ? 'selected' : '' ?>>None</option>
                                    <option <?php echo ($smtp_secure == 'tls') ? 'selected' : '' ?>>TLS</option>
                                    <option <?php echo ($smtp_secure == 'ssl') ? 'selected' : '' ?>>SSL</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="colleft"><label for="smtp-authenticate">SMTP Authenticate?</label></td>
                            <td class="colrite">
                                <input type="checkbox" id="smtp-authenticate"
                                       name="smtp_authenticate"
<?php if ($smtp_authenticate != '') {
    echo "checked";
} ?>
                                       value="true">
                            </td>
                        </tr>
                        <tr>
                            <td class="colleft"><label for="authenticate_username">Authenticate Username</label></td>
                            <td class="colrite">
                                <input type="text" id="authenticate_username" name="authenticate_username"
                                       value="<?php echo htmlentities($authenticate_username); ?>" style="width:95%;"
                                       placeholder="SMTP Server Username">
                            </td>
                        </tr>
                        <tr>
                            <td class="colleft"><label for="authenticate_password">Authenticate Password</label></td>
                            <td class="colrite">
                                <input type="password" name="authenticate_password" id="authenticate_password"
                                       value="<?php echo htmlentities($authenticate_password); ?>" style="width:95%;"
                                       placeholder="SMTP Server Password">
                            </td>
                        </tr>
                    </table>
                </div>
            </fieldset>
        </div>
        <br style="clear:both;">

        <div style="margin-left:2em; margin-bottom:5em; float:left;">
            <div style="margin-bottom: 1em; ">
                <input type="submit" value="Submit" name="submit">
            </div>
            <?php echo 'Current PHP version: ' . phpversion(); ?>
        </div>
    </div>
</form>
</body>
</html>
