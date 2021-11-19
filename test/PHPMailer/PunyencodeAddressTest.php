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
 * Test IDN to ASCII functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::punyencodeAddress
 */
final class PunyencodeAddressTest extends TestCase
{

    /**
     * Test IDN to ASCII form/punycode conversion for an email address.
     *
     * @requires extension mbstring
     * @requires function idn_to_ascii
     *
     * @dataProvider dataPunyencodeAddressConversion
     *
     * @param string $input    Input text string.
     * @param string $charset  The character set.
     * @param string $expected Expected funtion output.
     */
    public function testPunyencodeAddressConversion($input, $charset, $expected)
    {
        $this->Mail->CharSet = $charset;

        $input    = html_entity_decode($input, ENT_COMPAT, $charset);
        $expected = html_entity_decode($expected, ENT_COMPAT, $charset);

        $result = $this->Mail->punyencodeAddress($input);
        $this->assertSame($expected, $result);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataPunyencodeAddressConversion()
    {
        return [
            // This source file is in UTF-8, so characters here are in native charset.
            'UTF8' => [
                'input'    => 'test@fran&ccedil;ois.ch',
                'charset'  => PHPMailer::CHARSET_UTF8,
                'expected' => 'test@xn--franois-xxa.ch',
            ],
            // To force working another charset, decode an ASCII string to avoid literal string charset issues.
            'ISO88591' => [
                'input'    => 'test@fran&ccedil;ois.ch',
                'charset'  => PHPMailer::CHARSET_ISO88591,
                'expected' => 'test@xn--franois-xxa.ch',
            ],
            'Decode only domain' => [
                'input'    => 'fran&ccedil;ois@fran&ccedil;ois.ch',
                'charset'  => PHPMailer::CHARSET_UTF8,
                'expected' => 'fran&ccedil;ois@xn--franois-xxa.ch',
            ],
            'IDN conversion flags' => [
                'input'    => 'test@fußball.test',
                'charset'  => PHPMailer::CHARSET_UTF8,
                'expected' => 'test@xn--fuball-cta.test',
            ],
        ];
    }

    /**
     * Test IDN to ASCII form/punycode conversion returns the original value when no conversion
     * is needed or when the requirements to convert an address have not been met.
     *
     * @dataProvider dataPunyencodeAddressNoConversion
     *
     * @param string $input    Input text string.
     * @param string $charset  The character set.
     * @param string $expected Expected funtion output.
     */
    public function testPunyencodeAddressNoConversion($input, $charset, $expected)
    {
        $this->Mail->CharSet = $charset;

        // Prevent a warning about html_entity_decode() not supporting charset `us-ascii`.
        if ($charset !== PHPMailer::CHARSET_ASCII) {
            $input    = html_entity_decode($input, ENT_COMPAT, $charset);
            $expected = html_entity_decode($expected, ENT_COMPAT, $charset);
        }

        $result = $this->Mail->punyencodeAddress($input);
        $this->assertSame($expected, $result);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataPunyencodeAddressNoConversion()
    {
        return [
            'ASCII' => [
                'input'    => 'test@example.com',
                'charset'  => PHPMailer::CHARSET_ASCII,
                'expected' => 'test@example.com',
            ],
            'Invalid email address' => [
                'input'    => 'fran&ccedil;ois@',
                'charset'  => PHPMailer::CHARSET_UTF8,
                'expected' => 'fran&ccedil;ois@',
            ],
            'Not an email address' => [
                'input'    => 'testing 1-2-3',
                'charset'  => PHPMailer::CHARSET_UTF8,
                'expected' => 'testing 1-2-3',
            ],
            'Empty string' => [
                'input'    => '',
                'charset'  => PHPMailer::CHARSET_UTF8,
                'expected' => '',
            ],
            'Empty charset' => [
                'input'    => 'test@fran&ccedil;ois.ch',
                'charset'  => '',
                'expected' => 'test@fran&ccedil;ois.ch',
            ],
        ];
    }
}
