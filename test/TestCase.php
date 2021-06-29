<?php

/**
 * PHPMailer - Base test class.
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace PHPMailer\Test;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Yoast\PHPUnitPolyfills\TestCases\TestCase as PolyfillTestCase;

/**
 * PHPMailer - Base test class.
 */
abstract class TestCase extends PolyfillTestCase
{
    /**
     * Holds the PHPMailer instance.
     *
     * @var PHPMailer
     */
    protected $Mail;

    /**
     * Holds the change log.
     *
     * @var string[]
     */
    private $ChangeLog = [];

    /**
     * Holds the note log.
     *
     * @var string[]
     */
    private $NoteLog = [];

    /**
     * List of *public static* properties in the PHPMailer class with their default values.
     *
     * This list is used by the `set_up()` method.
     *
     * @var array Key is the property name, value the default as per the PHPMailer class.
     */
    private $PHPMailerStaticProps = [
        'validator' => 'php',
    ];

    /**
     * Run before each test class.
     */
    public static function set_up_before_class()
    {
        if (defined('PHPMAILER_INCLUDE_DIR') === false) {
            /*
             * Set up default include path.
             * Default to the dir above the test dir, i.e. the project home dir.
             */
            define('PHPMAILER_INCLUDE_DIR', dirname(__DIR__));
        }
    }

    /**
     * Run before each test is started.
     */
    protected function set_up()
    {
        // Make sure that public static variables contain their default values at the start of each test.
        foreach ($this->PHPMailerStaticProps as $key => $value) {
            PHPMailer::${$key} = $value;
        }

        if (file_exists(\PHPMAILER_INCLUDE_DIR . '/test/testbootstrap.php')) {
            include \PHPMAILER_INCLUDE_DIR . '/test/testbootstrap.php'; //Overrides go in here
        }
        $this->Mail = new PHPMailer();
        $this->Mail->SMTPDebug = SMTP::DEBUG_CONNECTION; //Full debug output
        $this->Mail->Debugoutput = ['PHPMailer\Test\DebugLogTestListener', 'debugLog'];
        $this->Mail->Priority = 3;
        $this->Mail->Encoding = '8bit';
        $this->Mail->CharSet = PHPMailer::CHARSET_ISO88591;
        if (array_key_exists('mail_from', $_REQUEST)) {
            $this->Mail->From = $_REQUEST['mail_from'];
        } else {
            $this->Mail->From = 'unit_test@phpmailer.example.com';
        }
        $this->Mail->FromName = 'Unit Tester';
        $this->Mail->Sender = '';
        $this->Mail->Subject = 'Unit Test';
        $this->Mail->Body = '';
        $this->Mail->AltBody = '';
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
        $this->Mail->Helo = 'localhost.localdomain';
        $this->Mail->SMTPAuth = false;
        $this->Mail->Username = '';
        $this->Mail->Password = '';
        if (array_key_exists('mail_useauth', $_REQUEST)) {
            $this->Mail->SMTPAuth = $_REQUEST['mail_useauth'];
        }
        if (array_key_exists('mail_username', $_REQUEST)) {
            $this->Mail->Username = $_REQUEST['mail_username'];
        }
        if (array_key_exists('mail_userpass', $_REQUEST)) {
            $this->Mail->Password = $_REQUEST['mail_userpass'];
        }
        $this->setAddress('no_reply@phpmailer.example.com', 'Reply Guy', 'ReplyTo');
        $this->Mail->Sender = 'unit_test@phpmailer.example.com';
        if ($this->Mail->Host != '') {
            $this->Mail->isSMTP();
        } else {
            $this->Mail->isMail();
        }
        if (array_key_exists('mail_to', $_REQUEST)) {
            $this->setAddress($_REQUEST['mail_to'], 'Test User', 'to');
        }
        if (array_key_exists('mail_cc', $_REQUEST) && $_REQUEST['mail_cc'] !== '') {
            $this->setAddress($_REQUEST['mail_cc'], 'Carbon User', 'cc');
        }
    }

    /**
     * Run after each test is completed.
     */
    protected function tear_down()
    {
        //Clean global variables
        $this->Mail = null;
        $this->ChangeLog = [];
        $this->NoteLog = [];
    }

    /**
     * Build the body of the message in the appropriate format.
     */
    protected function buildBody()
    {
        $this->checkChanges();

        //Determine line endings for message
        if ('text/html' === $this->Mail->ContentType || $this->Mail->AltBody !== '') {
            $eol = "<br>\r\n";
            $bullet_start = '<li>';
            $bullet_end = "</li>\r\n";
            $list_start = "<ul>\r\n";
            $list_end = "</ul>\r\n";
        } else {
            $eol = "\r\n";
            $bullet_start = ' - ';
            $bullet_end = "\r\n";
            $list_start = '';
            $list_end = '';
        }

        $ReportBody = '';

        $ReportBody .= '---------------------' . $eol;
        $ReportBody .= 'Unit Test Information' . $eol;
        $ReportBody .= '---------------------' . $eol;
        $ReportBody .= 'phpmailer version: ' . PHPMailer::VERSION . $eol;
        $ReportBody .= 'Content Type: ' . $this->Mail->ContentType . $eol;
        $ReportBody .= 'CharSet: ' . $this->Mail->CharSet . $eol;

        if ($this->Mail->Host !== '') {
            $ReportBody .= 'Host: ' . $this->Mail->Host . $eol;
        }

        //If attachments then create an attachment list
        $attachments = $this->Mail->getAttachments();
        if (count($attachments) > 0) {
            $ReportBody .= 'Attachments:' . $eol;
            $ReportBody .= $list_start;
            foreach ($attachments as $attachment) {
                $ReportBody .= $bullet_start . 'Name: ' . $attachment[1] . ', ';
                $ReportBody .= 'Encoding: ' . $attachment[3] . ', ';
                $ReportBody .= 'Type: ' . $attachment[4] . $bullet_end;
            }
            $ReportBody .= $list_end . $eol;
        }

        //If there are changes then list them
        if (count($this->ChangeLog) > 0) {
            $ReportBody .= 'Changes' . $eol;
            $ReportBody .= '-------' . $eol;

            $ReportBody .= $list_start;
            foreach ($this->ChangeLog as $iValue) {
                $ReportBody .= $bullet_start . $iValue[0] . ' was changed to [' .
                    $iValue[1] . ']' . $bullet_end;
            }
            $ReportBody .= $list_end . $eol . $eol;
        }

        //If there are notes then list them
        if (count($this->NoteLog) > 0) {
            $ReportBody .= 'Notes' . $eol;
            $ReportBody .= '-----' . $eol;

            $ReportBody .= $list_start;
            foreach ($this->NoteLog as $iValue) {
                $ReportBody .= $bullet_start . $iValue . $bullet_end;
            }
            $ReportBody .= $list_end;
        }

        //Re-attach the original body
        $this->Mail->Body .= $eol . $ReportBody;
    }

    /**
     * Check which default settings have been changed for the report.
     */
    protected function checkChanges()
    {
        if (3 != $this->Mail->Priority) {
            $this->addChange('Priority', $this->Mail->Priority);
        }
        if (PHPMailer::ENCODING_8BIT !== $this->Mail->Encoding) {
            $this->addChange('Encoding', $this->Mail->Encoding);
        }
        if (PHPMailer::CHARSET_ISO88591 !== $this->Mail->CharSet) {
            $this->addChange('CharSet', $this->Mail->CharSet);
        }
        if ('' != $this->Mail->Sender) {
            $this->addChange('Sender', $this->Mail->Sender);
        }
        if (0 != $this->Mail->WordWrap) {
            $this->addChange('WordWrap', $this->Mail->WordWrap);
        }
        if ('mail' !== $this->Mail->Mailer) {
            $this->addChange('Mailer', $this->Mail->Mailer);
        }
        if (25 != $this->Mail->Port) {
            $this->addChange('Port', $this->Mail->Port);
        }
        if ('localhost.localdomain' !== $this->Mail->Helo) {
            $this->addChange('Helo', $this->Mail->Helo);
        }
        if ($this->Mail->SMTPAuth) {
            $this->addChange('SMTPAuth', 'true');
        }
    }

    /**
     * Add a changelog entry.
     *
     * @param string $sName
     * @param string $sNewValue
     */
    protected function addChange($sName, $sNewValue)
    {
        $this->ChangeLog[] = [$sName, $sNewValue];
    }

    /**
     * Adds a simple note to the message.
     *
     * @param string $sValue
     */
    protected function addNote($sValue)
    {
        $this->NoteLog[] = $sValue;
    }

    /**
     * Adds all of the addresses.
     *
     * @param string $sAddress
     * @param string $sName
     * @param string $sType
     *
     * @return bool
     */
    protected function setAddress($sAddress, $sName = '', $sType = 'to')
    {
        switch ($sType) {
            case 'to':
                return $this->Mail->addAddress($sAddress, $sName);
            case 'cc':
                return $this->Mail->addCC($sAddress, $sName);
            case 'bcc':
                return $this->Mail->addBCC($sAddress, $sName);
            case 'ReplyTo':
                return $this->Mail->addReplyTo($sAddress, $sName);
        }

        return false;
    }
}
/*
 * This is a sample form for setting appropriate test values through a browser
 * These values can also be set using a file called testbootstrap.php (not in repo) in the same folder as this script
 * which is probably more useful if you run these tests a lot
 * <html>
 * <body>
 * <h3>PHPMailer Unit Test</h3>
 * By entering a SMTP hostname it will automatically perform tests with SMTP.
 *
 * <form name="phpmailer_unit" action=__FILE__ method="get">
 * <input type="hidden" name="submitted" value="1"/>
 * From Address: <input type="text" size="50" name="mail_from" value="<?php echo get("mail_from"); ?>"/>
 * <br/>
 * To Address: <input type="text" size="50" name="mail_to" value="<?php echo get("mail_to"); ?>"/>
 * <br/>
 * Cc Address: <input type="text" size="50" name="mail_cc" value="<?php echo get("mail_cc"); ?>"/>
 * <br/>
 * SMTP Hostname: <input type="text" size="50" name="mail_host" value="<?php echo get("mail_host"); ?>"/>
 * <p/>
 * <input type="submit" value="Run Test"/>
 *
 * </form>
 * </body>
 * </html>
 */
