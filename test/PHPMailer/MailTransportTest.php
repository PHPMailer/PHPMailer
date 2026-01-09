<?php

/**
 * PHPMailer - PHP email transport unit tests.
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License
 */

namespace PHPMailer\Test\PHPMailer;

use PHPMailer\Test\SendTestCase;

/**
 * Test sending mail using the various available mail transport options.
 */
final class MailTransportTest extends SendTestCase
{
    /** @var string */
    private $originalSendmailFrom = '';

    protected function set_up()
    {
        parent::set_up();

        $from = ini_get('sendmail_from');
        $this->originalSendmailFrom = $from === false ? '' : $from;
    }

    protected function tear_down()
    {
        ini_set('sendmail_from', $this->originalSendmailFrom);
        parent::tear_down();
    }

    /**
     * Test sending using SendMail.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::isSendmail
     */
    public function testSendmailSend()
    {
        $this->Mail->Body = 'Sending via sendmail';
        $this->buildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ': sendmail';
        $this->Mail->isSendmail();

        self::assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test sending using Qmail.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::isQmail
     */
    public function testQmailSend()
    {
        // Only run if we have qmail installed.
        if (file_exists('/var/qmail/bin/qmail-inject') === false) {
            self::markTestSkipped('Qmail is not installed');
        }

        $this->Mail->Body = 'Sending via qmail';
        $this->buildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ': qmail';
        $this->Mail->isQmail();
        self::assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test sending using PHP mail() function.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::isMail
     */
    public function testMailSend()
    {
        $this->Mail->Body = 'Sending via mail()';
        $this->buildBody();
        $this->Mail->Subject = $this->Mail->Subject . ': mail()';
        $this->Mail->clearAddresses();
        $this->Mail->clearCCs();
        $this->Mail->clearBCCs();
        $this->setAddress('testmailsend@example.com', 'totest');
        $this->setAddress('cctestmailsend@example.com', 'cctest', $sType = 'cc');
        $this->setAddress('bcctestmailsend@example.com', 'bcctest', $sType = 'bcc');

        self::assertContains('testmailsend@example.com', $this->Mail->getToAddresses()[0], 'To address not found');
        self::assertContains('cctestmailsend@example.com', $this->Mail->getCcAddresses()[0], 'CC address not found');
        self::assertContains('bcctestmailsend@example.com', $this->Mail->getBccAddresses()[0], 'BCC address not found');

        self::assertTrue(
            $this->Mail->getAllRecipientAddresses()['testmailsend@example.com'],
            'To address not in recipient addresses'
        );
        self::assertTrue(
            $this->Mail->getAllRecipientAddresses()['cctestmailsend@example.com'],
            'CC address not in recipient addresses'
        );
        self::assertTrue(
            $this->Mail->getAllRecipientAddresses()['bcctestmailsend@example.com'],
            'BCC address not in recipient addresses'
        );

        $this->Mail->createHeader();
        $this->Mail->isMail();
        self::assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);

        $msg = $this->Mail->getSentMIMEMessage();
        self::assertStringNotContainsString("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
    }

    /**
     * Test sending using PHP mail() function with Sender address
     * and explicit sendmail_from ini set.
     * Test running required with:
     * php -d sendmail_path="/usr/sbin/sendmail -t -i -frpath@example.org" ./vendor/bin/phpunit
     *
     * @group sendmailparams
     * @covers \PHPMailer\PHPMailer\PHPMailer::isMail
     */
    public function testMailSendWithSendmailParams()
    {
        $sender = 'rpath@example.org';

        if (strpos(ini_get('sendmail_path'), $sender) === false) {
            self::markTestSkipped('Custom Sendmail php.ini not available');
        }

        $this->Mail->Body = 'Sending via mail()';
        $this->buildBody();
        $this->Mail->Subject = $this->Mail->Subject . ': mail()';
        $this->Mail->clearAddresses();
        $this->setAddress('testmailsend@example.com', 'totest');

        ini_set('sendmail_from', $sender);
        $this->Mail->createHeader();
        $this->Mail->isMail();

        self::assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test sending using SendMail with Sender address
     * and explicit sendmail_from ini set.
     * Test running required with:
     * php -d sendmail_path="/usr/sbin/sendmail -t -i -frpath@example.org" ./vendor/bin/phpunit
     *
     * @group sendmailparams
     * @covers \PHPMailer\PHPMailer\PHPMailer::isSendmail
     */
    public function testSendmailSendWithSendmailParams()
    {
        $sender = 'rpath@example.org';

        if (strpos(ini_get('sendmail_path'), $sender) === false) {
            self::markTestSkipped('Custom Sendmail php.ini not available');
        }

        $this->Mail->Body = 'Sending via sendmail';
        $this->buildBody();
        $subject = $this->Mail->Subject;

        $this->Mail->Subject = $subject . ': sendmail';
        ini_set('sendmail_from', $sender);
        $this->Mail->isSendmail();

        self::assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Test parsing of sendmail path and with certain parameters.
     *
     * @group sendmailparams
     * @covers \PHPMailer\PHPMailer\PHPMailer::parseSendmailPath
     * @dataProvider sendmailPathProvider
     *
     * @param string $sendmailPath The sendmail path to parse.
     * @param string $expectedCommand The expected command after parsing.
     * @param string  $expectedSender The expected Sender (-f parameter) after parsing.
     */
    public function testParseSendmailPath($sendmailPath, $expectedCommand, $expectedSender)
    {
        $mailer = $this->Mail;

        $parseSendmailPath = \Closure::bind(
            function ($path) {
                return $this->{'parseSendmailPath'}($path);
            },
            $mailer,
            \PHPMailer\PHPMailer\PHPMailer::class
        );
        $command = $parseSendmailPath($sendmailPath);

        self::assertSame($expectedCommand, $command, 'Sendmail command not parsed correctly');
        self::assertSame($expectedSender, $mailer->Sender, 'Sender property not set correctly');
    }

    /**
     * Data provider for testParseSendmailPath.
     *
     * @return array{
     *   0: string, // The sendmail path to parse.
     *   1: string, // The expected command after parsing.
     *   2: string  // The expected Sender (-f parameter) after parsing.
     * }
     */

    public function sendmailPathProvider()
    {
        return [
            'path only' => [
                '/usr/sbin/sendmail',
                '/usr/sbin/sendmail',
                ''
            ],
            'with i and t' => [
                '/usr/sbin/sendmail -i -t',
                '/usr/sbin/sendmail',
                ''
            ],
            'with f concatenated' => [
                '/usr/sbin/sendmail -frpath@example.org -i',
                '/usr/sbin/sendmail',
                'rpath@example.org'
            ],
            'with f separated' => [
                '/usr/sbin/sendmail -f rpath@example.org -t',
                '/usr/sbin/sendmail',
                'rpath@example.org',
            ],
            'with extra flags preserved' => [
                '/opt/sendmail -x -y -fuser@example.org',
                '/opt/sendmail -x -y',
                'user@example.org',
            ],
            "extra flags with values preserved" => [
                '/opt/sendmail -X /path/to/logfile -fuser@example.org',
                '/opt/sendmail -X /path/to/logfile',
                'user@example.org',
            ],
            "extra flags concatenated preserved" => [
                '/opt/sendmail -X/path/to/logfile -t -i',
                '/opt/sendmail -X/path/to/logfile',
                '',
            ],
            "option values with regular parameters" => [
                '/opt/sendmail -oi -t',
                '/opt/sendmail',
                '',
            ],
        ];
    }
}
