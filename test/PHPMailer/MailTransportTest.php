<?php

/**
 * PHPMailer - PHP email transport unit tests.
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace PHPMailer\Test\PHPMailer;

use PHPMailer\Test\TestCase;

/**
 * Test sending mail using the various available mail transport options.
 */
final class MailTransportTest extends TestCase
{

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

        self::assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
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
            self::assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        } else {
            self::markTestSkipped('Qmail is not installed');
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
        $this->Mail->clearAddresses();
        $this->Mail->clearCCs();
        $this->Mail->clearBCCs();
        $this->setAddress('testmailsend@example.com', 'totest');
        $this->setAddress('cctestmailsend@example.com', 'cctest', $sType = 'cc');
        $this->setAddress('bcctestmailsend@example.com', 'bcctest', $sType = 'bcc');
        $this->Mail->addReplyTo('replytotestmailsend@example.com', 'replytotest');
        self::assertContains('testmailsend@example.com', $this->Mail->getToAddresses()[0]);
        self::assertContains('cctestmailsend@example.com', $this->Mail->getCcAddresses()[0]);
        self::assertContains('bcctestmailsend@example.com', $this->Mail->getBccAddresses()[0]);
        self::assertContains(
            'replytotestmailsend@example.com',
            $this->Mail->getReplyToAddresses()['replytotestmailsend@example.com']
        );
        self::assertTrue($this->Mail->getAllRecipientAddresses()['testmailsend@example.com']);
        self::assertTrue($this->Mail->getAllRecipientAddresses()['cctestmailsend@example.com']);
        self::assertTrue($this->Mail->getAllRecipientAddresses()['bcctestmailsend@example.com']);

        $this->Mail->createHeader();
        $this->Mail->isMail();
        self::assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
        $msg = $this->Mail->getSentMIMEMessage();
        self::assertStringNotContainsString("\r\n\r\nMIME-Version:", $msg, 'Incorrect MIME headers');
    }
}
