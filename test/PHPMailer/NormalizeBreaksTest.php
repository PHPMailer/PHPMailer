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
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test normalize line breaks functionality.
 */
final class NormalizeBreaksTest extends TestCase
{

    /**
     * Test line break normalization.
     *
     * @dataProvider dataNormalizeBreaks
     *
     * @param string $input     Input text string.
     * @param string $expected  Expected funtion output.
     * @param string $breaktype Optional. What kind of line break to use.
     */
    public function testNormalizeBreaks($input, $expected, $breaktype = null)
    {
        $result = PHPMailer::normalizeBreaks($input, $breaktype);
        self::assertSame($expected, $result, 'Line break reformatting failed');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataNormalizeBreaks()
    {
        $LE           = PHPMailer::getLE();
        $baseExpected = 'hello' . PHPMailer::CRLF . 'World' . PHPMailer::CRLF . 'Again' . PHPMailer::CRLF;

        return [
            'Text without line breaks' => [
                'input'    => 'hello World!',
                'expected' => 'hello World!',
            ],
            'Unix line breaks' => [
                'input'    => "hello\nWorld\nAgain\n",
                'expected' => $baseExpected,
            ],
            'Mac line breaks' => [
                'input'    => "hello\rWorld\rAgain\r",
                'expected' => $baseExpected,
            ],
            'Windows line breaks' => [
                'input'    => "hello\r\nWorld\r\nAgain\r\n",
                'expected' => $baseExpected,
            ],
            'Mixed line breaks' => [
                'input'    => "hello\nWorld\rAgain\r\n",
                'expected' => $baseExpected,
            ],
            'Mac line breaks, enforce Unix' => [
                'input'     => "1\r2\r3\r",
                'expected'  => "1\n2\n3\n",
                'breaktype' => "\n",
            ],
            'Unix line breaks, enforce Mac' => [
                'input'     => "1\n2\n3\n",
                'expected'  => "1\r2\r3\r",
                'breaktype' => "\r",
            ],
            'Mixed line breaks, enforce preset' => [
                'input'     => "1\r\n2\r3\n",
                'expected'  => "1{$LE}2{$LE}3{$LE}",
                'breaktype' => $LE,
            ],
        ];
    }
}
