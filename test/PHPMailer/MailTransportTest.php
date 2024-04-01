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
        $sendmail = ini_get('sendmail_path');
        // No path in sendmail_path.
        if (strpos($sendmail, '/') === false) {
            ini_set('sendmail_path', '/usr/sbin/sendmail -t -i ');
        }

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
}
