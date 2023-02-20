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

    public function testConfigureSendmail()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'sendmail://localhost');

        $this->assertEquals($this->Mail->Mailer, 'sendmail');
    }

    public function testConfigureSmtp()
    {
        $configurator = new DSNConfigurator();

        $configurator->configure($this->Mail, 'smtp://localhost');

        $this->assertEquals($this->Mail->Mailer, 'smtp');
    }
}
