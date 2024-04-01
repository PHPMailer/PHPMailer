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

use PHPMailer\PHPMailer\Exception;
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
        $input           = 'hello';
        $LE              = PHPMailer::getLE();
        $base64_expected = base64_encode($input) . $LE;

        return [
            'Simple string; no explicit encoding (using base64 default)' => [
                'input'    => $input,
                'expected' => $base64_expected,
            ],
            'Simple string; base64 encoding (lowercase)' => [
                'input'    => $input,
                'expected' => $base64_expected,
                'encoding' => PHPMailer::ENCODING_BASE64,
            ],
            'Simple string; base64 encoding (uppercase)' => [
                'input'    => $input,
                'expected' => $base64_expected,
                'encoding' => strtoupper(PHPMailer::ENCODING_BASE64),
            ],
            'Simple string; 7-bit encoding' => [
                'input'    => $input,
                'expected' => $input . $LE,
                'encoding' => PHPMailer::ENCODING_7BIT,
            ],
            'Simple string; 8-bit encoding' => [
                'input'    => $input,
                'expected' => $input . $LE,
                'encoding' => PHPMailer::ENCODING_8BIT,
            ],
            'Simple string; binary encoding' => [
                'input'    => $input,
                'expected' => $input,
                'encoding' => PHPMailer::ENCODING_BINARY,
            ],
            'Simple string; binary encoding (mixed case)' => [
                'input'    => $input,
                'expected' => $input,
                'encoding' => ucfirst(PHPMailer::ENCODING_BINARY),
            ],
            'Simple string; quoted printable encoding' => [
                'input'    => $input,
                'expected' => $input,
                'encoding' => PHPMailer::ENCODING_QUOTED_PRINTABLE,
            ],
            'String with line breaks; 8-bit encoding' => [
                'input'    => "hello\rWorld\r",
                'expected' => "hello{$LE}World{$LE}",
                'encoding' => PHPMailer::ENCODING_8BIT,
            ],
        ];
    }

    /**
     * Test passing an incorrect encoding.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::encodeString
     */
    public function testInvalidEncoding()
    {
        $result = $this->Mail->encodeString('hello', 'asdfghjkl');
        self::assertSame('', $result, 'Invalid encoding should result in an empty string');

        self::assertNotEmpty($this->Mail->ErrorInfo, 'Invalid encoding not detected');
        self::assertTrue($this->Mail->isError(), 'Error count not correctly incremented');
        self::assertSame('Unknown encoding: asdfghjkl', $this->Mail->ErrorInfo, 'Error info not correctly set');
    }

    /**
     * Test passing an incorrect encoding results in an exception being thrown when PHPMailer is
     * instantiated with `$exceptions = true`.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::encodeString
     */
    public function testInvalidEncodingException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unknown encoding: asdfghjkl');

        $mail = new PHPMailer(true);
        $mail->encodeString('hello', 'asdfghjkl');
    }
}
