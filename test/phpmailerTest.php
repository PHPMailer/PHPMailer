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
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

require 'PHPUnit/Autoload.php';

/**
 * PHPMailer - PHP email transport unit test class
 * Performs authentication tests
 */
class phpmailerTest extends PHPUnit_Framework_TestCase
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
    public $Host = '';

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
        $this->Mail->Encoding = '8bit';
        $this->Mail->CharSet = 'iso-8859-1';
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
        $this->Mail->PluginDir = $this->INCLUDE_DIR;
        $this->Mail->AddReplyTo('no_reply@phpmailer.example.com', 'Reply Guy');
        $this->Mail->Sender = 'unit_test@phpmailer.example.com';

        if (strlen($this->Mail->Host) > 0) {
            $this->Mail->Mailer = 'smtp';
        } else {
            $this->Mail->Mailer = 'mail';
            $this->Mail->Sender = 'unit_test@phpmailer.example.com';
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
        if ($this->Mail->ContentType == 'text/html' || strlen($this->Mail->AltBody) > 0) {
            $eol = '<br/>';
            $bullet = '<li>';
            $bullet_start = '<ul>';
            $bullet_end = '</ul>';
        } else {
            $eol = "\n";
            $bullet = ' - ';
            $bullet_start = '';
            $bullet_end = '';
        }

        $ReportBody = '';

        $ReportBody .= '---------------------' . $eol;
        $ReportBody .= 'Unit Test Information' . $eol;
        $ReportBody .= '---------------------' . $eol;
        $ReportBody .= 'phpmailer version: ' . $this->Mail->Version . $eol;
        $ReportBody .= 'Content Type: ' . $this->Mail->ContentType . $eol;

        if (strlen($this->Mail->Host) > 0) {
            $ReportBody .= 'Host: ' . $this->Mail->Host . $eol;
        }

        // If attachments then create an attachment list
        $attachments = $this->Mail->GetAttachments();
        if (count($attachments) > 0) {
            $ReportBody .= 'Attachments:' . $eol;
            $ReportBody .= $bullet_start;
            foreach ($attachments as $attachment) {
                $ReportBody .= $bullet . 'Name: ' . $attachment[1] . ', ';
                $ReportBody .= 'Encoding: ' . $attachment[3] . ', ';
                $ReportBody .= 'Type: ' . $attachment[4] . $eol;
            }
            $ReportBody .= $bullet_end . $eol;
        }

        // If there are changes then list them
        if (count($this->ChangeLog) > 0) {
            $ReportBody .= 'Changes' . $eol;
            $ReportBody .= '-------' . $eol;

            $ReportBody .= $bullet_start;
            for ($i = 0; $i < count($this->ChangeLog); $i++) {
                $ReportBody .= $bullet . $this->ChangeLog[$i][0] . ' was changed to [' .
                    $this->ChangeLog[$i][1] . ']' . $eol;
            }
            $ReportBody .= $bullet_end . $eol . $eol;
        }

        // If there are notes then list them
        if (count($this->NoteLog) > 0) {
            $ReportBody .= 'Notes' . $eol;
            $ReportBody .= '-----' . $eol;

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
            $this->AddChange('Priority', $this->Mail->Priority);
        }
        if ($this->Mail->Encoding != '8bit') {
            $this->AddChange('Encoding', $this->Mail->Encoding);
        }
        if ($this->Mail->CharSet != 'iso-8859-1') {
            $this->AddChange('CharSet', $this->Mail->CharSet);
        }
        if ($this->Mail->Sender != '') {
            $this->AddChange('Sender', $this->Mail->Sender);
        }
        if ($this->Mail->WordWrap != 0) {
            $this->AddChange('WordWrap', $this->Mail->WordWrap);
        }
        if ($this->Mail->Mailer != 'mail') {
            $this->AddChange('Mailer', $this->Mail->Mailer);
        }
        if ($this->Mail->Port != 25) {
            $this->AddChange('Port', $this->Mail->Port);
        }
        if ($this->Mail->Helo != 'localhost.localdomain') {
            $this->AddChange('Helo', $this->Mail->Helo);
        }
        if ($this->Mail->SMTPAuth) {
            $this->AddChange('SMTPAuth', 'true');
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
            case 'bcc':
                return $this->Mail->AddBCC($sAddress, $sName);
        }
        return false;
    }

    /////////////////////////////////////////////////
    // UNIT TESTS
    /////////////////////////////////////////////////

    /**
     * Test CRAM-MD5 authentication
     * Needs a connection to a server that supports this auth mechanism, so commented out by default
     */
    function testAuthCRAMMD5()
    {
        $this->Mail->Host = 'hostname';
        $this->Mail->Port = 587;
        $this->Mail->SMTPAuth = true;
        $this->Mail->SMTPSecure = 'tls';
        $this->Mail->AuthType = 'CRAM-MD5';
        $this->Mail->Username = 'username';
        $this->Mail->Password = 'password';
        $this->Mail->Body = 'Test body';
        $this->Mail->Subject .= ': Auth CRAM-MD5';
        $this->Mail->From = 'from@example.com';
        $this->Mail->Sender = 'from@example.com';
        $this->Mail->ClearAllRecipients();
        $this->Mail->AddAddress('user@example.com');
        //$this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test email address validation
     * Test addresses obtained from http://isemail.info
     * Some failing cases commented out that are apparently up for debate!
     */
    function testValidate()
    {
        $validaddresses = array(
            'first@iana.org',
            'first.last@iana.org',
            '1234567890123456789012345678901234567890123456789012345678901234@iana.org',
            '"first\"last"@iana.org',
            '"first@last"@iana.org',
            '"first\last"@iana.org',
            'first.last@[12.34.56.78]',
            'first.last@[IPv6:::12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333::4444:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:12.34.56.78]',
            'first.last@[IPv6:::1111:2222:3333:4444:5555:6666]',
            'first.last@[IPv6:1111:2222:3333::4444:5555:6666]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666::]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:8888]',
            'first.last@x23456789012345678901234567890123456789012345678901234567890123.iana.org',
            'first.last@3com.com',
            'first.last@123.iana.org',
            '"first\last"@iana.org',
            'first.last@[IPv6:1111:2222:3333::4444:5555:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333::4444:5555:6666:7777]',
            'first.last@example.123',
            'first.last@com',
            '"Abc\@def"@iana.org',
            '"Fred\ Bloggs"@iana.org',
            '"Joe.\Blow"@iana.org',
            '"Abc@def"@iana.org',
            '"Fred Bloggs"@iana.org',
            'user+mailbox@iana.org',
            'customer/department=shipping@iana.org',
            '$A12345@iana.org',
            '!def!xyz%abc@iana.org',
            '_somename@iana.org',
            'dclo@us.ibm.com',
            'peter.piper@iana.org',
            '"Doug \"Ace\" L."@iana.org',
            'test@iana.org',
            'TEST@iana.org',
            '1234567890@iana.org',
            'test+test@iana.org',
            'test-test@iana.org',
            't*est@iana.org',
            '+1~1+@iana.org',
            '{_test_}@iana.org',
            '"[[ test ]]"@iana.org',
            'test.test@iana.org',
            '"test.test"@iana.org',
            'test."test"@iana.org',
            '"test@test"@iana.org',
            'test@123.123.123.x123',
            'test@123.123.123.123',
            'test@[123.123.123.123]',
            'test@example.iana.org',
            'test@example.example.iana.org',
            '"test\test"@iana.org',
            'test@example',
            '"test\blah"@iana.org',
            '"test\blah"@iana.org',
            '"test\"blah"@iana.org',
            'customer/department@iana.org',
            '_Yosemite.Sam@iana.org',
            '~@iana.org',
            '"Austin@Powers"@iana.org',
            'Ima.Fool@iana.org',
            '"Ima.Fool"@iana.org',
            '"Ima Fool"@iana.org',
            '"first"."last"@iana.org',
            '"first".middle."last"@iana.org',
            '"first".last@iana.org',
            'first."last"@iana.org',
            '"first"."middle"."last"@iana.org',
            '"first.middle"."last"@iana.org',
            '"first.middle.last"@iana.org',
            '"first..last"@iana.org',
            '"first\"last"@iana.org',
            'first."mid\dle"."last"@iana.org',
            '"test blah"@iana.org',
            '(foo)cal(bar)@(baz)iamcal.com(quux)',
            'cal@iamcal(woo).(yay)com',
            'cal(woo(yay)hoopla)@iamcal.com',
            'cal(foo\@bar)@iamcal.com',
            'cal(foo\)bar)@iamcal.com',
            'first().last@iana.org',
            'pete(his account)@silly.test(his host)',
            'c@(Chris\'s host.)public.example',
            'jdoe@machine(comment). example',
            '1234 @ local(blah) .machine .example',
            'first(abc.def).last@iana.org',
            'first(a"bc.def).last@iana.org',
            'first.(")middle.last(")@iana.org',
            'first(abc\(def)@iana.org',
            'first.last@x(1234567890123456789012345678901234567890123456789012345678901234567890).com',
            'a(a(b(c)d(e(f))g)h(i)j)@iana.org',
            'name.lastname@domain.com',
            'a@b',
            'a@bar.com',
            'aaa@[123.123.123.123]',
            'a@bar',
            'a-b@bar.com',
            '+@b.c',
            '+@b.com',
            'a@b.co-foo.uk',
            '"hello my name is"@stutter.com',
            '"Test \"Fail\" Ing"@iana.org',
            'valid@about.museum',
            'shaitan@my-domain.thisisminekthx',
            'foobar@192.168.0.1',
            '"Joe\Blow"@iana.org',
            'HM2Kinsists@(that comments are allowed)this.is.ok',
            'user%uucp!path@berkeley.edu',
            'first.last @iana.org',
            'cdburgess+!#$%&\'*-/=?+_{}|~test@gmail.com',
            'first.last@[IPv6:::a2:a3:a4:b1:b2:b3:b4]',
            'first.last@[IPv6:a1:a2:a3:a4:b1:b2:b3::]',
            'first.last@[IPv6:::]',
            'first.last@[IPv6:::b4]',
            'first.last@[IPv6:::b3:b4]',
            'first.last@[IPv6:a1::b4]',
            'first.last@[IPv6:a1::]',
            'first.last@[IPv6:a1:a2::]',
            'first.last@[IPv6:0123:4567:89ab:cdef::]',
            'first.last@[IPv6:0123:4567:89ab:CDEF::]',
            'first.last@[IPv6:::a3:a4:b1:ffff:11.22.33.44]',
            'first.last@[IPv6:::a2:a3:a4:b1:ffff:11.22.33.44]',
            'first.last@[IPv6:a1:a2:a3:a4::11.22.33.44]',
            'first.last@[IPv6:a1:a2:a3:a4:b1::11.22.33.44]',
            'first.last@[IPv6:a1::11.22.33.44]',
            'first.last@[IPv6:a1:a2::11.22.33.44]',
            'first.last@[IPv6:0123:4567:89ab:cdef::11.22.33.44]',
            'first.last@[IPv6:0123:4567:89ab:CDEF::11.22.33.44]',
            'first.last@[IPv6:a1::b2:11.22.33.44]',
            'test@test.com',
            'test@xn--example.com',
            'test@example.com'
        );
        $invalidaddresses = array(
            'first.last@sub.do,com',
            'first\@last@iana.org',
            '123456789012345678901234567890123456789012345678901234567890@12345678901234567890123456789012345678901234 [...]',
            'first.last',
            '12345678901234567890123456789012345678901234567890123456789012345@iana.org',
            '.first.last@iana.org',
            'first.last.@iana.org',
            'first..last@iana.org',
            '"first"last"@iana.org',
            '"""@iana.org',
            '"\"@iana.org',
//            '""@iana.org',
            'first\@last@iana.org',
            'first.last@',
            'x@x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23 [...]',
            'first.last@[.12.34.56.78]',
            'first.last@[12.34.56.789]',
            'first.last@[::12.34.56.78]',
            'first.last@[IPv5:::12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:8888:9999]',
            'first.last@[IPv6:1111:2222::3333::4444:5555:6666]',
            'first.last@[IPv6:1111:2222:333x::4444:5555]',
            'first.last@[IPv6:1111:2222:33333::4444:5555]',
            'first.last@-xample.com',
            'first.last@exampl-.com',
            'first.last@x234567890123456789012345678901234567890123456789012345678901234.iana.org',
            'abc\@def@iana.org',
            'abc\@iana.org',
            'Doug\ \"Ace\"\ Lovell@iana.org',
            'abc@def@iana.org',
            'abc\@def@iana.org',
            'abc\@iana.org',
            '@iana.org',
            'doug@',
            '"qu@iana.org',
            'ote"@iana.org',
            '.dot@iana.org',
            'dot.@iana.org',
            'two..dot@iana.org',
            '"Doug "Ace" L."@iana.org',
            'Doug\ \"Ace\"\ L\.@iana.org',
            'hello world@iana.org',
            'gatsby@f.sc.ot.t.f.i.tzg.era.l.d.',
            'test.iana.org',
            'test.@iana.org',
            'test..test@iana.org',
            '.test@iana.org',
            'test@test@iana.org',
            'test@@iana.org',
            '-- test --@iana.org',
            '[test]@iana.org',
            '"test"test"@iana.org',
            '()[]\;:,><@iana.org',
            'test@.',
            'test@example.',
            'test@.org',
            'test@1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890 [...]',
            'test@[123.123.123.123',
            'test@123.123.123.123]',
            'NotAnEmail',
            '@NotAnEmail',
            '"test"blah"@iana.org',
            '.wooly@iana.org',
            'wo..oly@iana.org',
            'pootietang.@iana.org',
            '.@iana.org',
            'Ima Fool@iana.org',
            'phil.h\@\@ck@haacked.com',
            'foo@[\1.2.3.4]',
//            'first."".last@iana.org',
            'first\last@iana.org',
            'Abc\@def@iana.org',
            'Fred\ Bloggs@iana.org',
            'Joe.\Blow@iana.org',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:12.34.567.89]',
            '{^c\@**Dog^}@cartoon.com',
//            '"foo"(yay)@(hoopla)[1.2.3.4]',
            'cal(foo(bar)@iamcal.com',
            'cal(foo)bar)@iamcal.com',
            'cal(foo\)@iamcal.com',
            'first(12345678901234567890123456789012345678901234567890)last@(123456789012345678901234567890123456789012 [...]',
            'first(middle)last@iana.org',
            'first(abc("def".ghi).mno)middle(abc("def".ghi).mno).last@(abc("def".ghi).mno)example(abc("def".ghi).mno). [...]',
            'a(a(b(c)d(e(f))g)(h(i)j)@iana.org',
            '.@',
            '@bar.com',
            '@@bar.com',
            'aaa.com',
            'aaa@.com',
            'aaa@.123',
            'aaa@[123.123.123.123]a',
            'aaa@[123.123.123.333]',
            'a@bar.com.',
            'a@-b.com',
            'a@b-.com',
            '-@..com',
            '-@a..com',
            'invalid@about.museum-',
            'test@...........com',
            '"Unicode NULL' . chr(0) . '"@char.com',
            'Unicode NULL' . chr(0) . '@char.com',
            'first.last@[IPv6::]',
            'first.last@[IPv6::::]',
            'first.last@[IPv6::b4]',
            'first.last@[IPv6::::b4]',
            'first.last@[IPv6::b3:b4]',
            'first.last@[IPv6::::b3:b4]',
            'first.last@[IPv6:a1:::b4]',
            'first.last@[IPv6:a1:]',
            'first.last@[IPv6:a1:::]',
            'first.last@[IPv6:a1:a2:]',
            'first.last@[IPv6:a1:a2:::]',
            'first.last@[IPv6::11.22.33.44]',
            'first.last@[IPv6::::11.22.33.44]',
            'first.last@[IPv6:a1:11.22.33.44]',
            'first.last@[IPv6:a1:::11.22.33.44]',
            'first.last@[IPv6:a1:a2:::11.22.33.44]',
            'first.last@[IPv6:0123:4567:89ab:cdef::11.22.33.xx]',
            'first.last@[IPv6:0123:4567:89ab:CDEFF::11.22.33.44]',
            'first.last@[IPv6:a1::a4:b1::b4:11.22.33.44]',
            'first.last@[IPv6:a1::11.22.33]',
            'first.last@[IPv6:a1::11.22.33.44.55]',
            'first.last@[IPv6:a1::b211.22.33.44]',
            'first.last@[IPv6:a1::b2::11.22.33.44]',
            'first.last@[IPv6:a1::b3:]',
            'first.last@[IPv6::a2::b4]',
            'first.last@[IPv6:a1:a2:a3:a4:b1:b2:b3:]',
            'first.last@[IPv6::a2:a3:a4:b1:b2:b3:b4]',
            'first.last@[IPv6:a1:a2:a3:a4::b1:b2:b3:b4]'
        );
        $goodfails = array();
        foreach ($validaddresses as $address) {
            if (!PHPMailer::ValidateAddress($address)) {
                $goodfails[] = $address;
            }
        }
        $badpasses = array();
        foreach ($invalidaddresses as $address) {
            if (PHPMailer::ValidateAddress($address)) {
                $badpasses[] = $address;
            }
        }
        $err = '';
        if (count($goodfails) > 0) {
            $err .= "Good addreses that failed validation:\n";
            $err .= implode("\n", $goodfails);
        }
        if (count($badpasses) > 0) {
            if (!empty($err)) {
                $err .= "\n\n";
            }
            $err .= "Bad addreses that passed validation:\n";
            $err .= implode("\n", $badpasses);
        }
        $this->assertEmpty($err, $err);
    }

    /**
     * Try a plain message.
     */
    function test_WordWrap()
    {
        $this->Mail->WordWrap = 40;
        $my_body = 'Here is the main body of this message.  It should ' .
            'be quite a few lines.  It should be wrapped at the ' .
            '40 characters.  Make sure that it is.';
        $nBodyLen = strlen($my_body);
        $my_body .= "\n\nThis is the above body length: " . $nBodyLen;

        $this->Mail->Body = $my_body;
        $this->Mail->Subject .= ': Wordwrap';

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Try a plain message.
     */
    function test_Low_Priority()
    {
        $this->Mail->Priority = 5;
        $this->Mail->Body = 'Here is the main body.  There should be ' .
            'a reply to address in this message.';
        $this->Mail->Subject .= ': Low Priority';
        $this->Mail->AddReplyTo('nobody@nobody.com', 'Nobody (Unit Test)');

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple plain file attachment test.
     */
    function test_Multiple_Plain_FileAttachment()
    {
        $this->Mail->Body = 'Here is the text body';
        $this->Mail->Subject .= ': Plain + Multiple FileAttachments';

        if (!$this->Mail->AddAttachment('test.png')) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        if (!$this->Mail->AddAttachment(__FILE__, 'test.txt')) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple plain string attachment test.
     */
    function test_Plain_StringAttachment()
    {
        $this->Mail->Body = 'Here is the text body';
        $this->Mail->Subject .= ': Plain + StringAttachment';

        $sAttachment = 'These characters are the content of the ' .
            "string attachment.\nThis might be taken from a " .
            'database or some other such thing. ';

        $this->Mail->AddStringAttachment($sAttachment, 'string_attach.txt');

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Plain quoted-printable message.
     */
    function test_Quoted_Printable()
    {
        $this->Mail->Body = 'Here is the main body';
        $this->Mail->Subject .= ': Plain + Quoted-printable';
        $this->Mail->Encoding = 'quoted-printable';

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);

        //Check that a quoted printable encode and decode results in the same as went in
        $t = file_get_contents(__FILE__); //Use this file as test content
        $this->assertEquals(
            $t,
            quoted_printable_decode($this->Mail->EncodeQP($t)),
            'Quoted-Printable encoding round-trip failed'
        );
        $this->assertEquals($this->Mail->EncodeQP($t), $this->Mail->EncodeQPphp($t), 'Quoted-Printable BC wrapper failed');
    }

    /**
     * Try a plain message.
     */
    function test_Html()
    {
        $this->Mail->IsHTML(true);
        $this->Mail->Subject .= ": HTML only";

        $this->Mail->Body = <<<'EOT'
<html>
    <head>
        <title>HTML email test</title>
    </head>
    <body>
        <h1>PHPMailer does HTML!</h1>
        <p>This is a <strong>test message</strong> written in HTML.<br>
        Go to <a href="https://github.com/Synchro/PHPMailer/">https://github.com/Synchro/PHPMailer/</a>
        for new versions of PHPMailer.</p>
        <p>Thank you!</p>
    </body>
</html>
EOT;
        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    function test_MsgHTML() {
        $message = <<<'EOT'
<html>
    <head>
        <title>HTML email test</title>
    </head>
    <body>
        <h1>PHPMailer does HTML!</h1>
        <p>This is a <strong>test message</strong> written in HTML.<br>
        Go to <a href="https://github.com/Synchro/PHPMailer/">https://github.com/Synchro/PHPMailer/</a>
        for new versions of PHPMailer.</p>
        <p>Thank you!</p>
    </body>
</html>
EOT;
        $this->Mail->MsgHTML($message);
        $plainmessage = <<<'EOT'
PHPMailer does HTML!
        This is a test message written in HTML.
        Go to https://github.com/Synchro/PHPMailer/
        for new versions of PHPMailer.
        Thank you!
EOT;

        $this->assertEquals($this->Mail->Body, $message, 'Body not set by MsgHTML');
        $this->assertEquals($this->Mail->AltBody, $plainmessage, 'AltBody not set by MsgHTML');

        //Again, using the advanced HTML to text converter
        $this->Mail->AltBody = '';
        $this->Mail->MsgHTML($message, '', true);
        $this->assertNotEmpty($this->Mail->AltBody, 'Advanced AltBody not set by MsgHTML');

        //Make sure that changes to the original message are reflected when called again
        $message = str_replace('PHPMailer', 'bananas', $message);
        $plainmessage = str_replace('PHPMailer', 'bananas', $plainmessage);
        $this->Mail->MsgHTML($message);
        $this->assertEquals($this->Mail->Body, $message, 'Body not updated by MsgHTML');
        $this->assertEquals($this->Mail->AltBody, $plainmessage, 'AltBody not updated by MsgHTML');

    }
    /**
     * Simple HTML and attachment test
     */
    function test_HTML_Attachment()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->Subject .= ': HTML + Attachment';
        $this->Mail->IsHTML(true);

        if (!$this->Mail->AddAttachment(__FILE__, 'test_attach.txt')) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * An embedded attachment test.
     */
    function test_Embedded_Image()
    {
        $this->Mail->Body = 'Embedded Image: <img alt="phpmailer" src="cid:my-attach">' .
            'Here is an image!</a>';
        $this->Mail->Subject .= ': Embedded Image';
        $this->Mail->IsHTML(true);

        if (!$this->Mail->AddEmbeddedImage(
            'test.png',
            'my-attach',
            'test.png',
            'base64',
            'image/png'
        )
        ) {
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
    function test_Multi_Embedded_Image()
    {
        $this->Mail->Body = 'Embedded Image: <img alt="phpmailer" src="cid:my-attach">' .
            'Here is an image!</a>';
        $this->Mail->Subject .= ': Embedded Image + Attachment';
        $this->Mail->IsHTML(true);

        if (!$this->Mail->AddEmbeddedImage(
            'test.png',
            'my-attach',
            'test.png',
            'base64',
            'image/png'
        )
        ) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        if (!$this->Mail->AddAttachment(__FILE__, 'test.txt')) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);
            return;
        }

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple multipart/alternative test.
     */
    function test_AltBody()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->AltBody = 'Here is the text body of this message.  ' .
            'It should be quite a few lines.  It should be wrapped at the ' .
            '40 characters.  Make sure that it is.';
        $this->Mail->WordWrap = 40;
        $this->AddNote('This is a mulipart alternative email');
        $this->Mail->Subject .= ': AltBody + Word Wrap';

        $this->BuildBody();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple HTML and attachment test
     */
    function test_AltBody_Attachment()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->AltBody = 'This is the text part of the email.';
        $this->Mail->Subject .= ': AltBody + Attachment';
        $this->Mail->IsHTML(true);

        if (!$this->Mail->AddAttachment(__FILE__, 'test_attach.txt')) {
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

    /**
     * Test sending multiple messages with separate connections
     */
    function test_MultipleSend()
    {
        $this->Mail->Body = 'Sending two messages without keepalive';
        $this->BuildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ': SMTP 1';
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);

        $this->Mail->Subject = $subject . ': SMTP 2';
        $this->Mail->Sender = 'blah@example.com';
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test sending using SendMail
     */
    function test_SendmailSend()
    {
        $this->Mail->Body = 'Sending via sendmail';
        $this->BuildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ': sendmail';
        $this->Mail->IsSendmail();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test sending using Qmail
     */
    function test_QmailSend()
    {
      //Only run if we have qmail installed
      if (file_exists('/var/qmail/bin/qmail-inject')) {
        $this->Mail->Body = 'Sending via qmail';
        $this->BuildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ': qmail';
        $this->Mail->IsQmail();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
      }
    }

    /**
     * Test sending using PHP mail() function
     */
    function test_MailSend()
    {
        $sendmail = ini_get('sendmail_path');
        if (strpos($sendmail, '/') === false) { //No path in sendmail_path
            ini_set('sendmail_path', '/usr/sbin/sendmail -t -i ');
        }
        $this->Mail->Body = 'Sending via mail()';
        $this->BuildBody();

        $this->Mail->Subject = $this->Mail->Subject . ': mail()';
        $this->Mail->IsMail();
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test sending an empty body
     */
    function test_Empty_Body()
    {
        $this->BuildBody();
        $this->Mail->Body = '';
        $this->Mail->Subject = $this->Mail->Subject . ': Empty Body';
        $this->Mail->IsMail();
        $this->Mail->AllowEmpty = true;
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
        $this->Mail->AllowEmpty = false;
        $this->assertFalse($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test keepalive (sending multiple messages in a single connection)
     */
    function test_SmtpKeepAlive()
    {
        $this->Mail->Body = 'This was done using the SMTP keep-alive.';
        $this->BuildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->SMTPKeepAlive = true;
        $this->Mail->Subject = $subject . ': SMTP keep-alive 1';
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);

        $this->Mail->Subject = $subject . ': SMTP keep-alive 2';
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
        $this->Mail->SmtpClose();
    }

    /**
     * Tests this denial of service attack:
     *    http://www.cybsec.com/vuln/PHPMailer-DOS.pdf
     */
    function test_DenialOfServiceAttack()
    {
        $this->Mail->Body = 'This should no longer cause a denial of service.';
        $this->BuildBody();

        $this->Mail->Subject = substr(str_repeat('0123456789', 100), 0, 998);
        $this->assertTrue($this->Mail->Send(), $this->Mail->ErrorInfo);
    }

    /**
     * Tests this denial of service attack:
     *    https://sourceforge.net/p/phpmailer/bugs/383/
     * According to the ticket, this should get stuck in a loop, though I can't make it happen.
     */
    function test_DenialOfServiceAttack2()
    {
        //Encoding name longer than 68 chars
        $this->Mail->Encoding = '1234567890123456789012345678901234567890123456789012345678901234567890';
        //Call WrapText with a zero length value
        $t = $this->Mail->WrapText(str_repeat('This should no longer cause a denial of service. ', 30), 0);
    }

    /**
     * Test error handling
     */
    function test_Error()
    {
        $this->Mail->Subject .= ': This should be sent';
        $this->BuildBody();
        $this->Mail->ClearAllRecipients(); // no addresses should cause an error
        $this->assertTrue($this->Mail->IsError() == false, 'Error found');
        $this->assertTrue($this->Mail->Send() == false, 'Send succeeded');
        $this->assertTrue($this->Mail->IsError(), 'No error found');
        $this->assertEquals('You must provide at least one recipient email address.', $this->Mail->ErrorInfo);
        $this->Mail->AddAddress($_REQUEST['mail_to']);
        $this->assertTrue($this->Mail->Send(), 'Send failed');
    }

    /**
     * Test addressing
     */
    function test_Addressing()
    {
        $this->assertFalse($this->Mail->AddAddress(''), 'Empty address accepted');
        $this->assertFalse($this->Mail->AddAddress('', 'Nobody'), 'Empty address with name accepted');
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
     * Test address escaping
     */
    function test_AddressEscaping()
    {
        $this->Mail->Subject .= ': Address escaping';
        $this->Mail->ClearAddresses();
        $this->Mail->AddAddress('foo@example.com', 'Tim "The Book" O\'Reilly');
        $this->Mail->Body = 'Test correct escaping of quotes in addresses.';
        $this->BuildBody();
        $this->Mail->PreSend();
        $b = $this->Mail->GetSentMIMEMessage();
        $this->assertTrue((strpos($b, 'To: "Tim \"The Book\" O\'Reilly" <foo@example.com>') !==false));
    }

    /**
     * Test BCC-only addressing
     */
    function test_BCCAddressing()
    {
        $this->Mail->Subject .= ': BCC-only addressing';
        $this->BuildBody();
        $this->Mail->ClearAllRecipients();
        $this->assertTrue($this->Mail->AddBCC('a@example.com'), 'BCC addressing failed');
        $this->assertTrue($this->Mail->Send(), 'Send failed');
    }

    /**
     * Encoding tests
     */
    function test_Encodings()
    {
        $this->Mail->CharSet = 'iso-8859-1';
        $this->assertEquals(
            '=A1Hola!_Se=F1or!',
            $this->Mail->EncodeQ("\xa1Hola! Se\xf1or!", 'text'),
            'Q Encoding (text) failed'
        );
        $this->assertEquals(
            '=A1Hola!_Se=F1or!',
            $this->Mail->EncodeQ("\xa1Hola! Se\xf1or!", 'comment'),
            'Q Encoding (comment) failed'
        );
        $this->assertEquals(
            '=A1Hola!_Se=F1or!',
            $this->Mail->EncodeQ("\xa1Hola! Se\xf1or!", 'phrase'),
            'Q Encoding (phrase) failed'
        );
        $this->Mail->CharSet = 'UTF-8';
        $this->assertEquals(
            '=C2=A1Hola!_Se=C3=B1or!',
            $this->Mail->EncodeQ("\xc2\xa1Hola! Se\xc3\xb1or!", 'text'),
            'Q Encoding (text) failed'
        );
    }

    /**
     * Signing tests
     */
    function test_Signing()
    {
        $this->Mail->Body = 'This message is S/MIME signed.';
        $this->BuildBody();

        $dn = array(
            'countryName' => 'UK',
            'stateOrProvinceName' => 'Here',
            'localityName' => 'There',
            'organizationName' => 'PHP',
            'organizationalUnitName' => 'PHPMailer',
            'commonName' => 'PHPMailer Test',
            'emailAddress' => 'phpmailer@example.com'
        );
        $password = 'password';
        $certfile = 'certfile.txt';
        $keyfile = 'keyfile.txt';

        //Make a new key pair
        $pk = openssl_pkey_new();
        //Create a certificate signing request
        $csr = openssl_csr_new($dn, $pk);
        //Create a self-signed cert
        $cert = openssl_csr_sign($csr, null, $pk, 1);
        //Save the cert
        openssl_x509_export($cert, $certout);
        file_put_contents($certfile, $certout);
        //Save the key
        openssl_pkey_export($pk, $pkeyout, $password);
        file_put_contents($keyfile, $pkeyout);

        $this->Mail->Sign(
            $certfile,
            $keyfile,
            $password
        );
        $this->assertTrue($this->Mail->Send(), 'S/MIME signing failed');
        unlink($certfile);
        unlink($keyfile);
    }

    /**
     * Miscellaneous calls to improve test coverage and some small tests
     */
    function test_Miscellaneous()
    {
        $this->assertEquals('application/pdf', PHPMailer::_mime_types('pdf'), 'MIME TYPE lookup failed');
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
