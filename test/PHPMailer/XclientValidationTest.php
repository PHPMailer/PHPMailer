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
use PHPMailer\PHPMailer\SMTP;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test XCLIENT attribute handling (GHSA-29v7-qgmc-5mgv).
 *
 * XCLIENT attribute values are xtext-encoded (RFC 1891 & 3461) at send
 * time, so raw values cannot inject additional attributes.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::setSMTPXclientAttribute
 * @covers \PHPMailer\PHPMailer\SMTP::xclient
 * @covers \PHPMailer\PHPMailer\SMTP::xtext
 */
final class XclientValidationTest extends TestCase
{
    /**
     * Test that attribute values are xtext-encoded.
     *
     * @dataProvider dataXtextEncoding
     *
     * @param string $input    Raw attribute value.
     * @param string $expected Expected xtext-encoded output.
     */
    public function testXtextEncoding($input, $expected)
    {
        self::assertSame($expected, SMTP::xtext($input));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public static function dataXtextEncoding()
    {
        return [
            'plain hostname unchanged' => ['client.example.com', 'client.example.com'],
            'IPv4 address unchanged' => ['192.0.2.1', '192.0.2.1'],
            'unavailable sentinel unchanged' => ['[UNAVAILABLE]', '[UNAVAILABLE]'],
            'space encoded' => ['hello world', 'hello+20world'],
            'advisory PoC neutralised' => [
                'innocent HELLO=evil.de ADDR=10.0.0.1 PORT=25',
                'innocent+20HELLO=evil.de+20ADDR=10.0.0.1+20PORT=25',
            ],
            'plus encoded' => ['foo+bar', 'foo+2Bbar'],
            'pre-encoded escape left intact' => ['a+20b', 'a+2B20b'],
            'equals sign unchanged, spaces are the injection vector' => ['a=b', 'a=b'],
            'CR LF encoded' => ["bad\r\nvalue", 'bad+0D+0Avalue'],
            'tab encoded' => ["bad\tvalue", 'bad+09value'],
            'high-bit byte encoded' => ["caf\xE9", 'caf+E9'],
            'UTF-8 multibyte encoded' => ["caf\xC3\xA9", 'caf+C3+A9'],
            'NUL encoded' => ["a\x00b", 'a+00b'],
        ];
    }

    /**
     * Test that any string value can be set and is stored raw.
     */
    public function testSetAttributeStoresRawValue()
    {
        $mail = new PHPMailer();
        //The advisory PoC value is accepted...
        self::assertTrue(
            $mail->setSMTPXclientAttribute('NAME', 'innocent HELLO=evil.de ADDR=10.0.0.1 PORT=25')
        );
        //...and stored raw, so callers can read back what they set.
        self::assertSame(
            ['NAME' => 'innocent HELLO=evil.de ADDR=10.0.0.1 PORT=25'],
            $mail->getSMTPXclientAttributes()
        );

        //Unknown attribute names are still rejected.
        self::assertFalse($mail->setSMTPXclientAttribute('BOGUS', 'value'));
        self::assertFalse($mail->setSMTPXclientAttribute('INVALID', 'value'));

        //Removal via null still works.
        self::assertTrue($mail->setSMTPXclientAttribute('NAME', null));
        self::assertSame([], $mail->getSMTPXclientAttributes());
    }

    /**
     * Test that xclient() rejects bad attribute names before sending.
     */
    public function testXclientRejectsBadAttributeNames()
    {
        $smtp = new SMTP();
        self::assertFalse($smtp->xclient(['BOGUS' => 'value']));
        self::assertStringContainsString(
            'Invalid XCLIENT attribute name',
            $smtp->getError()['error']
        );
    }
}
