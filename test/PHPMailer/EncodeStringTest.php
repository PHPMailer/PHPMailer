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
 * Test string encoding functionality.
 */
final class EncodeStringTest extends TestCase
{

    /**
     * Encoding and charset tests.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::encodeString
     *
     * @dataProvider dataEncodeString
     *
     * @param string $input    Text to encode.
     * @param string $expected Expected function return value.
     * @param string $encoding Optional. Encoding to use.
     */
    public function testEncodeString($input, $expected, $encoding = null)
    {
        if (isset($encoding)) {
            $result = $this->Mail->encodeString($input, $encoding);
        } else {
            $result = $this->Mail->encodeString($input);
        }

        self::assertSame($expected, $result);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataEncodeString()
    {
        $input = 'hello';
        $LE    = PHPMailer::getLE();

        return [
            'Simple string; no explicit encoding (using base64 default)' => [
                'input'    => $input,
                'expected' => base64_encode($input) . $LE,
            ],
            'Simple string; binary encoding' => [
                'input'    => $input,
                'expected' => $input,
                'encoding' => PHPMailer::ENCODING_BINARY,
            ],
        ];
    }

    /**
     * Test passing an incorrect encoding.
     */
    public function testInvalidEncoding()
    {
        $this->Mail->encodeString('hello', 'asdfghjkl');
        self::assertNotEmpty($this->Mail->ErrorInfo, 'Invalid encoding not detected');
    }
}
