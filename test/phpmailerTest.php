<?php
/**
* PHPMailer - PHP email transport unit tests
* Before running these tests you need to install PHPUnit 3.3 or later through pear, like this:
*   pear install "channel://pear.phpunit.de/PHPUnit"
* Then run the tests like this:
*   phpunit phpmailerTest
* @package PHPMailer
* @author Andy Prevost
* @author Marcus Bointon
* @copyright 2004 - 2009 Andy Prevost
* @version $Id$
* @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
*/

require 'PHPUnit/Framework.php';

$INCLUDE_DIR = "../";

require $INCLUDE_DIR . 'class.phpmailer.php';
error_reporting(E_ALL);

/**
* PHPMailer - PHP email transport unit test class
* Performs authentication tests
*/
class phpmailerTest extends PHPUnit_Framework_TestCase {
    /**
     * Holds the default phpmailer instance.
     * @private
     * @type object
     */
    var $Mail = false;

    /**
     * Holds the SMTP mail host.
     * @public
     * @type string
     */
    var $Host = "";
    
    /**
     * Holds the change log.
     * @private
     * @type string array
     */
    var $ChangeLog = array();
    
     /**
     * Holds the note log.
     * @private
     * @type string array
     */
    var $NoteLog = array();   

    /**
     * Run before each test is started.
     */
    function setUp() {
        global $INCLUDE_DIR;

	@include './testbootstrap.php'; //Overrides go in here

        $this->Mail = new PHPMailer();

        $this->Mail->Priority = 3;
        $this->Mail->Encoding = "8bit";
        $this->Mail->CharSet = "iso-8859-1";
        if (array_key_exists('mail_from', $_REQUEST)) {
	        $this->Mail->From = $_REQUEST['mail_from'];
	    } else {
	        $this->Mail->From = 'unit_test@phpmailer.sf.net';
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
        $this->Mail->Port = 25;
        $this->Mail->Helo = "localhost.localdomain";
        $this->Mail->SMTPAuth = false;
        $this->Mail->Username = "";
        $this->Mail->Password = "";
        $this->Mail->PluginDir = $INCLUDE_DIR;
		$this->Mail->AddReplyTo("no_reply@phpmailer.sf.net", "Reply Guy");
        $this->Mail->Sender = "unit_test@phpmailer.sf.net";

        if(strlen($this->Mail->Host) > 0) {
            $this->Mail->Mailer = "smtp";
        } else {
            $this->Mail->Mailer = "mail";
            $this->Sender = "unit_test@phpmailer.sf.net";
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
    function tearDown() {
        // Clean global variables
        $this->Mail = NULL;
        $this->ChangeLog = array();
        $this->NoteLog = array();
    }


    /**
     * Build the body of the message in the appropriate format.
     * @private
     * @returns void
     */
    function BuildBody() {
        $this->CheckChanges();
        
        // Determine line endings for message        
        if($this->Mail->ContentType == "text/html" || strlen($this->Mail->AltBody) > 0)
        {
            $eol = "<br/>";
            $bullet = "<li>";
            $bullet_start = "<ul>";
            $bullet_end = "</ul>";
        }
        else
        {
            $eol = "\n";
            $bullet = " - ";
            $bullet_start = "";
            $bullet_end = "";
        }
        
        $ReportBody = "";
        
        $ReportBody .= "---------------------" . $eol;
        $ReportBody .= "Unit Test Information" . $eol;
        $ReportBody .= "---------------------" . $eol;
        $ReportBody .= "phpmailer version: " . PHPMailer::VERSION . $eol;
        $ReportBody .= "Content Type: " . $this->Mail->ContentType . $eol;
        
        if(strlen($this->Mail->Host) > 0)
            $ReportBody .= "Host: " . $this->Mail->Host . $eol;
        
        // If attachments then create an attachment list
        $attachments = $this->Mail->GetAttachments();
        if(count($attachments) > 0)
        {
            $ReportBody .= "Attachments:" . $eol;
            $ReportBody .= $bullet_start;
            foreach($attachments as $attachment) {
                $ReportBody .= $bullet . "Name: " . $attachment[1] . ", ";
                $ReportBody .= "Encoding: " . $attachment[3] . ", ";
                $ReportBody .= "Type: " . $attachment[4] . $eol;
            }
            $ReportBody .= $bullet_end . $eol;
        }
        
        // If there are changes then list them
        if(count($this->ChangeLog) > 0)
        {
            $ReportBody .= "Changes" . $eol;
            $ReportBody .= "-------" . $eol;

            $ReportBody .= $bullet_start;
            for($i = 0; $i < count($this->ChangeLog); $i++)
            {
                $ReportBody .= $bullet . $this->ChangeLog[$i][0] . " was changed to [" . 
                               $this->ChangeLog[$i][1] . "]" . $eol;
            }
            $ReportBody .= $bullet_end . $eol . $eol;
        }
        
        // If there are notes then list them
        if(count($this->NoteLog) > 0)
        {
            $ReportBody .= "Notes" . $eol;
            $ReportBody .= "-----" . $eol;

            $ReportBody .= $bullet_start;
            for($i = 0; $i < count($this->NoteLog); $i++)
            {
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
    function CheckChanges() {
        if($this->Mail->Priority != 3)
            $this->AddChange("Priority", $this->Mail->Priority);
        if($this->Mail->Encoding != "8bit")
            $this->AddChange("Encoding", $this->Mail->Encoding);
        if($this->Mail->CharSet != "iso-8859-1")
            $this->AddChange("CharSet", $this->Mail->CharSet);
        if($this->Mail->Sender != "")
            $this->AddChange("Sender", $this->Mail->Sender);
        if($this->Mail->WordWrap != 0)
            $this->AddChange("WordWrap", $this->Mail->WordWrap);
        if($this->Mail->Mailer != "mail")
            $this->AddChange("Mailer", $this->Mail->Mailer);
        if($this->Mail->Port != 25)
            $this->AddChange("Port", $this->Mail->Port);
        if($this->Mail->Helo != "localhost.localdomain")
            $this->AddChange("Helo", $this->Mail->Helo);
        if($this->Mail->SMTPAuth)
            $this->AddChange("SMTPAuth", "true");
    }
    
    /**
     * Adds a change entry.
     * @private
     * @returns void
     */
    function AddChange($sName, $sNewValue) {
        $cur = count($this->ChangeLog);
        $this->ChangeLog[$cur][0] = $sName;
        $this->ChangeLog[$cur][1] = $sNewValue;
    }
    
    /**
     * Adds a simple note to the message.
     * @public
     * @returns void
     */
    function AddNote($sValue) {
        $this->NoteLog[] = $sValue;
    }

    /**
     * Adds all of the addresses
     * @public
     * @returns void
     */
    function SetAddress($sAddress, $sName = "", $sType = "to") {
        switch($sType)
        {
            case "to":
                return $this->Mail->AddAddress($sAddress, $sName);
            case "cc":
                return $this->Mail->AddCC($sAddress, $sName);
            case "bcc":
                return $this->Mail->AddBCC($sAddress, $sName);
        }
    }

    /////////////////////////////////////////////////
    // UNIT TESTS
    /////////////////////////////////////////////////

    /**
     * Try a plain message.
     */
    function test_WordWrap() {

        $this->Mail->WordWrap = 40;
        $my_body = "Here is the main body of this message.  It should " .
                   "be quite a few lines.  It should be wrapped at the " .
                   "40 characters.  Make sure that it is.";
        $nBodyLen = strlen($my_body);
        $my_body .= "\n\nThis is the above body length: " . $nBodyLen;

        $this->Mail->Body = $my_body;
        $this->Mail->Subject .= ": Wordwrap";

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Try a plain message.
     */
    function test_Low_Priority() {
    
        $this->Mail->Priority = 5;
        $this->Mail->Body = "Here is the main body.  There should be " .
                            "a reply to address in this message.";
        $this->Mail->Subject .= ": Low Priority";
        $this->Mail->AddReplyTo("nobody@nobody.com", "Nobody (Unit Test)");

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple plain file attachment test.
     */
    function test_Multiple_Plain_FileAttachment() {

        $this->Mail->Body = "Here is the text body";
        $this->Mail->Subject .= ": Plain + Multiple FileAttachments";

        if(!$this->Mail->AddAttachment("test.png"))
        {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        if(!$this->Mail->AddAttachment(__FILE__, "test.txt"))
        {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple plain string attachment test.
     */
    function test_Plain_StringAttachment() {

        $this->Mail->Body = "Here is the text body";
        $this->Mail->Subject .= ": Plain + StringAttachment";
        
        $sAttachment = "These characters are the content of the " .
                       "string attachment.\nThis might be taken from a ".
                       "database or some other such thing. ";
        
        $this->Mail->AddStringAttachment($sAttachment, "string_attach.txt");

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Plain quoted-printable message.
     */
    function test_Quoted_Printable() {

        $this->Mail->Body = "Here is the main body";
        $this->Mail->Subject .= ": Plain + Quoted-printable";
        $this->Mail->Encoding = "quoted-printable";

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);

	//Check that a quoted printable encode and decode results in the same as went in
	$t = substr(file_get_contents(__FILE__), 0, 1024); //Just pick a chunk of this file as test content
	$this->assertEquals($t, quoted_printable_decode($this->Mail->EncodeQP($t)), 'QP encoding round-trip failed');
        //$this->assertEquals($t, quoted_printable_decode($this->Mail->EncodeQPphp($t)), 'Native PHP QP encoding round-trip failed'); //TODO the PHP qp encoder is quite broken

    }

    /**
     * Try a plain message.
     */
    function test_Html() {
    
        $this->Mail->IsHTML(true);
        $this->Mail->Subject .= ": HTML only";
        
        $this->Mail->Body = "This is a <b>test message</b> written in HTML. </br>" .
                            "Go to <a href=\"http://phpmailer.sourceforge.net/\">" .
                            "http://phpmailer.sourceforge.net/</a> for new versions of " .
                            "phpmailer.  <p/> Thank you!";

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple HTML and attachment test
     */
    function test_HTML_Attachment() {

        $this->Mail->Body = "This is the <b>HTML</b> part of the email.";
        $this->Mail->Subject .= ": HTML + Attachment";
        $this->Mail->IsHTML(true);
        
        if(!$this->Mail->AddAttachment(__FILE__, "test_attach.txt"))
        {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * An embedded attachment test.
     */
    function test_Embedded_Image() {

        $this->Mail->Body = "Embedded Image: <img alt=\"phpmailer\" src=\"cid:my-attach\">" .
                     "Here is an image!</a>";
        $this->Mail->Subject .= ": Embedded Image";
        $this->Mail->IsHTML(true);
        
        if(!$this->Mail->AddEmbeddedImage("test.png", "my-attach", "test.png",
                                          "base64", "image/png"))
        {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
	//For code coverage
	$this->Mail->AddEmbeddedImage('thisfiledoesntexist', 'xyz'); //Non-existent file
	$this->Mail->AddEmbeddedImage(__FILE__, '123'); //Missing name

    }

    /**
     * An embedded attachment test.
     */
    function test_Multi_Embedded_Image() {

        $this->Mail->Body = "Embedded Image: <img alt=\"phpmailer\" src=\"cid:my-attach\">" .
                     "Here is an image!</a>";
        $this->Mail->Subject .= ": Embedded Image + Attachment";
        $this->Mail->IsHTML(true);
        
        if(!$this->Mail->AddEmbeddedImage("test.png", "my-attach", "test.png",
                                          "base64", "image/png"))
        {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        if(!$this->Mail->AddAttachment(__FILE__, "test.txt"))
        {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }
        
        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple multipart/alternative test.
     */
    function test_AltBody() {

        $this->Mail->Body = "This is the <b>HTML</b> part of the email.";
        $this->Mail->AltBody = "Here is the text body of this message.  " .
                   "It should be quite a few lines.  It should be wrapped at the " .
                   "40 characters.  Make sure that it is.";
        $this->Mail->WordWrap = 40;
        $this->AddNote("This is a mulipart alternative email");
        $this->Mail->Subject .= ": AltBody + Word Wrap";

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple HTML and attachment test
     */
    function test_AltBody_Attachment() {

        $this->Mail->Body = "This is the <b>HTML</b> part of the email.";
        $this->Mail->AltBody = "This is the text part of the email.";
        $this->Mail->Subject .= ": AltBody + Attachment";
        $this->Mail->IsHTML(true);
        
        if(!$this->Mail->AddAttachment(__FILE__, "test_attach.txt"))
        {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
        if (is_writable('.')) {
            file_put_contents('message.txt', $this->Mail->CreateHeader() . $this->Mail->CreateBody());
        } else {
            $this->assertTrue(false, 'Could not write local file - check permissions');
        }
    }    

    function test_MultipleSend() {
        $this->Mail->Body = "Sending two messages without keepalive";
        $this->BuildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ": SMTP 1";
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
        
        $this->Mail->Subject = $subject . ": SMTP 2";
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    function test_SendmailSend() {
        $this->Mail->Body = "Sending via sendmail";
        $this->BuildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ": sendmail";
	$this->Mail->IsSendmail();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    function test_MailSend() {
        $this->Mail->Body = "Sending via mail()";
        $this->BuildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ": mail()";
	$this->Mail->IsMail();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    function test_SmtpKeepAlive() {
        $this->Mail->Body = "This was done using the SMTP keep-alive.";
        $this->BuildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->SMTPKeepAlive = true;
        $this->Mail->Subject = $subject . ": SMTP keep-alive 1";
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
        
        $this->Mail->Subject = $subject . ": SMTP keep-alive 2";
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
        $this->Mail->SmtpClose();
    }
    
    /**
     * Tests this denial of service attack: 
     *    http://www.cybsec.com/vuln/PHPMailer-DOS.pdf
     */
    function test_DenialOfServiceAttack() {
        $this->Mail->Body = "This should no longer cause a denial of service.";
        $this->BuildBody();
       
        $this->Mail->Subject = str_repeat("A", 998);
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }
    
	function test_Error() {
		$this->Mail->Subject .= ": This should be sent"; 
		$this->BuildBody();
		$this->Mail->ClearAllRecipients(); // no addresses should cause an error
		$this->assertTrue($this->Mail->IsError() == false, "Error found");
		$this->assertTrue($this->Mail->Send() == false, "Send succeeded");
		$this->assertTrue($this->Mail->IsError(), "No error found");
		$this->assertEquals('You must provide at least one recipient email address.', $this->Mail->ErrorInfo);
		$this->Mail->AddAddress($_REQUEST['mail_to']);
		$this->assertTrue($this->Mail->Send(), "Send failed");
	}
	
	function test_Addressing() {
		$this->assertFalse($this->Mail->AddAddress('a@example..com'), 'Invalid address accepted');
		$this->assertTrue($this->Mail->AddAddress('a@example.com'), 'Addressing failed');
		$this->assertFalse($this->Mail->AddAddress('a@example.com'), 'Duplicate addressing failed');
		$this->assertTrue($this->Mail->AddCC('b@example.com'), 'CC addressing failed');
		$this->assertFalse($this->Mail->AddCC('b@example.com'), 'CC duplicate addressing failed');
		$this->assertFalse($this->Mail->AddCC('a@example.com'), 'CC duplicate addressing failed (2)');
		$this->assertTrue($this->Mail->AddBCC('c@example.com'), 'BCC addressing failed');
		$this->assertFalse($this->Mail->AddBCC('c@example.com'), 'BCC duplicate addressing failed');
		$this->assertFalse($this->Mail->AddBCC('a@example.com'), 'BCC duplicate addressing failed (2)');
		$this->assertTrue($this->Mail->AddReplyTo('a@example.com'), 'Replyto Addressing failed');
		$this->assertFalse($this->Mail->AddReplyTo('a@example..com'), 'Invalid Replyto address accepted');
		$this->Mail->ClearAddresses();
		$this->Mail->ClearCCs();
		$this->Mail->ClearBCCs();
		$this->Mail->ClearReplyTos();
	}

	/**
	* Test language files for missing and excess translations
	* All languages are compared with English
	*/
	function test_Translations() {
		$this->Mail->SetLanguage('en');
		$definedStrings = $this->Mail->GetTranslations();
		foreach (new DirectoryIterator('../language') as $fileInfo) {
			if($fileInfo->isDot()) continue;
			$matches = array();
			//Only look at language files, ignore anything else in there
			if (preg_match('/^phpmailer\.lang-([a-z_]{2,})\.php$/', $fileInfo->getFilename(), $matches)) {
				$lang = $matches[1]; //Extract language code
				$PHPMAILER_LANG = array(); //Language strings get put in here
				include $fileInfo->getPathname(); //Get language strings
				$missing = array_diff(array_keys($definedStrings), array_keys($PHPMAILER_LANG));
				$extra = array_diff(array_keys($PHPMAILER_LANG), array_keys($definedStrings));
				$this->assertTrue(empty($missing), "Missing translations in $lang: ". implode(', ', $missing));
				$this->assertTrue(empty($extra), "Extra translations in $lang: ". implode(', ', $extra));
			}
		}
	}

	/**
	* Encoding tests
	*/
	function test_Encodings() {
	    $this->Mail->Charset = 'iso-8859-1';
	    $this->assertEquals('=A1Hola!_Se=F1or!', $this->Mail->EncodeQ('¡Hola! Señor!', 'text'), 'Q Encoding (text) failed');
	    $this->assertEquals('=A1Hola!_Se=F1or!', $this->Mail->EncodeQ('¡Hola! Señor!', 'comment'), 'Q Encoding (comment) failed');
	    $this->assertEquals('=A1Hola!_Se=F1or!', $this->Mail->EncodeQ('¡Hola! Señor!', 'phrase'), 'Q Encoding (phrase) failed');
	}
	
	/**
	* Signing tests
	*/
	function test_Signing() {
	    $this->Mail->Sign('certfile.txt', 'keyfile.txt', 'password'); //TODO this is not really testing signing, but at least helps coverage
	}

	/**
	* Miscellaneous calls to improve test coverage and some small tests
	*/
	function test_Miscellaneous() {
	    $this->assertEquals('application/pdf', PHPMailer::_mime_types('pdf') , 'MIME TYPE lookup failed');
	    $this->Mail->AddCustomHeader('SomeHeader: Some Value');
	    $this->Mail->ClearCustomHeaders();
	    $this->Mail->ClearAttachments();
	    $this->Mail->IsHTML(false);
	    $this->Mail->IsSMTP();
	    $this->Mail->IsMail();
	    $this->Mail->IsSendMail();
   	    $this->Mail->IsQmail();
	    $this->Mail->SetLanguage('fr');
	    $this->Mail->Sender = '';
	    $this->Mail->CreateHeader();
	    $this->assertFalse($this->Mail->set('x', 'y'), 'Invalid property set succeeded');
	    $this->assertTrue($this->Mail->set('Timeout', 11), 'Valid property set failed');
	    $this->Mail->getFile(__FILE__);
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

?>