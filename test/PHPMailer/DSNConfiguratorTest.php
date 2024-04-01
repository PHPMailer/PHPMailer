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

use PHPMailer\PHPMailer\DSNConfigurator;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\Test\TestCase;

/**
 * Test configuring with DSN.
 *
 * @covers \PHPMailer\PHPMailer\DSNConfigurator
 */
final class DSNConfiguratorTest extends TestCase
{
    /**
     * Test throwing exception if DSN is invalid.
     */
    public function testInvalidDSN()
    {
        $configurator = new DSNConfigurator();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Malformed DSN');

        $configurator->configure($this->Mail, 'localhost');
    }

    /**
     * Test throwing exception if DSN scheme is invalid.
     */
    public function testInvalidScheme()
    {
        $configurator = new DSNConfigurator();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid scheme: "ftp".');

        $configurator->configure($this->Mail, 'ftp://localhost');
    }

    /**
     * Test cofiguring mail.
     */
    public function testConfigureMail()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'mail://localhost');

        self::assertEquals($this->Mail->Mailer, 'mail');
    }

    /**
     * Test cofiguring sendmail.
     */
    public function testConfigureSendmail()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'sendmail://localhost');

        self::assertEquals($this->Mail->Mailer, 'sendmail');
    }

    /**
     * Test cofiguring qmail.
     */
    public function testConfigureQmail()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'qmail://localhost');

        self::assertEquals($this->Mail->Mailer, 'qmail');
    }

    /**
     * Test cofiguring SMTP without authentication.
     */
    public function testConfigureSmtpWithoutAuthentication()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtp://localhost');

        self::assertEquals($this->Mail->Mailer, 'smtp');
        self::assertEquals($this->Mail->Host, 'localhost');
        self::assertFalse($this->Mail->SMTPAuth);
    }

    /**
     * Test cofiguring SMTP with authentication.
     */
    public function testConfigureSmtpWithAuthentication()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtp://user:pass@remotehost');

        self::assertEquals($this->Mail->Mailer, 'smtp');
        self::assertEquals($this->Mail->Host, 'remotehost');

        self::assertTrue($this->Mail->SMTPAuth);
        self::assertEquals($this->Mail->Username, 'user');
        self::assertEquals($this->Mail->Password, 'pass');
    }

    /**
     * Test cofiguring SMTP without port.
     */
    public function testConfigureSmtpWithoutPort()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtp://localhost');

        self::assertEquals($this->Mail->Mailer, 'smtp');
        self::assertEquals($this->Mail->Host, 'localhost');
        self::assertEquals($this->Mail->Port, SMTP::DEFAULT_PORT);
    }

    /**
     * Test cofiguring SMTP with port.
     */
    public function testConfigureSmtpWitPort()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtp://localhost:2525');

        self::assertEquals($this->Mail->Mailer, 'smtp');
        self::assertEquals($this->Mail->Host, 'localhost');
        self::assertEquals($this->Mail->Port, 2525);
    }

    /**
     * Test cofiguring SMTPs without port.
     */
    public function testConfigureSmtpsWithoutPort()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtps://user:pass@remotehost');

        self::assertEquals($this->Mail->Mailer, 'smtp');
        self::assertEquals($this->Mail->SMTPSecure, PHPMailer::ENCRYPTION_STARTTLS);

        self::assertEquals($this->Mail->Host, 'remotehost');
        self::assertEquals($this->Mail->Port, SMTP::DEFAULT_SECURE_PORT);

        self::assertTrue($this->Mail->SMTPAuth);
        self::assertEquals($this->Mail->Username, 'user');
        self::assertEquals($this->Mail->Password, 'pass');
    }

    /**
     * Test cofiguring SMTPs with port.
     */
    public function testConfigureWithUnknownOption()
    {
        $configurator = new DSNConfigurator();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown option: "UnknownOption".');

        $configurator->configure($this->Mail, 'mail://locahost?UnknownOption=Value');
    }

    /**
     * Test cofiguring options with query sting.
     */
    public function testConfigureWithOptions()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure(
            $this->Mail,
            'sendmail://localhost?Sendmail=/usr/local/bin/sendmail&AllowEmpty=1&WordWrap=78'
        );

        self::assertEquals($this->Mail->Mailer, 'sendmail');
        self::assertEquals($this->Mail->Sendmail, '/usr/local/bin/sendmail');
        self::assertEquals($this->Mail->AllowEmpty, true);
        self::assertEquals($this->Mail->WordWrap, 78);
    }

    /**
     * Test shortcut.
     */
    public function testShortcut()
    {
        $mailer = DSNConfigurator::mailer('smtps://user@gmail.com:secret@smtp.gmail.com?SMTPDebug=3&Timeout=1000');

        self::assertEquals($mailer->Mailer, 'smtp');
        self::assertEquals($mailer->SMTPSecure, PHPMailer::ENCRYPTION_STARTTLS);

        self::assertEquals($mailer->Host, 'smtp.gmail.com');
        self::assertEquals($mailer->Port, SMTP::DEFAULT_SECURE_PORT);

        self::assertTrue($mailer->SMTPAuth);
        self::assertEquals($mailer->Username, 'user@gmail.com');
        self::assertEquals($mailer->Password, 'secret');

        self::assertEquals($mailer->SMTPDebug, 3);
        self::assertEquals($mailer->Timeout, 1000);
    }
}
