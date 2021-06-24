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
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::wrapText
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
            'empty string' => [
                'message'  => '',
                'expected' => PHPMailer::getLE(),
                'qp_mode'  => false,
            ],
            'line length less than wrap limit, qp: false' => [
                'message'  => 'Lorem ipsum dolor sit amet.',
                'expected' => 'Lorem ipsum dolor sit amet.' . PHPMailer::getLE(),
                'qp_mode'  => false,
            ],
            'line length less than wrap limit, qp: true' => [
                'message'  => 'Lorem ipsum dolor sit amet.',
                'expected' => 'Lorem ipsum dolor sit amet.' . PHPMailer::getLE(),
                'qp_mode'  => true,
            ],
            'message with line ending at end' => [
                'message'  => 'Lorem ipsum dolor' . PHPMailer::CRLF,
                'expected' => 'Lorem ipsum dolor' . PHPMailer::getLE(),
            ],
            'line length more than wrap limit, qp: false' => [
                'message'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
                    . ' Maecenas ultricies nisi justo, eu convallis tortor porttitor a.'
                    . ' Nam ut risus tellus. Vivamus imperdiet dictum nibh, in faucibus nunc pretium ac.',
                'expected' => 'Lorem ipsum dolor sit amet, consectetur adipiscing' . PHPMailer::getLE()
                    . 'elit. Maecenas ultricies nisi justo, eu convallis' . PHPMailer::getLE()
                    . 'tortor porttitor a. Nam ut risus tellus. Vivamus' . PHPMailer::getLE()
                    . 'imperdiet dictum nibh, in faucibus nunc pretium' . PHPMailer::getLE()
                    . 'ac.' . PHPMailer::getLE(),
                'qp_mode'  => false,
            ],
            'line length more than wrap limit, qp: true' => [
                'message'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.'
                    . ' Maecenas ultricies nisi justo, eu convallis tortor porttitor a.'
                    . ' Nam ut risus tellus. Vivamus imperdiet dictum nibh, in faucibus nunc pretium ac.',
                'expected' => 'Lorem ipsum dolor sit amet, consectetur adipiscing =' . PHPMailer::getLE()
                    . 'elit. Maecenas ultricies nisi justo, eu convallis =' . PHPMailer::getLE()
                    . 'tortor porttitor a. Nam ut risus tellus. Vivamus =' . PHPMailer::getLE()
                    . 'imperdiet dictum nibh, in faucibus nunc pretium =' . PHPMailer::getLE()
                    . 'ac.' . PHPMailer::getLE(),
                'qp_mode'  => true,
            ],
            'line length more than wrap limit, message already in qp format, qp: true' => [
                'message'  => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. =' . PHPMailer::CRLF
                    . 'Maecenas ultricies nisi justo, eu convallis tortor porttitor a. =' . PHPMailer::CRLF
                    . 'Nam ut risus tellus. Vivamus imperdiet dictum nibh, in faucibus nunc pretium ac.'
                    . PHPMailer::CRLF,
                'expected' => 'Lorem ipsum dolor sit amet, consectetur adipiscing =' . PHPMailer::getLE()
                    . 'elit. =' . PHPMailer::getLE()
                    . 'Maecenas ultricies nisi justo, eu convallis tortor =' . PHPMailer::getLE()
                    . 'porttitor a. =' . PHPMailer::getLE()
                    . 'Nam ut risus tellus. Vivamus imperdiet dictum =' . PHPMailer::getLE()
                    . 'nibh, in faucibus nunc pretium ac.' . PHPMailer::getLE(),
                'qp_mode'  => true,
            ],
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
            'very long word in message, message already in qp format, qp: true' => [
                'message'  => 'Lorem ipsumdolorsitametconsetetursadipscingelitrseddiam=' . PHPMailer::CRLF
                    . 'nonumy',
                'expected' => 'Lorem =' . PHPMailer::getLE()
                    . 'ipsumdolorsitametconsetetursadipscingelitrseddiam=' . PHPMailer::getLE()
                    . 'nonumy' . PHPMailer::getLE(),
                'qp_mode'  => true,
            ],
        ];
    }
}
