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
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test RFC822 address splitting.
 *
 * @todo Additional tests need to be added to verify the correct handling of inputs which
 * include a different encoding than UTF8 or even mixed encoding. For more information
 * on what these test cases should look like and should test, please see
 * {@link https://github.com/PHPMailer/PHPMailer/pull/2449} for context.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::parseAddresses
 */
final class ParseAddressesTest extends TestCase
{
    /**
     * Test RFC822 address splitting using the PHPMailer native implementation
     *
     * @requires extension mbstring
     * @group mbstringRequired
     * @dataProvider dataAddressSplitting
     *
     * @param string $addrstr  The address list string.
     * @param array  $expected The expected function output.
     * @param string $charset  Optional. The charset to use.
     */
    public function testAddressSplitting($addrstr, $expected, $charset = null)
    {
        if (isset($charset)) {
            $parsed = PHPMailer::parseAddresses($addrstr, false, $charset);
        } else {
            $parsed = PHPMailer::parseAddresses($addrstr, false);
        }

        $this->verifyExpectations($parsed, $expected);
    }

    /**
     * Test decodeHeader using the PHPMailer
     * with the Mbstring extension available.
     *
     * @requires extension mbstring
     * @group mbstringExtRequired
     * @dataProvider dataDecodeHeader
     *
     * @param string $addrstr  The header string.
     * @param array  $expected The expected function output.
     */
    public function testDecodeHeaderMbstring($addrstr, $expected)
    {

        $parsed = PHPMailer::decodeHeader($addrstr);

        $this->assertEquals($parsed, $expected['mbstring']);
    }
    
    /**
     * Test decodeHeader using the PHPMailer native implementation
     * without the Mbstring extension.
     *
     * @group mbstringExtDisabled
     * @dataProvider dataDecodeHeader
     *
     * @param string $addrstr  The header string.
     * @param array  $expected The expected function output.
     */
    public function testDecodeHeaderNative($addrstr, $expected)
    {
        if (extension_loaded('mbstring')) {
            self::markTestSkipped('Test requires MbString *not* to be available');
        }
        $parsed = PHPMailer::decodeHeader($addrstr);

        $this->assertEquals($parsed, $expected['native']);
    }

    /**
     * Verify the expectations.
     *
     * Abstracted out as the same verification needs to be done for every test, just with different data.
     *
     * @param string $actual   The actual function output.
     * @param array  $expected The expected function output.
     */
    protected function verifyExpectations($actual, $expected)
    {
        self::assertIsArray($actual, 'parseAddresses() did not return an array');
        self::assertSame(
            $expected,
            $actual,
            'The return value from parseAddresses() did not match the expected output'
        );
    }

    /**
     * Data provider.
     *
     * @return array The array is expected to have an `addrstr` and an `expected` key.
     *               The `expected` key should - as a minimum.
     */
    public function dataAddressSplitting()
    {
        return [
            // Test cases with valid addresses.
            'Valid address: single address without name' => [
                'addrstr'  => 'joe@example.com',
                'expected' => [
                        ['name' => '', 'address' => 'joe@example.com'],
                ],
            ],
            'Valid address: single address with name' => [
                'addrstr'  => 'Joe User <joe@example.com>',
                'expected' => [
                    ['name' => 'Joe User', 'address' => 'joe@example.com'],
                ],
            ],
            'Valid address: single RFC2047 address folded onto multiple lines' => [
                'addrstr' => "=?iso-8859-1?B?QWJjZGVmZ2ggSWprbG3DsSDmnIPorbDlrqTpoJDntITn?=\r\n" .
                    ' =?iso-8859-1?B?s7vntbE=?= <xyz@example.com>',
                'expected' => [
                    ['name' => 'Abcdefgh Ijklmñ 會議室預約系統', 'address' => 'xyz@example.com'],
                ],
            ],
            'Valid address: single RFC2047 address with space encoded as _' => [
                'addrstr' => '=?iso-8859-1?Q?Abcdefgh_ijklm=C3=B1?= <xyz@example.com>',
                'expected' => [
                    ['name' => 'Abcdefgh ijklmñ', 'address' => 'xyz@example.com'],
                ],
            ],
            'Valid address: single address, quotes within name' => [
                'addrstr'  => 'Tim "The Book" O\'Reilly <foo@example.com>',
                'expected' => [
                    ['name' => 'Tim "The Book" O\'Reilly', 'address' => 'foo@example.com'],
                ],
            ],
            'Valid address: two addresses with names' => [
                'addrstr'  => 'Joe User <joe@example.com>, Jill User <jill@example.net>',
                'expected' => [
                    ['name' => 'Joe User', 'address' => 'joe@example.com'],
                    ['name' => 'Jill User', 'address' => 'jill@example.net'],
                ],
            ],
            'Valid address: two addresses with names, one without' => [
                'addrstr'  => 'Joe User <joe@example.com>,'
                    . 'Jill User <jill@example.net>,'
                    . 'frank@example.com,',
                'expected' => [
                    ['name' => 'Joe User', 'address' => 'joe@example.com'],
                    ['name' => 'Jill User', 'address' => 'jill@example.net'],
                    ['name' => '', 'address' => 'frank@example.com'],
                ],
            ],
            'Valid address: multiple address, various formats, including one utf8-encoded names' => [
                'addrstr'  => 'joe@example.com, <me@example.com>, Joe Doe <doe@example.com>,' .
                    ' "John O\'Groats" <johnog@example.net>,' .
                    ' =?utf-8?B?0J3QsNC30LLQsNC90LjQtSDRgtC10YHRgtCw?= <encoded@example.org>,' .
                    ' =?UTF-8?Q?Welcome_to_our_caf=C3=A9!?= =?ISO-8859-1?Q?_Willkommen_in_unserem_Caf=E9!?= =?KOI8-R?Q?_=F0=D2=C9=D7=C5=D4_=D7_=CE=C1=DB=C5_=CB=C1=C6=C5!?= <encoded3@example.org>',
                'expected' => [
                    ['name' => '', 'address' => 'joe@example.com'],
                    ['name' => '', 'address' => 'me@example.com'],
                    ['name' => 'Joe Doe', 'address' => 'doe@example.com'],
                    ['name' => "John O'Groats", 'address' => 'johnog@example.net'],
                    ['name' => 'Название теста', 'address' => 'encoded@example.org'],
                    ['name' => 'Welcome to our café! Willkommen in unserem Café! Привет в наше кафе!', 'address' => 'encoded3@example.org'],
                ],
                'charset' => PHPMailer::CHARSET_UTF8,
            ],

            // Test cases with invalid addresses.
            'Invalid address: single address, incomplete email' => [
                'addrstr'  => 'Jill User <doug@>',
                'expected' => [],
            ],
            'Invalid address: single address, invalid characters in email' => [
                'addrstr'  => 'Joe User <{^c\@**Dog^}@cartoon.com>',
                'expected' => [],
            ],
            'Invalid address: multiple addresses, invalid periods' => [
                'addrstr'  => 'Joe User <joe@example.com.>, Jill User <jill.@example.net>',
                'expected' => [],
            ],
        ];
    }

    /**
     * Data provider for decodeHeader.
     *
     * @return array The array is expected to have an `addrstr` and an `expected` key.
     *               The `expected` key should - as a minimum - have a `mbstring` and `native` key.
     *               - `mbstring`           Expected output from the native implementation with Mbstring.
     *               - `native`             Expected output from the native implementation without Mbstring.
     */
    public function dataDecodeHeader()
    {
        return [
            'UTF-8' => [
                'name'  => '=?utf-8?B?0J3QsNC30LLQsNC90LjQtSDRgtC10YHRgtCw?=',
                'expected' => [
                    'mbstring' => 'Название теста',
                    'native' => '=?utf-8?B?0J3QsNC30LLQsNC90LjQtSDRgtC10YHRgtCw?=',
                ],
            ],
            'KOI8-R' => [
                'name'  => '=?koi8-r?B?0J3QsNC30LLQsNC90LjQtSDRgtC10YHRgtCw?=',
                'expected' => [
                    'mbstring' => 'Название теста',
                    'native' => '=?koi8-r?B?0J3QsNC30LLQsNC90LjQtSDRgtC10YHRgtCw?=',
                ],
            ],
            'Simple ISO-8859-1' => [
                'name'  => '=?ISO-8859-1?Q?_Willkommen_in_unserem_Caf=E9!?=',
                'expected' => [
                    'mbstring' => 'Willkommen in unserem Café!',
                    'native' => 'Willkommen in unserem Café!',
                ],
            ],
            'Wrongly labeled ISO-8859-1' => [
                'name'  => '=?iso-8859-1?B?QWJjZGVmZ2ggSWprbG3DsSDmnIPorbDlrqTpoJDntITns7vntbE=?=',
                'expected' => [
                    'mbstring' => 'Abcdefgh Ijklmñ 會議室預約系統',
                    'native' => '=?iso-8859-1?B?QWJjZGVmZ2ggSWprbG3DsSDmnIPorbDlrqTpoJDntITn?=',
                ],
            ],
            'UTF-8' => [
                'name' => '=?UTF-8?B?SGVsbG8g8J+MjSDkuJbnlYwgY2Fmw6k=?=',
                'expected' => [
                    'mbstring' => 'Hello 🌍 世界 café',
                    'native' => '=?UTF-8?B?SGVsbG8g8J+MjSDkuJbnlYwgY2Fmw6k=?=',
                ],
            ],
        ];
    }

}
