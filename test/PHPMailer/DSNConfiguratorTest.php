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
        $this->expectExceptionMessage('Mailformed DSN: "localhost".');

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

        $this->assertEquals($this->Mail->Mailer, 'mail');
    }

    /**
     * Test cofiguring sendmail.
     */
    public function testConfigureSendmail()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'sendmail://localhost');

        $this->assertEquals($this->Mail->Mailer, 'sendmail');
    }

    /**
     * Test cofiguring qmail.
     */
    public function testConfigureQmail()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'qmail://localhost');

        $this->assertEquals($this->Mail->Mailer, 'qmail');
    }

    /**
     * Test cofiguring SMTP without authentication.
     */
    public function testConfigureSmtpWithoutAuthentication()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtp://localhost');

        $this->assertEquals($this->Mail->Mailer, 'smtp');
        $this->assertEquals($this->Mail->Host, 'localhost');
        $this->assertFalse($this->Mail->SMTPAuth);
    }

    /**
     * Test cofiguring SMTP with authentication.
     */
    public function testConfigureSmtpWithAuthentication()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtp://user:pass@remotehost');

        $this->assertEquals($this->Mail->Mailer, 'smtp');
        $this->assertEquals($this->Mail->Host, 'remotehost');

        $this->assertTrue($this->Mail->SMTPAuth);
        $this->assertEquals($this->Mail->Username, 'user');
        $this->assertEquals($this->Mail->Password, 'pass');
    }

    /**
     * Test cofiguring SMTP without port.
     */
    public function testConfigureSmtpWithoutPort()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtp://localhost');

        $this->assertEquals($this->Mail->Mailer, 'smtp');
        $this->assertEquals($this->Mail->Host, 'localhost');
        $this->assertEquals($this->Mail->Port, SMTP::DEFAULT_PORT);
    }

    /**
     * Test cofiguring SMTP with port.
     */
    public function testConfigureSmtpWitPort()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtp://localhost:2525');

        $this->assertEquals($this->Mail->Mailer, 'smtp');
        $this->assertEquals($this->Mail->Host, 'localhost');
        $this->assertEquals($this->Mail->Port, 2525);
    }

    /**
     * Test cofiguring SMTPs without port.
     */
    public function testConfigureSmtpsWithoutPort()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtps://user:pass@remotehost');

        $this->assertEquals($this->Mail->Mailer, 'smtp');
        $this->assertEquals($this->Mail->SMTPSecure, PHPMailer::ENCRYPTION_STARTTLS);

        $this->assertEquals($this->Mail->Host, 'remotehost');
        $this->assertEquals($this->Mail->Port, SMTP::DEFAULT_SECURE_PORT);

        $this->assertTrue($this->Mail->SMTPAuth);
        $this->assertEquals($this->Mail->Username, 'user');
        $this->assertEquals($this->Mail->Password, 'pass');
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

        $configurator->configure($this->Mail, 'sendmail://localhost?Sendmail=/usr/local/bin/sendmail&AllowEmpty=1&WordWrap=78');

        $this->assertEquals($this->Mail->Mailer, 'sendmail');
        $this->assertEquals($this->Mail->Sendmail, '/usr/local/bin/sendmail');
        $this->assertEquals($this->Mail->AllowEmpty, true);
        $this->assertEquals($this->Mail->WordWrap, 78);
    }
}
