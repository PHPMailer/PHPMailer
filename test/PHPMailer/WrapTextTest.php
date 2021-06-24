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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\Test\TestCase;

/**
 * Test word wrapping functionality.
 */
final class WrapTextTest extends TestCase
{

    public function testVeryLongWordInMessage_wrapText_returnsWrappedText()
    {
        $message = 'Lorem ipsumdolorsitametconsetetursadipscingelitrseddiamnonumy';
        $expected = 'Lorem' . PHPMailer::getLE() .
            'ipsumdolorsitametconsetetursadipscingelitrseddiamnonumy' . PHPMailer::getLE();
        $expectedqp = 'Lorem ipsumdolorsitametconsetetursadipscingelitrs=' .
            PHPMailer::getLE() . 'eddiamnonumy' . PHPMailer::getLE();
        $this->assertSame($expectedqp, $this->Mail->wrapText($message, 50, true));
        $this->assertSame($expected, $this->Mail->wrapText($message, 50, false));
    }
}
