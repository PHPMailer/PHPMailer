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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\Test\TestCase;

/**
 * @covers \PHPMailer\PHPMailer\PHPMailer::addAddress
 */
final class AddAddressWithIDNTest extends TestCase
{
    public function testGivenIdnAddress_addAddress_returns_true()
    {
        if (file_exists(\PHPMAILER_INCLUDE_DIR . '/test/fakefunctions.php') === false) {
            $this->markTestSkipped('/test/fakefunctions.php file not found');
        }

        include \PHPMAILER_INCLUDE_DIR . '/test/fakefunctions.php';
        $this->assertTrue($this->Mail->addAddress('test@françois.ch'));
    }
}
