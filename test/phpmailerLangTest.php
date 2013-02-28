<?php
/**
 * PHPMailer - language file tests
 * Before running these tests you need to install PHPUnit 3.3 or later through pear, like this:
 *   pear install "channel://pear.phpunit.de/PHPUnit"
 * Then run the tests like this:
 *   phpunit phpmailerLangTest
 * @package PHPMailer
 * @author Andy Prevost
 * @author Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

require 'PHPUnit/Autoload.php';

/**
 * PHPMailer - PHP email transport unit test class
 * Performs authentication tests
 */
class phpmailerLangTest extends PHPUnit_Framework_TestCase
{
    /**
     * Holds the default phpmailer instance.
     * @private
     * @var PHPMailer
     */
    public $Mail;

    /**
     * Holds the SMTP mail host.
     * @public
     * @var string
     */
    public $Host = "";

    /**
     * Holds the change log.
     * @private
     * @var string[]
     */
    public $ChangeLog = array();

    /**
     * Holds the note log.
     * @private
     * @var string[]
     */
    public $NoteLog = array();

    /**
     * @var string Default include path
     */
    public $INCLUDE_DIR = '../';

    /**
     * Run before each test is started.
     */
    function setUp()
    {

        if (file_exists('./testbootstrap.php')) {
            include './testbootstrap.php'; //Overrides go in here
        }
        require_once $this->INCLUDE_DIR . 'class.phpmailer.php';
        $this->Mail = new PHPMailer;

        $this->Mail->Priority = 3;
        $this->Mail->Encoding = "8bit";
        $this->Mail->CharSet = "iso-8859-1";
        if (array_key_exists('mail_from', $_REQUEST)) {
            $this->Mail->From = $_REQUEST['mail_from'];
        } else {
            $this->Mail->From = 'unit_test@phpmailer.example.com';
        }
        $this->Mail->FromName = "Unit Tester";
        $this->Mail->Sender = "";
        $this->Mail->Subject = "Unit Test";
        $this->Mail->Body = "";
        $this->Mail->AltBody = "";
        $this->Mail->WordWrap = 0;
        if (array_key_exists('mail_host', $_REQUEST)) {
            $this->Mail->Host = $_REQUEST['mail_host'];
        } else {
            $this->Mail->Host = 'mail.example.com';
        }
        if (array_key_exists('mail_port', $_REQUEST)) {
            $this->Mail->Port = $_REQUEST['mail_port'];
        } else {
            $this->Mail->Port = 25;
        }
        $this->Mail->Helo = "localhost.localdomain";
        $this->Mail->SMTPAuth = false;
        $this->Mail->Username = "";
        $this->Mail->Password = "";
        $this->Mail->PluginDir = $this->INCLUDE_DIR;
        $this->Mail->AddReplyTo("no_reply@phpmailer.example.com", "Reply Guy");
        $this->Mail->Sender = "unit_test@phpmailer.example.com";

        if (strlen($this->Mail->Host) > 0) {
            $this->Mail->Mailer = "smtp";
        } else {
            $this->Mail->Mailer = "mail";
            $this->Mail->Sender = "unit_test@phpmailer.example.com";
        }

        if (array_key_exists('mail_to', $_REQUEST)) {
            $this->SetAddress($_REQUEST['mail_to'], 'Test User', 'to');
        }
        if (array_key_exists('mail_cc', $_REQUEST) and strlen($_REQUEST['mail_cc']) > 0) {
            $this->SetAddress($_REQUEST['mail_cc'], 'Carbon User', 'cc');
        }
    }

    /**
     * Run after each test is completed.
     */
    function tearDown()
    {
        // Clean global variables
        $this->Mail = null;
        $this->ChangeLog = array();
        $this->NoteLog = array();
    }


    /**
     * Build the body of the message in the appropriate format.
     * @private
     * @returns void
     */
    function BuildBody()
    {
        $this->CheckChanges();

        // Determine line endings for message
        if ($this->Mail->ContentType == "text/html" || strlen($this->Mail->AltBody) > 0) {
            $eol = "<br/>";
            $bullet = "<li>";
            $bullet_start = "<ul>";
            $bullet_end = "</ul>";
        } else {
            $eol = "\n";
            $bullet = " - ";
            $bullet_start = "";
            $bullet_end = "";
        }

        $ReportBody = "";

        $ReportBody .= "---------------------" . $eol;
        $ReportBody .= "Unit Test Information" . $eol;
        $ReportBody .= "---------------------" . $eol;
        $ReportBody .= "phpmailer version: " . $this->Mail->Version . $eol;
        $ReportBody .= "Content Type: " . $this->Mail->ContentType . $eol;

        if (strlen($this->Mail->Host) > 0) {
            $ReportBody .= "Host: " . $this->Mail->Host . $eol;
        }

        // If attachments then create an attachment list
        $attachments = $this->Mail->GetAttachments();
        if (count($attachments) > 0) {
            $ReportBody .= "Attachments:" . $eol;
            $ReportBody .= $bullet_start;
            foreach ($attachments as $attachment) {
                $ReportBody .= $bullet . "Name: " . $attachment[1] . ", ";
                $ReportBody .= "Encoding: " . $attachment[3] . ", ";
                $ReportBody .= "Type: " . $attachment[4] . $eol;
            }
            $ReportBody .= $bullet_end . $eol;
        }

        // If there are changes then list them
        if (count($this->ChangeLog) > 0) {
            $ReportBody .= "Changes" . $eol;
            $ReportBody .= "-------" . $eol;

            $ReportBody .= $bullet_start;
            for ($i = 0; $i < count($this->ChangeLog); $i++) {
                $ReportBody .= $bullet . $this->ChangeLog[$i][0] . " was changed to [" .
                    $this->ChangeLog[$i][1] . "]" . $eol;
            }
            $ReportBody .= $bullet_end . $eol . $eol;
        }

        // If there are notes then list them
        if (count($this->NoteLog) > 0) {
            $ReportBody .= "Notes" . $eol;
            $ReportBody .= "-----" . $eol;

            $ReportBody .= $bullet_start;
            for ($i = 0; $i < count($this->NoteLog); $i++) {
                $ReportBody .= $bullet . $this->NoteLog[$i] . $eol;
            }
            $ReportBody .= $bullet_end;
        }

        // Re-attach the original body
        $this->Mail->Body .= $eol . $eol . $ReportBody;
    }

    /**
     * Check which default settings have been changed for the report.
     * @private
     * @returns void
     */
    function CheckChanges()
    {
        if ($this->Mail->Priority != 3) {
            $this->AddChange("Priority", $this->Mail->Priority);
        }
        if ($this->Mail->Encoding != "8bit") {
            $this->AddChange("Encoding", $this->Mail->Encoding);
        }
        if ($this->Mail->CharSet != "iso-8859-1") {
            $this->AddChange("CharSet", $this->Mail->CharSet);
        }
        if ($this->Mail->Sender != "") {
            $this->AddChange("Sender", $this->Mail->Sender);
        }
        if ($this->Mail->WordWrap != 0) {
            $this->AddChange("WordWrap", $this->Mail->WordWrap);
        }
        if ($this->Mail->Mailer != "mail") {
            $this->AddChange("Mailer", $this->Mail->Mailer);
        }
        if ($this->Mail->Port != 25) {
            $this->AddChange("Port", $this->Mail->Port);
        }
        if ($this->Mail->Helo != "localhost.localdomain") {
            $this->AddChange("Helo", $this->Mail->Helo);
        }
        if ($this->Mail->SMTPAuth) {
            $this->AddChange("SMTPAuth", "true");
        }
    }

    /**
     * Add a changelog entry.
     * @access private
     * @param string $sName
     * @param string $sNewValue
     * @return void
     */
    function AddChange($sName, $sNewValue)
    {
        $this->ChangeLog[] = array($sName, $sNewValue);
    }

    /**
     * Adds a simple note to the message.
     * @public
     * @param string $sValue
     * @return void
     */
    function AddNote($sValue)
    {
        $this->NoteLog[] = $sValue;
    }

    /**
     * Adds all of the addresses
     * @access public
     * @param string $sAddress
     * @param string $sName
     * @param string $sType
     * @return boolean
     */
    function SetAddress($sAddress, $sName = '', $sType = 'to')
    {
        switch ($sType) {
            case 'to':
                return $this->Mail->AddAddress($sAddress, $sName);
            case 'cc':
                return $this->Mail->AddCC($sAddress, $sName);
            case "bcc":
                return $this->Mail->AddBCC($sAddress, $sName);
        }
        return false;
    }

    /////////////////////////////////////////////////
    // UNIT TESTS
    /////////////////////////////////////////////////

    /**
     * Test language files for missing and excess translations
     * All languages are compared with English
     */
    function test_Translations()
    {
        $this->Mail->SetLanguage('en');
        $definedStrings = $this->Mail->GetTranslations();
        $err = '';
        foreach (new DirectoryIterator('../language') as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            $matches = array();
            //Only look at language files, ignore anything else in there
            if (preg_match('/^phpmailer\.lang-([a-z_]{2,})\.php$/', $fileInfo->getFilename(), $matches)) {
                $lang = $matches[1]; //Extract language code
                $PHPMAILER_LANG = array(); //Language strings get put in here
                include $fileInfo->getPathname(); //Get language strings
                $missing = array_diff(array_keys($definedStrings), array_keys($PHPMAILER_LANG));
                $extra = array_diff(array_keys($PHPMAILER_LANG), array_keys($definedStrings));
                if (!empty($missing)) {
                    $err .= "\nMissing translations in $lang: " . implode(', ', $missing);
                }
                if (!empty($extra)) {
                    $err .= "\nExtra translations in $lang: " . implode(', ', $extra);
                }
            }
        }
        $this->assertEmpty($err, $err);
    }
}

/**
 * This is a sample form for setting appropriate test values through a browser
 * These values can also be set using a file called testbootstrap.php (not in svn) in the same folder as this script
 * which is probably more useful if you run these tests a lot
<html>
<body>
<h3>phpmailer Unit Test</h3>
By entering a SMTP hostname it will automatically perform tests with SMTP.

<form name="phpmailer_unit" action=__FILE__ method="get">
<input type="hidden" name="submitted" value="1"/>
From Address: <input type="text" size="50" name="mail_from" value="<?php echo get("mail_from"); ?>"/>
<br/>
To Address: <input type="text" size="50" name="mail_to" value="<?php echo get("mail_to"); ?>"/>
<br/>
Cc Address: <input type="text" size="50" name="mail_cc" value="<?php echo get("mail_cc"); ?>"/>
<br/>
SMTP Hostname: <input type="text" size="50" name="mail_host" value="<?php echo get("mail_host"); ?>"/>
<p/>
<input type="submit" value="Run Test"/>

</form>
</body>
</html>
 */
