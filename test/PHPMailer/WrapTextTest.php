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

    /**
     * Test wrapping text.
     *
     * @dataProvider dataWrapText
     *
     * @param string $message  Input text string.
     * @param string $expected Expected funtion output.
     * @param bool   $qp_mode  Optional. Whether to run in Quoted-Printable mode. Defaults to `false`.
     * @param int    $length   Optional. Length to wrap at. Defaults to `50`.
     */
    public function testWrapText($message, $expected, $qp_mode = false, $length = 50)
    {
        $this->assertSame($expected, $this->Mail->wrapText($message, $length, $qp_mode));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataWrapText()
    {
        return [
            'very long word in message, qp: false' => [
                'message'  => 'Lorem ipsumdolorsitametconsetetursadipscingelitrseddiamnonumy',
                'expected' => 'Lorem' . PHPMailer::getLE()
                    . 'ipsumdolorsitametconsetetursadipscingelitrseddiamnonumy' . PHPMailer::getLE(),
                'qp_mode'  => false,
            ],
            'very long word in message, qp: true' => [
                'message'  => 'Lorem ipsumdolorsitametconsetetursadipscingelitrseddiamnonumy',
                'expected' => 'Lorem ipsumdolorsitametconsetetursadipscingelitrs=' . PHPMailer::getLE()
                    . 'eddiamnonumy' . PHPMailer::getLE(),
                'qp_mode'  => true,
            ],
        ];
    }
}
