<?php
/*******************
  Unit Test
  Type: phpmailer class
********************/

$INCLUDE_DIR = "../";

require("phpunit.php");
require($INCLUDE_DIR . "class.phpmailer.php");
error_reporting(E_ALL);

/**
 * Performs authentication tests
 */
class phpmailerTest extends TestCase
{
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
     * Class constuctor.
     */
    function phpmailerTest($name) {
        /* must define this constructor */
        $this->TestCase( $name );
    }
    
    /**
     * Run before each test is started.
     */
    function setUp() {
        global $global_vars;
        global $INCLUDE_DIR;

        $this->Mail = new PHPMailer();

        $this->Mail->Priority = 3;
        $this->Mail->Encoding = "8bit";
        $this->Mail->CharSet = "iso-8859-1";
        $this->Mail->From = "unit_test@phpmailer.sf.net";
        $this->Mail->FromName = "Unit Tester";
        $this->Mail->Sender = "";
        $this->Mail->Subject = "Unit Test";
        $this->Mail->Body = "";
        $this->Mail->AltBody = "";
        $this->Mail->WordWrap = 0;
        $this->Mail->Host = $global_vars["mail_host"];
        $this->Mail->Port = 25;
        $this->Mail->Helo = "localhost.localdomain";
        $this->Mail->SMTPAuth = false;
        $this->Mail->Username = "";
        $this->Mail->Password = "";
        $this->Mail->PluginDir = $INCLUDE_DIR;
		$this->Mail->AddReplyTo("no_reply@phpmailer.sf.net", "Reply Guy");
        $this->Mail->Sender = "unit_test@phpmailer.sf.net";

        if(strlen($this->Mail->Host) > 0)
            $this->Mail->Mailer = "smtp";
        else
        {
            $this->Mail->Mailer = "mail";
            $this->Sender = "unit_test@phpmailer.sf.net";
        }
        
        global $global_vars;
        $this->SetAddress($global_vars["mail_to"], "Test User");
        if(strlen($global_vars["mail_cc"]) > 0)
            $this->SetAddress($global_vars["mail_cc"], "Carbon User", "cc");
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
        $ReportBody .= "phpmailer version: " . $this->Mail->Version . $eol;
        $ReportBody .= "Content Type: " . $this->Mail->ContentType . $eol;
        
        if(strlen($this->Mail->Host) > 0)
            $ReportBody .= "Host: " . $this->Mail->Host . $eol;
        
        // If attachments then create an attachment list
        if(count($this->Mail->attachment) > 0)
        {
            $ReportBody .= "Attachments:" . $eol;
            $ReportBody .= $bullet_start;
            for($i = 0; $i < count($this->Mail->attachment); $i++)
            {
                $ReportBody .= $bullet . "Name: " . $this->Mail->attachment[$i][1] . ", ";
                $ReportBody .= "Encoding: " . $this->Mail->attachment[$i][3] . ", ";
                $ReportBody .= "Type: " . $this->Mail->attachment[$i][4] . $eol;
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
                $this->Mail->AddAddress($sAddress, $sName);
                break;
            case "cc":
                $this->Mail->AddCC($sAddress, $sName);
                break;
            case "bcc":
                $this->Mail->AddBCC($sAddress, $sName);
                break;
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
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
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
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple plain file attachment test.
     */
    function test_Multiple_Plain_FileAttachment() {

        $this->Mail->Body = "Here is the text body";
        $this->Mail->Subject .= ": Plain + Multiple FileAttachments";

        if(!$this->Mail->AddAttachment("test.png"))
        {
            $this->assert(false, $this->Mail->ErrorInfo);
            return;
        }

        if(!$this->Mail->AddAttachment("phpmailer_test.php", "test.txt"))
        {
            $this->assert(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->BuildBody();
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
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
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Plain quoted-printable message.
     */
    function test_Quoted_Printable() {

        $this->Mail->Body = "Here is the main body";
        $this->Mail->Subject .= ": Plain + Quoted-printable";
        $this->Mail->Encoding = "quoted-printable";

        $this->BuildBody();
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
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
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple HTML and attachment test
     */
    function test_HTML_Attachment() {

        $this->Mail->Body = "This is the <b>HTML</b> part of the email.";
        $this->Mail->Subject .= ": HTML + Attachment";
        $this->Mail->IsHTML(true);
        
        if(!$this->Mail->AddAttachment("phpmailer_test.php", "test_attach.txt"))
        {
            $this->assert(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->BuildBody();
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
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
            $this->assert(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->BuildBody();
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
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
            $this->assert(false, $this->Mail->ErrorInfo);
            return;
        }

        if(!$this->Mail->AddAttachment("phpmailer_test.php", "test.txt"))
        {
            $this->assert(false, $this->Mail->ErrorInfo);
            return;
        }
        
        $this->BuildBody();
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
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
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple HTML and attachment test
     */
    function test_AltBody_Attachment() {

        $this->Mail->Body = "This is the <b>HTML</b> part of the email.";
        $this->Mail->AltBody = "This is the text part of the email.";
        $this->Mail->Subject .= ": AltBody + Attachment";
        $this->Mail->IsHTML(true);
        
        if(!$this->Mail->AddAttachment("phpmailer_test.php", "test_attach.txt"))
        {
            $this->assert(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->BuildBody();
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);

        $fp = fopen("message.txt", "w");
        fwrite($fp, $this->Mail->CreateHeader() . $this->Mail->CreateBody());
        fclose($fp);
    }    

    function test_MultipleSend() {
        $this->Mail->Body = "Sending two messages without keepalive";
        $this->BuildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ": SMTP 1";
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
        
        $this->Mail->Subject = $subject . ": SMTP 2";
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    function test_SmtpKeepAlive() {
        $this->Mail->Body = "This was done using the SMTP keep-alive.";
        $this->BuildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->SMTPKeepAlive = true;
        $this->Mail->Subject = $subject . ": SMTP keep-alive 1";
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
        
        $this->Mail->Subject = $subject . ": SMTP keep-alive 2";
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
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
        $this->assert($this->Mail->Send(), $this->Mail->ErrorInfo);
    }
    
    function test_Error() {
        $this->Mail->Subject .= ": This should be sent"; 
        $this->BuildBody();
        $this->Mail->ClearAllRecipients(); // no addresses should cause an error
        $this->assert($this->Mail->IsError() == false, "Error found");
        $this->assert($this->Mail->Send() == false, "Send succeeded");
        $this->assert($this->Mail->IsError(), "No error found");
        $this->assertEquals('You must provide at least one ' .
                            'recipient email address.', $this->Mail->ErrorInfo);
        $this->Mail->AddAddress(get("mail_to"));
        $this->assert($this->Mail->Send(), "Send failed");
    }
}  
 
/**
 * Create and run test instance.
 */
 
if(isset($HTTP_GET_VARS))
    $global_vars = $HTTP_GET_VARS;
else
    $global_vars = $_REQUEST;

if(isset($global_vars["submitted"]))
{
    echo "Test results:<br>";
    $suite = new TestSuite( "phpmailerTest" );
    
    $testRunner = new TestRunner;
    $testRunner->run($suite);
    echo "<hr noshade/>";
}

function get($sName) {
    global $global_vars;
    if(isset($global_vars[$sName]))
        return $global_vars[$sName];
    else
        return "";
}

?>

<html>
<body>
<h3>phpmailer Unit Test</h3>
By entering a SMTP hostname it will automatically perform tests with SMTP.

<form name="phpmailer_unit" action="phpmailer_test.php" method="get">
<input type="hidden" name="submitted" value="1"/>
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
