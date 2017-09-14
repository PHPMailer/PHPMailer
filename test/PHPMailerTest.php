<?php
/**
 * PHPMailer - PHP email transport unit tests.
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2012 - 2017 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace PHPMailer\Test;

use PHPMailer\PHPMailer\PHPMailer;
use PHPUnit\Framework\TestCase;

/**
 * PHPMailer - PHP email transport unit test class.
 */
final class PHPMailerTest extends TestCase
{
    /**
     * Holds the PHPMailer instance.
     *
     * @var PHPMailer
     */
    private $Mail;

    /**
     * Holds the SMTP mail host.
     *
     * @var string
     */
    private $Host = '';

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
     * Default include path.
     *
     * @var string
     */
    private $INCLUDE_DIR = '..';

    /**
     * PIDs of any processes we need to kill.
     *
     * @var array
     */
    private $pids = [];

    /**
     * Run before each test is started.
     */
    protected function setUp()
    {
        $this->INCLUDE_DIR = dirname(__DIR__); //Default to the dir above the test dir, i.e. the project home dir
        if (file_exists($this->INCLUDE_DIR . '/test/testbootstrap.php')) {
            include $this->INCLUDE_DIR . '/test/testbootstrap.php'; //Overrides go in here
        }
        $this->Mail = new PHPMailer();
        $this->Mail->SMTPDebug = 3; //Full debug output
        $this->Mail->Debugoutput = ['PHPMailer\Test\DebugLogTestListener', 'debugLog'];
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
        $this->Mail->addReplyTo('no_reply@phpmailer.example.com', 'Reply Guy');
        $this->Mail->Sender = 'unit_test@phpmailer.example.com';
        if (strlen($this->Mail->Host) > 0) {
            $this->Mail->isSMTP();
        } else {
            $this->Mail->isMail();
        }
        if (array_key_exists('mail_to', $_REQUEST)) {
            $this->setAddress($_REQUEST['mail_to'], 'Test User', 'to');
        }
        if (array_key_exists('mail_cc', $_REQUEST) and strlen($_REQUEST['mail_cc']) > 0) {
            $this->setAddress($_REQUEST['mail_cc'], 'Carbon User', 'cc');
        }
    }

    /**
     * Run after each test is completed.
     */
    protected function tearDown()
    {
        // Clean global variables
        $this->Mail = null;
        $this->ChangeLog = [];
        $this->NoteLog = [];

        foreach ($this->pids as $pid) {
            $p = escapeshellarg($pid);
            shell_exec("ps $p && kill -TERM $p");
        }
    }

    /**
     * Build the body of the message in the appropriate format.
     */
    private function buildBody()
    {
        $this->checkChanges();

        // Determine line endings for message
        if ('text/html' == $this->Mail->ContentType || strlen($this->Mail->AltBody) > 0) {
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

        if (strlen($this->Mail->Host) > 0) {
            $ReportBody .= 'Host: ' . $this->Mail->Host . $eol;
        }

        // If attachments then create an attachment list
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

        // If there are changes then list them
        if (count($this->ChangeLog) > 0) {
            $ReportBody .= 'Changes' . $eol;
            $ReportBody .= '-------' . $eol;

            $ReportBody .= $list_start;
            for ($i = 0; $i < count($this->ChangeLog); ++$i) {
                $ReportBody .= $bullet_start . $this->ChangeLog[$i][0] . ' was changed to [' .
                    $this->ChangeLog[$i][1] . ']' . $bullet_end;
            }
            $ReportBody .= $list_end . $eol . $eol;
        }

        // If there are notes then list them
        if (count($this->NoteLog) > 0) {
            $ReportBody .= 'Notes' . $eol;
            $ReportBody .= '-----' . $eol;

            $ReportBody .= $list_start;
            for ($i = 0; $i < count($this->NoteLog); ++$i) {
                $ReportBody .= $bullet_start . $this->NoteLog[$i] . $bullet_end;
            }
            $ReportBody .= $list_end;
        }

        // Re-attach the original body
        $this->Mail->Body .= $eol . $ReportBody;
    }

    /**
     * Check which default settings have been changed for the report.
     */
    private function checkChanges()
    {
        if (3 != $this->Mail->Priority) {
            $this->addChange('Priority', $this->Mail->Priority);
        }
        if ('8bit' != $this->Mail->Encoding) {
            $this->addChange('Encoding', $this->Mail->Encoding);
        }
        if ('iso-8859-1' != $this->Mail->CharSet) {
            $this->addChange('CharSet', $this->Mail->CharSet);
        }
        if ('' != $this->Mail->Sender) {
            $this->addChange('Sender', $this->Mail->Sender);
        }
        if (0 != $this->Mail->WordWrap) {
            $this->addChange('WordWrap', $this->Mail->WordWrap);
        }
        if ('mail' != $this->Mail->Mailer) {
            $this->addChange('Mailer', $this->Mail->Mailer);
        }
        if (25 != $this->Mail->Port) {
            $this->addChange('Port', $this->Mail->Port);
        }
        if ('localhost.localdomain' != $this->Mail->Helo) {
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
    private function addChange($sName, $sNewValue)
    {
        $this->ChangeLog[] = [$sName, $sNewValue];
    }

    /**
     * Adds a simple note to the message.
     *
     * @param string $sValue
     */
    private function addNote($sValue)
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
    private function setAddress($sAddress, $sName = '', $sType = 'to')
    {
        switch ($sType) {
            case 'to':
                return $this->Mail->addAddress($sAddress, $sName);
            case 'cc':
                return $this->Mail->addCC($sAddress, $sName);
            case 'bcc':
                return $this->Mail->addBCC($sAddress, $sName);
        }

        return false;
    }

    /**
     * Check that we have loaded default test params.
     * Pretty much everything will fail due to unset recipient if this is not done.
     */
    public function testBootstrap()
    {
        $this->assertFileExists(
            $this->INCLUDE_DIR . '/test/testbootstrap.php',
            'Test config params missing - copy testbootstrap.php to testbootstrap-dist.php and change as appropriate'
        );
    }

    /**
     * Test CRAM-MD5 authentication.
     * Needs a connection to a server that supports this auth mechanism, so commented out by default.
     */
    public function testAuthCRAMMD5()
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
        $this->Mail->clearAllRecipients();
        $this->Mail->addAddress('user@example.com');
        //$this->assertTrue($this->mail->send(), $this->mail->ErrorInfo);
    }

    /**
     * Test email address validation.
     * Test addresses obtained from http://isemail.info
     * Some failing cases commented out that are apparently up for debate!
     */
    public function testValidate()
    {
        $validaddresses = [
            'first@example.org',
            'first.last@example.org',
            '1234567890123456789012345678901234567890123456789012345678901234@example.org',
            '"first\"last"@example.org',
            '"first@last"@example.org',
            '"first\last"@example.org',
            'first.last@[12.34.56.78]',
            'first.last@x23456789012345678901234567890123456789012345678901234567890123.example.org',
            'first.last@123.example.org',
            '"first\last"@example.org',
            '"Abc\@def"@example.org',
            '"Fred\ Bloggs"@example.org',
            '"Joe.\Blow"@example.org',
            '"Abc@def"@example.org',
            'user+mailbox@example.org',
            'customer/department=shipping@example.org',
            '$A12345@example.org',
            '!def!xyz%abc@example.org',
            '_somename@example.org',
            'dclo@us.example.com',
            'peter.piper@example.org',
            'test@example.org',
            'TEST@example.org',
            '1234567890@example.org',
            'test+test@example.org',
            'test-test@example.org',
            't*est@example.org',
            '+1~1+@example.org',
            '{_test_}@example.org',
            'test.test@example.org',
            '"test.test"@example.org',
            'test."test"@example.org',
            '"test@test"@example.org',
            'test@123.123.123.x123',
            'test@[123.123.123.123]',
            'test@example.example.org',
            'test@example.example.example.org',
            '"test\test"@example.org',
            '"test\blah"@example.org',
            '"test\blah"@example.org',
            '"test\"blah"@example.org',
            'customer/department@example.org',
            '_Yosemite.Sam@example.org',
            '~@example.org',
            '"Austin@Powers"@example.org',
            'Ima.Fool@example.org',
            '"Ima.Fool"@example.org',
            '"first"."last"@example.org',
            '"first".middle."last"@example.org',
            '"first".last@example.org',
            'first."last"@example.org',
            '"first"."middle"."last"@example.org',
            '"first.middle"."last"@example.org',
            '"first.middle.last"@example.org',
            '"first..last"@example.org',
            '"first\"last"@example.org',
            'first."mid\dle"."last"@example.org',
            'name.lastname@example.com',
            'a@example.com',
            'aaa@[123.123.123.123]',
            'a-b@example.com',
            '+@b.c',
            '+@b.com',
            'a@b.co-foo.uk',
            'valid@about.museum',
            'shaitan@my-domain.thisisminekthx',
            '"Joe\Blow"@example.org',
            'user%uucp!path@example.edu',
            'cdburgess+!#$%&\'*-/=?+_{}|~test@example.com',
            'test@test.com',
            'test@xn--example.com',
            'test@example.com',
        ];
        //These are invalid according to PHP's filter_var
        //which doesn't allow dotless domains, numeric TLDs or unbracketed IPv4 literals
        $invalidphp = [
            'a@b',
            'a@bar',
            'first.last@com',
            'test@123.123.123.123',
            'foobar@192.168.0.1',
            'first.last@example.123',
        ];
        //Valid RFC 5322 addresses using quoting and comments
        //Note that these are *not* all valid for RFC5321
        $validqandc = [
            'HM2Kinsists@(that comments are allowed)this.is.ok',
            '"Doug \"Ace\" L."@example.org',
            '"[[ test ]]"@example.org',
            '"Ima Fool"@example.org',
            '"test blah"@example.org',
            '(foo)cal(bar)@(baz)example.com(quux)',
            'cal@example(woo).(yay)com',
            'cal(woo(yay)hoopla)@example.com',
            'cal(foo\@bar)@example.com',
            'cal(foo\)bar)@example.com',
            'first().last@example.org',
            'pete(his account)@silly.test(his host)',
            'c@(Chris\'s host.)public.example',
            'jdoe@machine(comment). example',
            '1234 @ local(blah) .machine .example',
            'first(abc.def).last@example.org',
            'first(a"bc.def).last@example.org',
            'first.(")middle.last(")@example.org',
            'first(abc\(def)@example.org',
            'first.last@x(1234567890123456789012345678901234567890123456789012345678901234567890).com',
            'a(a(b(c)d(e(f))g)h(i)j)@example.org',
            '"hello my name is"@example.com',
            '"Test \"Fail\" Ing"@example.org',
            'first.last @example.org',
        ];
        //Valid explicit IPv6 numeric addresses
        $validipv6 = [
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
            'first.last@[IPv6:::12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333::4444:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:12.34.56.78]',
            'first.last@[IPv6:::1111:2222:3333:4444:5555:6666]',
            'first.last@[IPv6:1111:2222:3333::4444:5555:6666]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666::]',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:7777:8888]',
            'first.last@[IPv6:1111:2222:3333::4444:5555:12.34.56.78]',
            'first.last@[IPv6:1111:2222:3333::4444:5555:6666:7777]',
        ];
        $invalidaddresses = [
            'first.last@sub.do,com',
            'first\@last@iana.org',
            '123456789012345678901234567890123456789012345678901234567890' .
            '@12345678901234567890123456789012345678901234 [...]',
            'first.last',
            '12345678901234567890123456789012345678901234567890123456789012345@iana.org',
            '.first.last@iana.org',
            'first.last.@iana.org',
            'first..last@iana.org',
            '"first"last"@iana.org',
            '"""@iana.org',
            '"\"@iana.org',
            //'""@iana.org',
            'first\@last@iana.org',
            'first.last@',
            'x@x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.' .
            'x23456789.x23456789.x23456789.x23 [...]',
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
            //'helloworld@iana .org',
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
            'test@12345678901234567890123456789012345678901234567890123456789012345678901234567890' .
            '12345678901234567890 [...]',
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
            //'first."".last@iana.org',
            'first\last@iana.org',
            'Abc\@def@iana.org',
            'Fred\ Bloggs@iana.org',
            'Joe.\Blow@iana.org',
            'first.last@[IPv6:1111:2222:3333:4444:5555:6666:12.34.567.89]',
            '{^c\@**Dog^}@cartoon.com',
            //'"foo"(yay)@(hoopla)[1.2.3.4]',
            'cal(foo(bar)@iamcal.com',
            'cal(foo)bar)@iamcal.com',
            'cal(foo\)@iamcal.com',
            'first(12345678901234567890123456789012345678901234567890)last@(1234567890123456789' .
            '01234567890123456789012 [...]',
            'first(middle)last@iana.org',
            'first(abc("def".ghi).mno)middle(abc("def".ghi).mno).last@(abc("def".ghi).mno)example' .
            '(abc("def".ghi).mno). [...]',
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
            'first.last@[IPv6:a1:a2:a3:a4::b1:b2:b3:b4]',
            //This is a valid RFC5322 address, but we don't want to allow it for obvious reasons!
            "(\r\n RCPT TO:user@example.com\r\n DATA \\\nSubject: spam10\\\n\r\n Hello," .
            "\r\n this is a spam mail.\\\n.\r\n QUIT\r\n ) a@example.net",
        ];
        // IDNs in Unicode and ASCII forms.
        $unicodeaddresses = [
            'first.last@bücher.ch',
            'first.last@кто.рф',
            'first.last@phplíst.com',
        ];
        $asciiaddresses = [
            'first.last@xn--bcher-kva.ch',
            'first.last@xn--j1ail.xn--p1ai',
            'first.last@xn--phplst-6va.com',
        ];
        $goodfails = [];
        foreach (array_merge($validaddresses, $asciiaddresses) as $address) {
            if (!PHPMailer::validateAddress($address)) {
                $goodfails[] = $address;
            }
        }
        $badpasses = [];
        foreach (array_merge($invalidaddresses, $unicodeaddresses) as $address) {
            if (PHPMailer::validateAddress($address)) {
                $badpasses[] = $address;
            }
        }
        $err = '';
        if (count($goodfails) > 0) {
            $err .= "Good addresses that failed validation:\n";
            $err .= implode("\n", $goodfails);
        }
        if (count($badpasses) > 0) {
            if (!empty($err)) {
                $err .= "\n\n";
            }
            $err .= "Bad addresses that passed validation:\n";
            $err .= implode("\n", $badpasses);
        }
        $this->assertEmpty($err, $err);
        //For coverage
        $this->assertTrue(PHPMailer::validateAddress('test@example.com', 'auto'));
        $this->assertFalse(PHPMailer::validateAddress('test@example.com.', 'auto'));
        $this->assertTrue(PHPMailer::validateAddress('test@example.com', 'pcre'));
        $this->assertFalse(PHPMailer::validateAddress('test@example.com.', 'pcre'));
        $this->assertTrue(PHPMailer::validateAddress('test@example.com', 'pcre8'));
        $this->assertFalse(PHPMailer::validateAddress('test@example.com.', 'pcre8'));
        $this->assertTrue(PHPMailer::validateAddress('test@example.com', 'php'));
        $this->assertFalse(PHPMailer::validateAddress('test@example.com.', 'php'));
        $this->assertTrue(PHPMailer::validateAddress('test@example.com', 'noregex'));
        $this->assertFalse(PHPMailer::validateAddress('bad', 'noregex'));
    }

    /**
     * Test injecting a custom validator.
     */
    public function testCustomValidator()
    {
        //Inject a one-off custom validator
        $this->assertTrue(
            PHPMailer::validateAddress(
                'user@example.com',
                function ($address) {
                    return strpos($address, '@') !== false;
                }
            ),
            'Custom validator false negative'
        );
        $this->assertFalse(
            PHPMailer::validateAddress(
                'userexample.com',
                function ($address) {
                    return strpos($address, '@') !== false;
                }
            ),
            'Custom validator false positive'
        );
        //Set the default validator to an injected function
        PHPMailer::$validator = function ($address) {
            return 'user@example.com' === $address;
        };
        $this->assertTrue(
            $this->Mail->addAddress('user@example.com'),
            'Custom default validator false negative'
        );
        $this->assertFalse(
        //Need to pick a failing value which would pass all other validators
        //to be sure we're using our custom one
            $this->Mail->addAddress('bananas@example.com'),
            'Custom default validator false positive'
        );
        //Set default validator to PHP built-in
        PHPMailer::$validator = 'php';
        $this->assertFalse(
        //This is a valid address that FILTER_VALIDATE_EMAIL thinks is invalid
            $this->Mail->addAddress('first.last@example.123'),
            'PHP validator not behaving as expected'
        );
    }

    /**
     * Word-wrap an ASCII message.
     */
    public function testWordWrap()
    {
        $this->Mail->WordWrap = 40;
        $my_body = str_repeat(
            'Here is the main body of this message.  It should ' .
            'be quite a few lines.  It should be wrapped at ' .
            '40 characters.  Make sure that it is. ',
            10
        );
        $nBodyLen = strlen($my_body);
        $my_body .= "\n\nThis is the above body length: " . $nBodyLen;

        $this->Mail->Body = $my_body;
        $this->Mail->Subject .= ': Wordwrap';

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Word-wrap a multibyte message.
     */
    public function testWordWrapMultibyte()
    {
        $this->Mail->WordWrap = 40;
        $my_body = str_repeat(
            '飛兒樂 團光茫 飛兒樂 團光茫 飛兒樂 團光茫 飛兒樂 團光茫 ' .
            '飛飛兒樂 團光茫兒樂 團光茫飛兒樂 團光飛兒樂 團光茫飛兒樂 團光茫兒樂 團光茫 ' .
            '飛兒樂 團光茫飛兒樂 團飛兒樂 團光茫光茫飛兒樂 團光茫. ',
            10
        );
        $nBodyLen = strlen($my_body);
        $my_body .= "\n\nThis is the above body length: " . $nBodyLen;

        $this->Mail->Body = $my_body;
        $this->Mail->Subject .= ': Wordwrap multibyte';

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test low priority.
     */
    public function testLowPriority()
    {
        $this->Mail->Priority = 5;
        $this->Mail->Body = 'Here is the main body.  There should be ' .
            'a reply to address in this message.';
        $this->Mail->Subject .= ': Low Priority';
        $this->Mail->addReplyTo('nobody@nobody.com', 'Nobody (Unit Test)');

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple plain file attachment test.
     */
    public function testMultiplePlainFileAttachment()
    {
        $this->Mail->Body = 'Here is the text body';
        $this->Mail->Subject .= ': Plain + Multiple FileAttachments';

        if (!$this->Mail->addAttachment(realpath($this->INCLUDE_DIR . '/examples/images/phpmailer.png'))) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);

            return;
        }

        if (!$this->Mail->addAttachment(__FILE__, 'test.txt')) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);

            return;
        }

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple plain string attachment test.
     */
    public function testPlainStringAttachment()
    {
        $this->Mail->Body = 'Here is the text body';
        $this->Mail->Subject .= ': Plain + StringAttachment';

        $sAttachment = 'These characters are the content of the ' .
            "string attachment.\nThis might be taken from a " .
            'database or some other such thing. ';

        $this->Mail->addStringAttachment($sAttachment, 'string_attach.txt');

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Plain quoted-printable message.
     */
    public function testQuotedPrintable()
    {
        $this->Mail->Body = 'Here is the main body';
        $this->Mail->Subject .= ': Plain + Quoted-printable';
        $this->Mail->Encoding = 'quoted-printable';

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        //Check that a quoted printable encode and decode results in the same as went in
        $t = file_get_contents(__FILE__); //Use this file as test content
        //Force line breaks to UNIX-style
        $t = str_replace(["\r\n", "\r"], "\n", $t);
        $this->assertEquals(
            $t,
            quoted_printable_decode($this->Mail->encodeQP($t)),
            'Quoted-Printable encoding round-trip failed'
        );
        //Force line breaks to Windows-style
        $t = str_replace("\n", "\r\n", $t);
        $this->assertEquals(
            $t,
            quoted_printable_decode($this->Mail->encodeQP($t)),
            'Quoted-Printable encoding round-trip failed (Windows line breaks)'
        );
    }

    /**
     * Test header encoding & folding.
     */
    public function testHeaderEncoding()
    {
        $this->Mail->CharSet = 'UTF-8';
        //This should select B-encoding automatically and should fold
        $bencode = str_repeat('é', PHPMailer::STD_LINE_LENGTH + 1);
        //This should select Q-encoding automatically and should fold
        $qencode = str_repeat('e', PHPMailer::STD_LINE_LENGTH) . 'é';
        //This should select B-encoding automatically and should not fold
        $bencodenofold = str_repeat('é', 10);
        //This should select Q-encoding automatically and should not fold
        $qencodenofold = str_repeat('e', 9) . 'é';
        //This should not encode, but just fold automatically
        $justfold = str_repeat('e', PHPMailer::STD_LINE_LENGTH + 10);
        //This should not change
        $noencode = 'eeeeeeeeee';
        $this->Mail->isMail();
        //Expected results
        $bencoderes = '=?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6k=?=' . PHPMailer::getLE() .
            ' =?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6k=?=' . PHPMailer::getLE() .
            ' =?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6k=?=' . PHPMailer::getLE() .
            ' =?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6nDqcOpw6k=?=';
        $qencoderes = '=?UTF-8?Q?eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee?=' . PHPMailer::getLE() .
            ' =?UTF-8?Q?eeeeeeeeeeeeeeeeeeeeeeeeee=C3=A9?=';
        $bencodenofoldres = '=?UTF-8?B?w6nDqcOpw6nDqcOpw6nDqcOpw6k=?=';
        $qencodenofoldres = '=?UTF-8?Q?eeeeeeeee=C3=A9?=';
        $justfoldres = 'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee' .
            'eeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee' . PHPMailer::getLE() . ' eeeeeeeeee';
        $noencoderes = 'eeeeeeeeee';
        $this->assertEquals(
            $bencoderes,
            $this->Mail->encodeHeader($bencode),
            'Folded B-encoded header value incorrect'
        );
        $this->assertEquals(
            $qencoderes,
            $this->Mail->encodeHeader($qencode),
            'Folded Q-encoded header value incorrect'
        );
        $this->assertEquals(
            $bencodenofoldres,
            $this->Mail->encodeHeader($bencodenofold),
            'B-encoded header value incorrect'
        );
        $this->assertEquals(
            $qencodenofoldres,
            $this->Mail->encodeHeader($qencodenofold),
            'Q-encoded header value incorrect'
        );
        $this->assertEquals(
            $justfoldres,
            $this->Mail->encodeHeader($justfold),
            'Folded header value incorrect'
        );
        $this->assertEquals(
            $noencoderes,
            $this->Mail->encodeHeader($noencode),
            'Unencoded header value incorrect'
        );
    }

    /**
     * Send an HTML message.
     */
    public function testHtml()
    {
        $this->Mail->isHTML(true);
        $this->Mail->Subject .= ': HTML only';

        $this->Mail->Body = <<<'EOT'
<html>
    <head>
        <title>HTML email test</title>
    </head>
    <body>
        <h1>PHPMailer does HTML!</h1>
        <p>This is a <strong>test message</strong> written in HTML.<br>
        Go to <a href="https://github.com/PHPMailer/PHPMailer/">https://github.com/PHPMailer/PHPMailer/</a>
        for new versions of PHPMailer.</p>
        <p>Thank you!</p>
    </body>
</html>
EOT;
        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $msg = $this->Mail->getSentMIMEMessage();
        $this->assertNotContains("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
    }

    /**
     * Send a message containing ISO-8859-1 text.
     */
    public function testHtmlIso8859()
    {
        $this->Mail->isHTML(true);
        $this->Mail->Subject .= ': ISO-8859-1 HTML';
        $this->Mail->CharSet = 'iso-8859-1';

        //This file is in ISO-8859-1 charset
        //Needs to be external because this file is in UTF-8
        $content = file_get_contents(realpath($this->INCLUDE_DIR . '/examples/contents.html'));
        // This is the string 'éèîüçÅñæß' in ISO-8859-1, base-64 encoded
        $check = base64_decode('6eju/OfF8ebf');
        //Make sure it really is in ISO-8859-1!
        $this->Mail->msgHTML(
            mb_convert_encoding(
                $content,
                'ISO-8859-1',
                mb_detect_encoding($content, 'UTF-8, ISO-8859-1, ISO-8859-15', true)
            ),
            realpath($this->INCLUDE_DIR . '/examples')
        );
        $this->buildBody();
        $this->assertTrue(
            strpos($this->Mail->Body, $check) !== false,
            'ISO message body does not contain expected text'
        );
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Send a message containing multilingual UTF-8 text.
     */
    public function testHtmlUtf8()
    {
        $this->Mail->isHTML(true);
        $this->Mail->Subject .= ': UTF-8 HTML Пустое тело сообщения';
        $this->Mail->CharSet = 'UTF-8';

        $this->Mail->Body = <<<'EOT'
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>HTML email test</title>
    </head>
    <body>
        <p>Chinese text: 郵件內容為空</p>
        <p>Russian text: Пустое тело сообщения</p>
        <p>Armenian text: Հաղորդագրությունը դատարկ է</p>
        <p>Czech text: Prázdné tělo zprávy</p>
    </body>
</html>
EOT;
        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $msg = $this->Mail->getSentMIMEMessage();
        $this->assertNotContains("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
    }

    /**
     * Send a message containing multilingual UTF-8 text with an embedded image.
     */
    public function testUtf8WithEmbeddedImage()
    {
        $this->Mail->isHTML(true);
        $this->Mail->Subject .= ': UTF-8 with embedded image';
        $this->Mail->CharSet = 'UTF-8';

        $this->Mail->Body = <<<'EOT'
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <title>HTML email test</title>
    </head>
    <body>
        <p>Chinese text: 郵件內容為空</p>
        <p>Russian text: Пустое тело сообщения</p>
        <p>Armenian text: Հաղորդագրությունը դատարկ է</p>
        <p>Czech text: Prázdné tělo zprávy</p>
        Embedded Image: <img alt="phpmailer" src="cid:my-attach">
    </body>
</html>
EOT;
        $this->Mail->addEmbeddedImage(
            realpath($this->INCLUDE_DIR . '/examples/images/phpmailer.png'),
            'my-attach',
            'phpmailer.png',
            'base64',
            'image/png'
        );
        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Send a message containing multilingual UTF-8 text.
     */
    public function testPlainUtf8()
    {
        $this->Mail->isHTML(false);
        $this->Mail->Subject .= ': UTF-8 plain text';
        $this->Mail->CharSet = 'UTF-8';

        $this->Mail->Body = <<<'EOT'
Chinese text: 郵件內容為空
Russian text: Пустое тело сообщения
Armenian text: Հաղորդագրությունը դատարկ է
Czech text: Prázdné tělo zprávy
EOT;
        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $msg = $this->Mail->getSentMIMEMessage();
        $this->assertNotContains("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
    }

    /**
     * Test simple message builder and html2text converters.
     */
    public function testMsgHTML()
    {
        $message = file_get_contents(realpath($this->INCLUDE_DIR . '/examples/contentsutf8.html'));
        $this->Mail->CharSet = 'utf-8';
        $this->Mail->Body = '';
        $this->Mail->AltBody = '';
        //Uses internal HTML to text conversion
        $this->Mail->msgHTML($message, realpath($this->INCLUDE_DIR . '/examples'));
        $sub = $this->Mail->Subject . ': msgHTML';
        $this->Mail->Subject .= $sub;

        $this->assertNotEmpty($this->Mail->Body, 'Body not set by msgHTML');
        $this->assertNotEmpty($this->Mail->AltBody, 'AltBody not set by msgHTML');
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        //Again, using a custom HTML to text converter
        $this->Mail->AltBody = '';
        $this->Mail->msgHTML(
            $message,
            realpath($this->INCLUDE_DIR . '/examples'),
            function ($html) {
                return strtoupper(strip_tags($html));
            }
        );
        $this->Mail->Subject = $sub . ' + custom html2text';
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        //Test that local paths without a basedir are ignored
        $this->Mail->msgHTML('<img src="/etc/hostname">test');
        $this->assertTrue(strpos($this->Mail->Body, 'src="/etc/hostname"') !== false);
        //Test that local paths with a basedir are not ignored
        $this->Mail->msgHTML('<img src="composer.json">test', realpath($this->INCLUDE_DIR));
        $this->assertTrue(strpos($this->Mail->Body, 'src="composer.json"') === false);
        //Test that local paths with parent traversal are ignored
        $this->Mail->msgHTML('<img src="../composer.json">test', realpath($this->INCLUDE_DIR));
        $this->assertTrue(strpos($this->Mail->Body, 'src="composer.json"') === false);
        //Test that existing embedded URLs are ignored
        $this->Mail->msgHTML('<img src="cid:5d41402abc4b2a76b9719d911017c592">test');
        $this->assertTrue(
            strpos($this->Mail->Body, 'src="cid:5d41402abc4b2a76b9719d911017c592"') !== false
        );
        //Test that absolute URLs are ignored
        $this->Mail->msgHTML('<img src="https://github.com/PHPMailer/PHPMailer/blob/master/composer.json">test');
        $this->assertTrue(
            strpos(
                $this->Mail->Body,
                'src="https://github.com/PHPMailer/PHPMailer/blob/master/composer.json"'
            ) !== false
        );
        //Test that absolute URLs with anonymous/relative protocol are ignored
        //Note that such URLs will not work in email anyway because they have no protocol to be relative to
        $this->Mail->msgHTML('<img src="//github.com/PHPMailer/PHPMailer/blob/master/composer.json">test');
        $this->assertTrue(
            strpos(
                $this->Mail->Body,
                'src="//github.com/PHPMailer/PHPMailer/blob/master/composer.json"'
            ) !== false
        );
    }

    /**
     * Simple HTML and attachment test.
     */
    public function testHTMLAttachment()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->Subject .= ': HTML + Attachment';
        $this->Mail->isHTML(true);
        $this->Mail->CharSet = 'UTF-8';

        if (!$this->Mail->addAttachment(
            realpath($this->INCLUDE_DIR . '/examples/images/phpmailer_mini.png'),
            'phpmailer_mini.png'
        )
        ) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);

            return;
        }

        //Make sure that trying to attach a nonexistent file fails
        $this->assertFalse($this->Mail->addAttachment(__FILE__ . md5(microtime()), 'nonexistent_file.txt'));

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test embedded image without a name.
     */
    public function testHTMLStringEmbedNoName()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->Subject .= ': HTML + unnamed embedded image';
        $this->Mail->isHTML(true);

        if (!$this->Mail->addStringEmbeddedImage(
            file_get_contents(realpath($this->INCLUDE_DIR . '/examples/images/phpmailer_mini.png')),
            hash('sha256', 'phpmailer_mini.png') . '@phpmailer.0',
            '', //Intentionally empty name
            'base64',
            '', //Intentionally empty MIME type
            'inline'
        )) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);

            return;
        }

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple HTML and multiple attachment test.
     */
    public function testHTMLMultiAttachment()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->Subject .= ': HTML + multiple Attachment';
        $this->Mail->isHTML(true);

        if (!$this->Mail->addAttachment(
            realpath($this->INCLUDE_DIR . '/examples/images/phpmailer_mini.png'),
            'phpmailer_mini.png'
        )
        ) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);

            return;
        }

        if (!$this->Mail->addAttachment(
            realpath($this->INCLUDE_DIR . '/examples/images/phpmailer.png'),
            'phpmailer.png'
        )
        ) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);

            return;
        }

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * An embedded attachment test.
     */
    public function testEmbeddedImage()
    {
        $this->Mail->Body = 'Embedded Image: <img alt="phpmailer" src="' .
            'cid:my-attach">' .
            'Here is an image!';
        $this->Mail->Subject .= ': Embedded Image';
        $this->Mail->isHTML(true);

        if (!$this->Mail->addEmbeddedImage(
            realpath($this->INCLUDE_DIR . '/examples/images/phpmailer.png'),
            'my-attach',
            'phpmailer.png',
            'base64',
            'image/png'
        )
        ) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);

            return;
        }

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        //For code coverage
        $this->Mail->addEmbeddedImage('thisfiledoesntexist', 'xyz'); //Non-existent file
        $this->Mail->addEmbeddedImage(__FILE__, '123'); //Missing name
    }

    /**
     * An embedded attachment test.
     */
    public function testMultiEmbeddedImage()
    {
        $this->Mail->Body = 'Embedded Image: <img alt="phpmailer" src="' .
            'cid:my-attach">' .
            'Here is an image!</a>';
        $this->Mail->Subject .= ': Embedded Image + Attachment';
        $this->Mail->isHTML(true);

        if (!$this->Mail->addEmbeddedImage(
            realpath($this->INCLUDE_DIR . '/examples/images/phpmailer.png'),
            'my-attach',
            'phpmailer.png',
            'base64',
            'image/png'
        )
        ) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);

            return;
        }

        if (!$this->Mail->addAttachment(__FILE__, 'test.txt')) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);

            return;
        }

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple multipart/alternative test.
     */
    public function testAltBody()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->AltBody = 'Here is the plain text body of this message. ' .
            'It should be quite a few lines. It should be wrapped at ' .
            '40 characters.  Make sure that it is.';
        $this->Mail->WordWrap = 40;
        $this->addNote('This is a multipart/alternative email');
        $this->Mail->Subject .= ': AltBody + Word Wrap';

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Simple HTML and attachment test.
     */
    public function testAltBodyAttachment()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->AltBody = 'This is the text part of the email.';
        $this->Mail->Subject .= ': AltBody + Attachment';
        $this->Mail->isHTML(true);

        if (!$this->Mail->addAttachment(__FILE__, 'test_attach.txt')) {
            $this->assertTrue(false, $this->Mail->ErrorInfo);

            return;
        }

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test sending multiple messages with separate connections.
     */
    public function testMultipleSend()
    {
        $this->Mail->Body = 'Sending two messages without keepalive';
        $this->buildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ': SMTP 1';
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        $this->Mail->Subject = $subject . ': SMTP 2';
        $this->Mail->Sender = 'blah@example.com';
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test sending using SendMail.
     */
    public function testSendmailSend()
    {
        $this->Mail->Body = 'Sending via sendmail';
        $this->buildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ': sendmail';
        $this->Mail->isSendmail();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test sending using Qmail.
     */
    public function testQmailSend()
    {
        //Only run if we have qmail installed
        if (file_exists('/var/qmail/bin/qmail-inject')) {
            $this->Mail->Body = 'Sending via qmail';
            $this->buildBody();
            $subject = $this->Mail->Subject;

            $this->Mail->Subject = $subject . ': qmail';
            $this->Mail->isQmail();
            $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        } else {
            $this->markTestSkipped('Qmail is not installed');
        }
    }

    /**
     * Test sending using PHP mail() function.
     */
    public function testMailSend()
    {
        $sendmail = ini_get('sendmail_path');
        //No path in sendmail_path
        if (strpos($sendmail, '/') === false) {
            ini_set('sendmail_path', '/usr/sbin/sendmail -t -i ');
        }
        $this->Mail->Body = 'Sending via mail()';
        $this->buildBody();

        $this->Mail->Subject = $this->Mail->Subject . ': mail()';
        $this->Mail->isMail();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $msg = $this->Mail->getSentMIMEMessage();
        $this->assertNotContains("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
    }

    /**
     * Test sending an empty body.
     */
    public function testEmptyBody()
    {
        $this->buildBody();
        $this->Mail->Body = '';
        $this->Mail->Subject = $this->Mail->Subject . ': Empty Body';
        $this->Mail->isMail();
        $this->Mail->AllowEmpty = true;
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $this->Mail->AllowEmpty = false;
        $this->assertFalse($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test constructing a multipart message that contains lines that are too long for RFC compliance.
     */
    public function testLongBody()
    {
        $oklen = str_repeat(str_repeat('0', PHPMailer::MAX_LINE_LENGTH) . PHPMailer::getLE(), 2);
        //Use +2 to ensure line length is over limit - LE may only be 1 char
        $badlen = str_repeat(str_repeat('1', PHPMailer::MAX_LINE_LENGTH + 2) . PHPMailer::getLE(), 2);

        $this->Mail->Body = 'This message contains lines that are too long.' .
            PHPMailer::getLE() . $oklen . $badlen . $oklen;
        $this->assertTrue(
            PHPMailer::hasLineLongerThanMax($this->Mail->Body),
            'Test content does not contain long lines!'
        );
        $this->Mail->isHTML();
        $this->buildBody();
        $this->Mail->AltBody = $this->Mail->Body;
        $this->Mail->Encoding = '8bit';
        $this->Mail->preSend();
        $message = $this->Mail->getSentMIMEMessage();
        $this->assertFalse(
            PHPMailer::hasLineLongerThanMax($message),
            'Long line not corrected (Max: ' . (PHPMailer::MAX_LINE_LENGTH + strlen(PHPMailer::getLE())) . ' chars)'
        );
        $this->assertContains(
            'Content-Transfer-Encoding: quoted-printable',
            $message,
            'Long line did not cause transfer encoding switch.'
        );
    }

    /**
     * Test constructing a message that does NOT contain lines that are too long for RFC compliance.
     */
    public function testShortBody()
    {
        $oklen = str_repeat(str_repeat('0', PHPMailer::MAX_LINE_LENGTH) . PHPMailer::getLE(), 10);

        $this->Mail->Body = 'This message does not contain lines that are too long.' .
            PHPMailer::getLE() . $oklen;
        $this->assertFalse(
            PHPMailer::hasLineLongerThanMax($this->Mail->Body),
            'Test content contains long lines!'
        );
        $this->buildBody();
        $this->Mail->Encoding = '8bit';
        $this->Mail->preSend();
        $message = $this->Mail->getSentMIMEMessage();
        $this->assertFalse(PHPMailer::hasLineLongerThanMax($message), 'Long line not corrected.');
        $this->assertNotContains(
            'Content-Transfer-Encoding: quoted-printable',
            $message,
            'Short line caused transfer encoding switch.'
        );
    }

    /**
     * Test keepalive (sending multiple messages in a single connection).
     */
    public function testSmtpKeepAlive()
    {
        $this->Mail->Body = 'SMTP keep-alive test.';
        $this->buildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->SMTPKeepAlive = true;
        $this->Mail->Subject = $subject . ': SMTP keep-alive 1';
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        $this->Mail->Subject = $subject . ': SMTP keep-alive 2';
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $this->Mail->smtpClose();
    }

    /**
     * Test this denial of service attack.
     *
     * @see http://www.cybsec.com/vuln/PHPMailer-DOS.pdf
     */
    public function testDenialOfServiceAttack()
    {
        $this->Mail->Body = 'This should no longer cause a denial of service.';
        $this->buildBody();

        $this->Mail->Subject = substr(str_repeat('0123456789', 100), 0, 998);
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Tests this denial of service attack.
     *
     * @see https://sourceforge.net/p/phpmailer/bugs/383/
     * According to the ticket, this should get stuck in a loop, though I can't make it happen.
     */
    public function testDenialOfServiceAttack2()
    {
        //Encoding name longer than 68 chars
        $this->Mail->Encoding = '1234567890123456789012345678901234567890123456789012345678901234567890';
        //Call wrapText with a zero length value
        $this->Mail->wrapText(str_repeat('This should no longer cause a denial of service. ', 30), 0);
    }

    /**
     * Test error handling.
     */
    public function testError()
    {
        $this->Mail->Subject .= ': Error handling test - this should be sent ok';
        $this->buildBody();
        $this->Mail->clearAllRecipients(); // no addresses should cause an error
        $this->assertTrue($this->Mail->isError() == false, 'Error found');
        $this->assertTrue($this->Mail->send() == false, 'send succeeded');
        $this->assertTrue($this->Mail->isError(), 'No error found');
        $this->assertEquals('You must provide at least one recipient email address.', $this->Mail->ErrorInfo);
        $this->Mail->addAddress($_REQUEST['mail_to']);
        $this->assertTrue($this->Mail->send(), 'send failed');
    }

    /**
     * Test addressing.
     */
    public function testAddressing()
    {
        $this->assertFalse($this->Mail->addAddress(''), 'Empty address accepted');
        $this->assertFalse($this->Mail->addAddress('', 'Nobody'), 'Empty address with name accepted');
        $this->assertFalse($this->Mail->addAddress('a@example..com'), 'Invalid address accepted');
        $this->assertTrue($this->Mail->addAddress('a@example.com'), 'Addressing failed');
        $this->assertFalse($this->Mail->addAddress('a@example.com'), 'Duplicate addressing failed');
        $this->assertTrue($this->Mail->addCC('b@example.com'), 'CC addressing failed');
        $this->assertFalse($this->Mail->addCC('b@example.com'), 'CC duplicate addressing failed');
        $this->assertFalse($this->Mail->addCC('a@example.com'), 'CC duplicate addressing failed (2)');
        $this->assertTrue($this->Mail->addBCC('c@example.com'), 'BCC addressing failed');
        $this->assertFalse($this->Mail->addBCC('c@example.com'), 'BCC duplicate addressing failed');
        $this->assertFalse($this->Mail->addBCC('a@example.com'), 'BCC duplicate addressing failed (2)');
        $this->assertTrue($this->Mail->addReplyTo('a@example.com'), 'Replyto Addressing failed');
        $this->assertFalse($this->Mail->addReplyTo('a@example..com'), 'Invalid Replyto address accepted');
        $this->assertTrue($this->Mail->setFrom('a@example.com', 'some name'), 'setFrom failed');
        $this->assertFalse($this->Mail->setFrom('a@example.com.', 'some name'), 'setFrom accepted invalid address');
        $this->Mail->Sender = '';
        $this->Mail->setFrom('a@example.com', 'some name', true);
        $this->assertEquals($this->Mail->Sender, 'a@example.com', 'setFrom failed to set sender');
        $this->Mail->Sender = '';
        $this->Mail->setFrom('a@example.com', 'some name', false);
        $this->assertEquals($this->Mail->Sender, '', 'setFrom should not have set sender');
        $this->Mail->clearCCs();
        $this->Mail->clearBCCs();
        $this->Mail->clearReplyTos();
    }

    /**
     * Test RFC822 address splitting.
     */
    public function testAddressSplitting()
    {
        //Test built-in address parser
        $this->assertCount(
            2,
            PHPMailer::parseAddresses(
                'Joe User <joe@example.com>, Jill User <jill@example.net>'
            ),
            'Failed to recognise address list (IMAP parser)'
        );
        $this->assertEquals(
            [
                ['name' => 'Joe User', 'address' => 'joe@example.com'],
                ['name' => 'Jill User', 'address' => 'jill@example.net'],
                ['name' => '', 'address' => 'frank@example.com'],
            ],
            $this->Mail->parseAddresses(
                'Joe User <joe@example.com>,'
                . 'Jill User <jill@example.net>,'
                . 'frank@example.com,'
            ),
            'Parsed addresses'
        );
        //Test simple address parser
        $this->assertCount(
            2,
            PHPMailer::parseAddresses(
                'Joe User <joe@example.com>, Jill User <jill@example.net>',
                false
            ),
            'Failed to recognise address list'
        );
        //Test single address
        $this->assertNotEmpty(
            PHPMailer::parseAddresses(
                'Joe User <joe@example.com>',
                false
            ),
            'Failed to recognise single address'
        );
        //Test quoted name IMAP
        $this->assertNotEmpty(
            PHPMailer::parseAddresses(
                'Tim "The Book" O\'Reilly <foo@example.com>'
            ),
            'Failed to recognise quoted name (IMAP)'
        );
        //Test quoted name
        $this->assertNotEmpty(
            PHPMailer::parseAddresses(
                'Tim "The Book" O\'Reilly <foo@example.com>',
                false
            ),
            'Failed to recognise quoted name'
        );
        //Test single address IMAP
        $this->assertNotEmpty(
            PHPMailer::parseAddresses(
                'Joe User <joe@example.com>'
            ),
            'Failed to recognise single address (IMAP)'
        );
        //Test unnamed address
        $this->assertNotEmpty(
            PHPMailer::parseAddresses(
                'joe@example.com',
                false
            ),
            'Failed to recognise unnamed address'
        );
        //Test unnamed address IMAP
        $this->assertNotEmpty(
            PHPMailer::parseAddresses(
                'joe@example.com'
            ),
            'Failed to recognise unnamed address (IMAP)'
        );
        //Test invalid addresses
        $this->assertEmpty(
            PHPMailer::parseAddresses(
                'Joe User <joe@example.com.>, Jill User <jill.@example.net>'
            ),
            'Failed to recognise invalid addresses (IMAP)'
        );
        //Test invalid addresses
        $this->assertEmpty(
            PHPMailer::parseAddresses(
                'Joe User <joe@example.com.>, Jill User <jill.@example.net>',
                false
            ),
            'Failed to recognise invalid addresses'
        );
    }

    /**
     * Test address escaping.
     */
    public function testAddressEscaping()
    {
        $this->Mail->Subject .= ': Address escaping';
        $this->Mail->clearAddresses();
        $this->Mail->addAddress('foo@example.com', 'Tim "The Book" O\'Reilly');
        $this->Mail->Body = 'Test correct escaping of quotes in addresses.';
        $this->buildBody();
        $this->Mail->preSend();
        $b = $this->Mail->getSentMIMEMessage();
        $this->assertTrue((strpos($b, 'To: "Tim \"The Book\" O\'Reilly" <foo@example.com>') !== false));
    }

    /**
     * Test MIME structure assembly.
     */
    public function testMIMEStructure()
    {
        $this->Mail->Subject .= ': MIME structure';
        $this->Mail->Body = '<h3>MIME structure test.</h3>';
        $this->Mail->AltBody = 'MIME structure test.';
        $this->buildBody();
        $this->Mail->preSend();
        $this->assertRegExp(
            "/Content-Transfer-Encoding: 8bit\r\n\r\n" .
            'This is a multi-part message in MIME format./',
            $this->Mail->getSentMIMEMessage(),
            'MIME structure broken'
        );
    }

    /**
     * Test BCC-only addressing.
     */
    public function testBCCAddressing()
    {
        $this->Mail->Subject .= ': BCC-only addressing';
        $this->buildBody();
        $this->Mail->clearAllRecipients();
        $this->assertTrue($this->Mail->addBCC('a@example.com'), 'BCC addressing failed');
        $this->assertTrue($this->Mail->send(), 'send failed');
    }

    /**
     * Encoding and charset tests.
     */
    public function testEncodings()
    {
        $this->Mail->CharSet = 'iso-8859-1';
        $this->assertEquals(
            '=A1Hola!_Se=F1or!',
            $this->Mail->encodeQ("\xa1Hola! Se\xf1or!", 'text'),
            'Q Encoding (text) failed'
        );
        $this->assertEquals(
            '=A1Hola!_Se=F1or!',
            $this->Mail->encodeQ("\xa1Hola! Se\xf1or!", 'comment'),
            'Q Encoding (comment) failed'
        );
        $this->assertEquals(
            '=A1Hola!_Se=F1or!',
            $this->Mail->encodeQ("\xa1Hola! Se\xf1or!", 'phrase'),
            'Q Encoding (phrase) failed'
        );
        $this->Mail->CharSet = 'UTF-8';
        $this->assertEquals(
            '=C2=A1Hola!_Se=C3=B1or!',
            $this->Mail->encodeQ("\xc2\xa1Hola! Se\xc3\xb1or!", 'text'),
            'Q Encoding (text) failed'
        );
        //Strings containing '=' are a special case
        $this->assertEquals(
            'Nov=C3=A1=3D',
            $this->Mail->encodeQ("Nov\xc3\xa1=", 'text'),
            'Q Encoding (text) failed 2'
        );

        $this->assertEquals($this->Mail->encodeString('hello', 'binary'), 'hello', 'Binary encoding changed input');
        $this->Mail->ErrorInfo = '';
        $this->Mail->encodeString('hello', 'asdfghjkl');
        $this->assertNotEmpty($this->Mail->ErrorInfo, 'Invalid encoding not detected');
    }

    /**
     * Test base-64 encoding.
     */
    public function testBase64()
    {
        $this->Mail->Subject .= ': Base-64 encoding';
        $this->Mail->Encoding = 'base64';
        $this->buildBody();
        $this->assertTrue($this->Mail->send(), 'Base64 encoding failed');
    }

    /**
     * S/MIME Signing tests (self-signed).
     *
     * @requires extension openssl
     */
    public function testSigning()
    {
        $this->Mail->Subject .= ': S/MIME signing';
        $this->Mail->Body = 'This message is S/MIME signed.';
        $this->buildBody();

        $dn = [
            'countryName' => 'UK',
            'stateOrProvinceName' => 'Here',
            'localityName' => 'There',
            'organizationName' => 'PHP',
            'organizationalUnitName' => 'PHPMailer',
            'commonName' => 'PHPMailer Test',
            'emailAddress' => 'phpmailer@example.com',
        ];
        $keyconfig = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        $password = 'password';
        $certfile = 'certfile.pem';
        $keyfile = 'keyfile.pem';

        //Make a new key pair
        $pk = openssl_pkey_new($keyconfig);
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

        $this->Mail->sign(
            $certfile,
            $keyfile,
            $password
        );
        $this->assertTrue($this->Mail->send(), 'S/MIME signing failed');

        $msg = $this->Mail->getSentMIMEMessage();
        $this->assertNotContains("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
        unlink($certfile);
        unlink($keyfile);
    }

    /**
     * S/MIME Signing tests using a CA chain cert.
     * To test that a generated message is signed correctly, save the message in a file called `signed.eml`
     * and use openssl along with the certs generated by this script:
     * `openssl smime -verify -in signed.eml -signer certfile.pem -CAfile cacertfile.pem`.
     *
     * @requires extension openssl
     */
    public function testSigningWithCA()
    {
        $this->Mail->Subject .= ': S/MIME signing with CA';
        $this->Mail->Body = 'This message is S/MIME signed with an extra CA cert.';
        $this->buildBody();

        $certprops = [
            'countryName' => 'UK',
            'stateOrProvinceName' => 'Here',
            'localityName' => 'There',
            'organizationName' => 'PHP',
            'organizationalUnitName' => 'PHPMailer',
            'commonName' => 'PHPMailer Test',
            'emailAddress' => 'phpmailer@example.com',
        ];
        $cacertprops = [
            'countryName' => 'UK',
            'stateOrProvinceName' => 'Here',
            'localityName' => 'There',
            'organizationName' => 'PHP',
            'organizationalUnitName' => 'PHPMailer CA',
            'commonName' => 'PHPMailer Test CA',
            'emailAddress' => 'phpmailer@example.com',
        ];
        $keyconfig = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];
        $password = 'password';
        $cacertfile = 'cacertfile.pem';
        $cakeyfile = 'cakeyfile.pem';
        $certfile = 'certfile.pem';
        $keyfile = 'keyfile.pem';

        //Create a CA cert
        //Make a new key pair
        $capk = openssl_pkey_new($keyconfig);
        //Create a certificate signing request
        $csr = openssl_csr_new($cacertprops, $capk);
        //Create a self-signed cert
        $cert = openssl_csr_sign($csr, null, $capk, 1);
        //Save the CA cert
        openssl_x509_export($cert, $certout);
        file_put_contents($cacertfile, $certout);
        //Save the CA key
        openssl_pkey_export($capk, $pkeyout, $password);
        file_put_contents($cakeyfile, $pkeyout);

        //Create a cert signed by our CA
        //Make a new key pair
        $pk = openssl_pkey_new($keyconfig);
        //Create a certificate signing request
        $csr = openssl_csr_new($certprops, $pk);
        //Create a self-signed cert
        $cacert = file_get_contents($cacertfile);
        $cert = openssl_csr_sign($csr, $cacert, $capk, 1);
        //Save the cert
        openssl_x509_export($cert, $certout);
        file_put_contents($certfile, $certout);
        //Save the key
        openssl_pkey_export($pk, $pkeyout, $password);
        file_put_contents($keyfile, $pkeyout);

        $this->Mail->sign(
            $certfile,
            $keyfile,
            $password,
            $cacertfile
        );
        $this->assertTrue($this->Mail->send(), 'S/MIME signing with CA failed');
        unlink($cacertfile);
        unlink($cakeyfile);
        unlink($certfile);
        unlink($keyfile);
    }

    /**
     * DKIM body canonicalization tests.
     *
     * @see https://tools.ietf.org/html/rfc6376#section-3.4.4
     */
    public function testDKIMBodyCanonicalization()
    {
        //Example from https://tools.ietf.org/html/rfc6376#section-3.4.5
        $prebody = " C \r\nD \t E\r\n\r\n\r\n";
        $postbody = " C \r\nD \t E\r\n";
        $this->assertEquals($this->Mail->DKIM_BodyC(''), "\r\n", 'DKIM empty body canonicalization incorrect');
        $this->assertEquals(
            'frcCV1k9oG9oKj3dpUqdJg1PxRT2RSN/XKdLCPjaYaY=',
            base64_encode(hash('sha256', $this->Mail->DKIM_BodyC(''), true)),
            'DKIM canonicalized empty body hash mismatch'
        );
        $this->assertEquals($this->Mail->DKIM_BodyC($prebody), $postbody, 'DKIM body canonicalization incorrect');
    }

    /**
     * DKIM header canonicalization tests.
     *
     * @see https://tools.ietf.org/html/rfc6376#section-3.4.2
     */
    public function testDKIMHeaderCanonicalization()
    {
        //Example from https://tools.ietf.org/html/rfc6376#section-3.4.5
        $preheaders = "A: X\r\nB : Y\t\r\n\tZ  \r\n";
        $postheaders = "a:X\r\nb:Y Z\r\n";
        $this->assertEquals(
            $postheaders,
            $this->Mail->DKIM_HeaderC($preheaders),
            'DKIM header canonicalization incorrect'
        );
    }

    /**
     * DKIM Signing tests.
     *
     * @requires extension openssl
     */
    public function testDKIM()
    {
        $this->Mail->Subject .= ': DKIM signing';
        $this->Mail->Body = 'This message is DKIM signed.';
        $this->buildBody();
        $privatekeyfile = 'dkim_private.pem';
        //Make a new key pair
        //(2048 bits is the recommended minimum key length -
        //gmail won't accept less than 1024 bits)
        $pk = openssl_pkey_new(
            [
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]
        );
        openssl_pkey_export_to_file($pk, $privatekeyfile);
        $this->Mail->DKIM_domain = 'example.com';
        $this->Mail->DKIM_private = $privatekeyfile;
        $this->Mail->DKIM_selector = 'phpmailer';
        $this->Mail->DKIM_passphrase = ''; //key is not encrypted
        $this->assertTrue($this->Mail->send(), 'DKIM signed mail failed');
        $this->Mail->isMail();
        $this->assertTrue($this->Mail->send(), 'DKIM signed mail via mail() failed');
        unlink($privatekeyfile);
    }

    /**
     * Test line break reformatting.
     */
    public function testLineBreaks()
    {
        //May have been altered by earlier tests, can interfere with line break format
        $this->Mail->isSMTP();
        $this->Mail->preSend();
        $unixsrc = "hello\nWorld\nAgain\n";
        $macsrc = "hello\rWorld\rAgain\r";
        $windowssrc = "hello\r\nWorld\r\nAgain\r\n";
        $mixedsrc = "hello\nWorld\rAgain\r\n";
        $target = "hello\r\nWorld\r\nAgain\r\n";
        $this->assertEquals($target, PHPMailer::normalizeBreaks($unixsrc), 'UNIX break reformatting failed');
        $this->assertEquals($target, PHPMailer::normalizeBreaks($macsrc), 'Mac break reformatting failed');
        $this->assertEquals($target, PHPMailer::normalizeBreaks($windowssrc), 'Windows break reformatting failed');
        $this->assertEquals($target, PHPMailer::normalizeBreaks($mixedsrc), 'Mixed break reformatting failed');

        //To see accurate results when using postfix, set `sendmail_fix_line_endings = never` in main.cf
        $this->Mail->Subject = 'PHPMailer DOS line breaks';
        $this->Mail->Body = "This message\r\ncontains\r\nDOS-format\r\nCRLF line breaks.";
        $this->assertTrue($this->Mail->send());

        $this->Mail->Subject = 'PHPMailer UNIX line breaks';
        $this->Mail->Body = "This message\ncontains\nUNIX-format\nLF line breaks.";
        $this->assertTrue($this->Mail->send());

        $this->Mail->Encoding = 'quoted-printable';
        $this->Mail->Subject = 'PHPMailer DOS line breaks, QP';
        $this->Mail->Body = "This message\r\ncontains\r\nDOS-format\r\nCRLF line breaks.";
        $this->assertTrue($this->Mail->send());

        $this->Mail->Subject = 'PHPMailer UNIX line breaks, QP';
        $this->Mail->Body = "This message\ncontains\nUNIX-format\nLF line breaks.";
        $this->assertTrue($this->Mail->send());
    }

    /**
     * Test line length detection.
     */
    public function testLineLength()
    {
        //May have been altered by earlier tests, can interfere with line break format
        $this->Mail->isSMTP();
        $this->Mail->preSend();
        $oklen = str_repeat(str_repeat('0', PHPMailer::MAX_LINE_LENGTH) . "\r\n", 2);
        $badlen = str_repeat(str_repeat('1', PHPMailer::MAX_LINE_LENGTH + 1) . "\r\n", 2);
        $this->assertTrue(PHPMailer::hasLineLongerThanMax($badlen), 'Long line not detected (only)');
        $this->assertTrue(PHPMailer::hasLineLongerThanMax($oklen . $badlen), 'Long line not detected (first)');
        $this->assertTrue(PHPMailer::hasLineLongerThanMax($badlen . $oklen), 'Long line not detected (last)');
        $this->assertTrue(
            PHPMailer::hasLineLongerThanMax($oklen . $badlen . $oklen),
            'Long line not detected (middle)'
        );
        $this->assertFalse(PHPMailer::hasLineLongerThanMax($oklen), 'Long line false positive');
        $this->Mail->isHTML(false);
        $this->Mail->Subject .= ': Line length test';
        $this->Mail->CharSet = 'UTF-8';
        $this->Mail->Encoding = '8bit';
        $this->Mail->Body = $oklen . $badlen . $oklen . $badlen;
        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $this->assertEquals('quoted-printable', $this->Mail->Encoding, 'Long line did not override transfer encoding');
    }

    /**
     * Test setting and retrieving message ID.
     */
    public function testMessageID()
    {
        $this->Mail->Body = 'Test message ID.';
        $id = hash('sha256', 12345);
        $this->Mail->MessageID = $id;
        $this->buildBody();
        $this->Mail->preSend();
        $lastid = $this->Mail->getLastMessageID();
        $this->assertNotEquals($lastid, $id, 'Invalid Message ID allowed');
        $id = '<' . hash('sha256', 12345) . '@example.com>';
        $this->Mail->MessageID = $id;
        $this->buildBody();
        $this->Mail->preSend();
        $lastid = $this->Mail->getLastMessageID();
        $this->assertEquals($lastid, $id, 'Custom Message ID not used');
        $this->Mail->MessageID = '';
        $this->buildBody();
        $this->Mail->preSend();
        $lastid = $this->Mail->getLastMessageID();
        $this->assertRegExp('/^<.*@.*>$/', $lastid, 'Invalid default Message ID');
    }

    /**
     * Miscellaneous calls to improve test coverage and some small tests.
     */
    public function testMiscellaneous()
    {
        $this->assertEquals('application/pdf', PHPMailer::_mime_types('pdf'), 'MIME TYPE lookup failed');
        $this->Mail->addCustomHeader('SomeHeader: Some Value');
        $this->Mail->clearCustomHeaders();
        $this->Mail->clearAttachments();
        $this->Mail->isHTML(false);
        $this->Mail->isSMTP();
        $this->Mail->isMail();
        $this->Mail->isSendmail();
        $this->Mail->isQmail();
        $this->Mail->setLanguage('fr');
        $this->Mail->Sender = '';
        $this->Mail->createHeader();
        $this->assertFalse($this->Mail->set('x', 'y'), 'Invalid property set succeeded');
        $this->assertTrue($this->Mail->set('Timeout', 11), 'Valid property set failed');
        $this->assertTrue($this->Mail->set('AllowEmpty', null), 'Null property set failed');
        $this->assertTrue($this->Mail->set('AllowEmpty', false), 'Valid property set of null property failed');
        //Test pathinfo
        $a = '/mnt/files/飛兒樂 團光茫.mp3';
        $q = PHPMailer::mb_pathinfo($a);
        $this->assertEquals($q['dirname'], '/mnt/files', 'UNIX dirname not matched');
        $this->assertEquals($q['basename'], '飛兒樂 團光茫.mp3', 'UNIX basename not matched');
        $this->assertEquals($q['extension'], 'mp3', 'UNIX extension not matched');
        $this->assertEquals($q['filename'], '飛兒樂 團光茫', 'UNIX filename not matched');
        $this->assertEquals(
            PHPMailer::mb_pathinfo($a, PATHINFO_DIRNAME),
            '/mnt/files',
            'Dirname path element not matched'
        );
        $this->assertEquals(
            PHPMailer::mb_pathinfo($a, PATHINFO_BASENAME),
            '飛兒樂 團光茫.mp3',
            'Basename path element not matched'
        );
        $this->assertEquals(PHPMailer::mb_pathinfo($a, 'filename'), '飛兒樂 團光茫', 'Filename path element not matched');
        $a = 'c:\mnt\files\飛兒樂 團光茫.mp3';
        $q = PHPMailer::mb_pathinfo($a);
        $this->assertEquals($q['dirname'], 'c:\mnt\files', 'Windows dirname not matched');
        $this->assertEquals($q['basename'], '飛兒樂 團光茫.mp3', 'Windows basename not matched');
        $this->assertEquals($q['extension'], 'mp3', 'Windows extension not matched');
        $this->assertEquals($q['filename'], '飛兒樂 團光茫', 'Windows filename not matched');

        $this->assertEquals(
            PHPMailer::filenameToType('abc.jpg?xyz=1'),
            'image/jpeg',
            'Query string not ignored in filename'
        );
        $this->assertEquals(
            PHPMailer::filenameToType('abc.xyzpdq'),
            'application/octet-stream',
            'Default MIME type not applied to unknown extension'
        );

        //Line break normalization
        $eol = PHPMailer::getLE();
        $b1 = "1\r2\r3\r";
        $b2 = "1\n2\n3\n";
        $b3 = "1\r\n2\r3\n";
        $t1 = "1{$eol}2{$eol}3{$eol}";
        $this->assertEquals(PHPMailer::normalizeBreaks($b1), $t1, 'Failed to normalize line breaks (1)');
        $this->assertEquals(PHPMailer::normalizeBreaks($b2), $t1, 'Failed to normalize line breaks (2)');
        $this->assertEquals(PHPMailer::normalizeBreaks($b3), $t1, 'Failed to normalize line breaks (3)');
    }

    public function testBadSMTP()
    {
        $this->Mail->smtpConnect();
        $smtp = $this->Mail->getSMTPInstance();
        $this->assertFalse($smtp->mail("somewhere\nbad"), 'Bad SMTP command containing breaks accepted');
    }

    public function testHostValidation()
    {
        $good = [
            'localhost',
            'example.com',
            'smtp.gmail.com',
            '127.0.0.1',
            trim(str_repeat('a0123456789.', 21), '.'),
            '[::1]',
            '[0:1234:dc0:41:216:3eff:fe67:3e01]',
        ];
        $bad = [
            null,
            123,
            1.5,
            new \stdClass(),
            [],
            '',
            '999.0.0.0',
            '[1234]',
            '[1234:::1]',
            trim(str_repeat('a0123456789.', 22), '.'),
            '0:1234:dc0:41:216:3eff:fe67:3e01',
            '[012q:1234:dc0:41:216:3eff:fe67:3e01]',
        ];
        foreach ($good as $h) {
            $this->assertTrue(PHPMailer::isValidHost($h), 'Good hostname denied: ' . $h);
        }
        foreach ($bad as $h) {
            $this->assertFalse(PHPMailer::isValidHost($h), 'Bad hostname accepted: ' . var_export($h, true));
        }
    }

    /**
     * Tests the Custom header getter.
     */
    public function testCustomHeaderGetter()
    {
        $this->Mail->addCustomHeader('foo', 'bar');
        $this->assertEquals([['foo', 'bar']], $this->Mail->getCustomHeaders());

        $this->Mail->addCustomHeader('foo', 'baz');
        $this->assertEquals(
            [
                ['foo', 'bar'],
                ['foo', 'baz'],
            ],
            $this->Mail->getCustomHeaders()
        );

        $this->Mail->clearCustomHeaders();
        $this->assertEmpty($this->Mail->getCustomHeaders());

        $this->Mail->addCustomHeader('yux');
        $this->assertEquals([['yux']], $this->Mail->getCustomHeaders());

        $this->Mail->addCustomHeader('Content-Type: application/json');
        $this->assertEquals(
            [
                ['yux'],
                ['Content-Type', ' application/json'],
            ],
            $this->Mail->getCustomHeaders()
        );
    }

    /**
     * Tests setting and retrieving ConfirmReadingTo address, also known as "read receipt" address.
     */
    public function testConfirmReadingTo()
    {
        $this->Mail->CharSet = 'utf-8';
        $this->buildBody();

        $this->Mail->ConfirmReadingTo = 'test@example..com';  //Invalid address
        $this->assertFalse($this->Mail->send(), $this->Mail->ErrorInfo);

        $this->Mail->ConfirmReadingTo = ' test@example.com';  //Extra space to trim
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $this->assertEquals(
            'test@example.com',
            $this->Mail->ConfirmReadingTo,
            'Unexpected read receipt address'
        );

        $this->Mail->ConfirmReadingTo = 'test@françois.ch';  //Address with IDN
        if ($this->Mail->idnSupported()) {
            $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
            $this->assertEquals(
                'test@xn--franois-xxa.ch',
                $this->Mail->ConfirmReadingTo,
                'IDN address not converted to punycode'
            );
        } else {
            $this->assertFalse($this->Mail->send(), $this->Mail->ErrorInfo);
        }
    }

    /**
     * Tests CharSet and Unicode -> ASCII conversions for addresses with IDN.
     */
    public function testConvertEncoding()
    {
        if (!$this->Mail->idnSupported()) {
            $this->markTestSkipped('intl and/or mbstring extensions are not available');
        }

        $this->Mail->clearAllRecipients();
        $this->Mail->clearReplyTos();

        // This file is UTF-8 encoded. Create a domain encoded in "iso-8859-1".
        $domain = '@' . mb_convert_encoding('françois.ch', 'ISO-8859-1', 'UTF-8');
        $this->Mail->addAddress('test' . $domain);
        $this->Mail->addCC('test+cc' . $domain);
        $this->Mail->addBCC('test+bcc' . $domain);
        $this->Mail->addReplyTo('test+replyto' . $domain);

        // Queued addresses are not returned by get*Addresses() before send() call.
        $this->assertEmpty($this->Mail->getToAddresses(), 'Bad "to" recipients');
        $this->assertEmpty($this->Mail->getCcAddresses(), 'Bad "cc" recipients');
        $this->assertEmpty($this->Mail->getBccAddresses(), 'Bad "bcc" recipients');
        $this->assertEmpty($this->Mail->getReplyToAddresses(), 'Bad "reply-to" recipients');

        // Clear queued BCC recipient.
        $this->Mail->clearBCCs();

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        // Addresses with IDN are returned by get*Addresses() after send() call.
        $domain = $this->Mail->punyencodeAddress($domain);
        $this->assertEquals(
            [['test' . $domain, '']],
            $this->Mail->getToAddresses(),
            'Bad "to" recipients'
        );
        $this->assertEquals(
            [['test+cc' . $domain, '']],
            $this->Mail->getCcAddresses(),
            'Bad "cc" recipients'
        );
        $this->assertEmpty($this->Mail->getBccAddresses(), 'Bad "bcc" recipients');
        $this->assertEquals(
            ['test+replyto' . $domain => ['test+replyto' . $domain, '']],
            $this->Mail->getReplyToAddresses(),
            'Bad "reply-to" addresses'
        );
    }

    /**
     * Tests removal of duplicate recipients and reply-tos.
     */
    public function testDuplicateIDNRemoved()
    {
        if (!$this->Mail->idnSupported()) {
            $this->markTestSkipped('intl and/or mbstring extensions are not available');
        }

        $this->Mail->clearAllRecipients();
        $this->Mail->clearReplyTos();

        $this->Mail->CharSet = 'utf-8';

        $this->assertTrue($this->Mail->addAddress('test@françois.ch'));
        $this->assertFalse($this->Mail->addAddress('test@françois.ch'));
        $this->assertTrue($this->Mail->addAddress('test@FRANÇOIS.CH'));
        $this->assertFalse($this->Mail->addAddress('test@FRANÇOIS.CH'));
        $this->assertTrue($this->Mail->addAddress('test@xn--franois-xxa.ch'));
        $this->assertFalse($this->Mail->addAddress('test@xn--franois-xxa.ch'));
        $this->assertFalse($this->Mail->addAddress('test@XN--FRANOIS-XXA.CH'));

        $this->assertTrue($this->Mail->addReplyTo('test+replyto@françois.ch'));
        $this->assertFalse($this->Mail->addReplyTo('test+replyto@françois.ch'));
        $this->assertTrue($this->Mail->addReplyTo('test+replyto@FRANÇOIS.CH'));
        $this->assertFalse($this->Mail->addReplyTo('test+replyto@FRANÇOIS.CH'));
        $this->assertTrue($this->Mail->addReplyTo('test+replyto@xn--franois-xxa.ch'));
        $this->assertFalse($this->Mail->addReplyTo('test+replyto@xn--franois-xxa.ch'));
        $this->assertFalse($this->Mail->addReplyTo('test+replyto@XN--FRANOIS-XXA.CH'));

        $this->buildBody();
        $this->assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        // There should be only one "To" address and one "Reply-To" address.
        $this->assertEquals(
            1,
            count($this->Mail->getToAddresses()),
            'Bad count of "to" recipients'
        );
        $this->assertEquals(
            1,
            count($this->Mail->getReplyToAddresses()),
            'Bad count of "reply-to" addresses'
        );
    }

    /**
     * Use a fake POP3 server to test POP-before-SMTP auth with a known-good login.
     *
     * @group pop3
     */
    public function testPopBeforeSmtpGood()
    {
        //Start a fake POP server
        $pid = shell_exec(
            '/usr/bin/nohup ' .
            $this->INCLUDE_DIR .
            '/test/runfakepopserver.sh 1100 >/dev/null 2>/dev/null & printf "%u" $!'
        );
        $this->pids[] = $pid;

        sleep(1);
        //Test a known-good login
        $this->assertTrue(
            POP3::popBeforeSmtp('localhost', 1100, 10, 'user', 'test', $this->Mail->SMTPDebug),
            'POP before SMTP failed'
        );
        //Kill the fake server, don't care if it fails
        @shell_exec('kill -TERM ' . escapeshellarg($pid));
        sleep(2);
    }

    /**
     * Use a fake POP3 server to test POP-before-SMTP auth
     * with a known-bad login.
     *
     * @group pop3
     */
    public function testPopBeforeSmtpBad()
    {
        //Start a fake POP server on a different port
        //so we don't inadvertently connect to the previous instance
        $pid = shell_exec(
            '/usr/bin/nohup ' .
            $this->INCLUDE_DIR .
            '/test/runfakepopserver.sh 1101 >/dev/null 2>/dev/null & printf "%u" $!'
        );
        $this->pids[] = $pid;

        sleep(2);
        //Test a known-bad login
        $this->assertFalse(
            POP3::popBeforeSmtp('localhost', 1101, 10, 'user', 'xxx', $this->Mail->SMTPDebug),
            'POP before SMTP should have failed'
        );
        //Kill the fake server, don't care if it fails
        @shell_exec('kill -TERM ' . escapeshellarg($pid));
        sleep(2);
    }

    /**
     * Test SMTP host connections.
     * This test can take a long time, so run it last.
     *
     * @group slow
     */
    public function testSmtpConnect()
    {
        $this->Mail->SMTPDebug = 4; //Show connection-level errors
        $this->assertTrue($this->Mail->smtpConnect(), 'SMTP single connect failed');
        $this->Mail->smtpClose();
        $this->Mail->Host = 'localhost:12345;10.10.10.10:54321;' . $_REQUEST['mail_host'];
        $this->assertTrue($this->Mail->smtpConnect(), 'SMTP multi-connect failed');
        $this->Mail->smtpClose();
        $this->Mail->Host = '[::1]:' . $this->Mail->Port . ';' . $_REQUEST['mail_host'];
        $this->assertTrue($this->Mail->smtpConnect(), 'SMTP IPv6 literal multi-connect failed');
        $this->Mail->smtpClose();
        //All these hosts are expected to fail
        $this->Mail->Host = 'xyz://bogus:25;tls://[bogus]:25;ssl://localhost:12345;tls://localhost:587;10.10.10.10:54321;localhost:12345;10.10.10.10';
        $this->assertFalse($this->Mail->smtpConnect(), 'SMTP bad multi-connect succeeded');
        $this->Mail->smtpClose();
        $this->Mail->Host = ' localhost:12345 ; ' . $_REQUEST['mail_host'] . ' ';
        $this->assertTrue($this->Mail->smtpConnect(), 'SMTP hosts with stray spaces failed');
        $this->Mail->smtpClose();
        $this->Mail->Host = $_REQUEST['mail_host'];
        //Need to pick a harmless option so as not cause problems of its own! socket:bind doesn't work with Travis-CI
        $this->assertTrue(
            $this->Mail->smtpConnect(['ssl' => ['verify_depth' => 10]]),
            'SMTP connect with options failed'
        );
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
