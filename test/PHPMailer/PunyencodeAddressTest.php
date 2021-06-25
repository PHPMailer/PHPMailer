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
        ];
    }
}
