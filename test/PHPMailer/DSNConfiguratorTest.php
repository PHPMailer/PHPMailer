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

final class DSNConfiguratorTest extends TestCase
{
    public function testInvalidDSN()
    {
        $configurator = new DSNConfigurator();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Mailformed DSN: "localhost".');

        $configurator->configure($this->Mail, 'localhost');
    }

    public function testInvalidScheme()
    {
        $configurator = new DSNConfigurator();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid scheme: "ftp".');

        $configurator->configure($this->Mail, 'ftp://localhost');
    }

    public function testConfigureMail()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'mail://localhost');

        $this->assertEquals($this->Mail->Mailer, 'mail');
    }

    public function testConfigureSendmail()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'sendmail://localhost');

        $this->assertEquals($this->Mail->Mailer, 'sendmail');
    }

    public function testConfigureQmail()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'qmail://localhost');

        $this->assertEquals($this->Mail->Mailer, 'qmail');
    }

    public function testConfigureSmtpWithoutAuthentication()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtp://localhost');

        $this->assertEquals($this->Mail->Mailer, 'smtp');
        $this->assertEquals($this->Mail->Host, 'localhost');
        $this->assertFalse($this->Mail->SMTPAuth);
    }

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

    public function testConfigureSmtpWithoutPort()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtp://localhost');

        $this->assertEquals($this->Mail->Mailer, 'smtp');
        $this->assertEquals($this->Mail->Host, 'localhost');
        $this->assertEquals($this->Mail->Port, SMTP::DEFAULT_PORT);
    }

    public function testConfigureSmtpWitPort()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtp://localhost:2525');

        $this->assertEquals($this->Mail->Mailer, 'smtp');
        $this->assertEquals($this->Mail->Host, 'localhost');
        $this->assertEquals($this->Mail->Port, 2525);
    }

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
}
