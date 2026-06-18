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
use ReflectionMethod;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test RFC822 address splitting.
 */
final class ParseAddressesTest extends TestCase
{
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
     * Test RFC822 address splitting using the native implementation
     *
     * @dataProvider dataAddressSplittingNative
     * @covers \PHPMailer\PHPMailer\PHPMailer::parseSimplerAddresses
     *
     * @param string $addrstr The address list string.
     * @param array $expected The expected function output.
     * @param string $charset Optional.The charset to use.
     */
    public function testAddressSplittingNative($addrstr, $expected, $charset = PHPMailer::CHARSET_ISO88591)
    {
        set_error_handler(static function ($errno, $errstr) {
            throw new \Exception($errstr, $errno);
        }, E_USER_NOTICE);

        try {
            $this->expectException(\Exception::class);

            $reflMethod = new ReflectionMethod(PHPMailer::class, 'parseSimplerAddresses');
            (\PHP_VERSION_ID < 80100) && $reflMethod->setAccessible(true);
            $parsed = $reflMethod->invoke(null, $addrstr, $charset);
            (\PHP_VERSION_ID < 80100) && $reflMethod->setAccessible(false);
            $this->verifyExpectations($parsed, $expected);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Test that falsy $useimap values use the simpler parser without a deprecation warning.
     *
     * @dataProvider dataParseAddressesFalsyUseimapValues
     * @covers \PHPMailer\PHPMailer\PHPMailer::parseAddresses
     *
     * @param mixed $useimap The $useimap argument.
     */
    public function testParseAddressesWithFalsyUseimapValues($useimap)
    {
        set_error_handler(static function ($errno, $errstr) {
            if ($errno === E_USER_DEPRECATED) {
                throw new \Exception($errstr, $errno);
            }

            return true;
        }, E_USER_NOTICE | E_USER_DEPRECATED);

        try {
            $expected = [
                ['name' => '', 'address' => 'joe@example.com'],
            ];
            $this->verifyExpectations(PHPMailer::parseAddresses('joe@example.com', $useimap), $expected);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Test that truthy $useimap values request the deprecated IMAP parser.
     *
     * @dataProvider dataParseAddressesTruthyUseimapValues
     * @covers \PHPMailer\PHPMailer\PHPMailer::parseAddresses
     *
     * @param mixed $useimap The $useimap argument.
     */
    public function testParseAddressesWithTruthyUseimapValues($useimap)
    {
        set_error_handler(static function ($errno, $errstr) {
            if ($errno === E_USER_DEPRECATED) {
                throw new \Exception($errstr, $errno);
            }

            return true;
        }, E_USER_DEPRECATED);

        try {
            $this->expectException(\Exception::class);
            $this->expectExceptionCode(E_USER_DEPRECATED);
            PHPMailer::parseAddresses('joe@example.com', $useimap);
        } finally {
            restore_error_handler();
        }
    }

    /**
     * Data provider for falsy $useimap values.
     *
     * @return array
     */
    public static function dataParseAddressesFalsyUseimapValues()
    {
        return [
            'null' => [null],
            'false' => [false],
            'zero integer' => [0],
            'empty string' => [''],
            'zero string' => ['0'],
        ];
    }

    /**
     * Data provider for truthy $useimap values.
     *
     * @return array
     */
    public static function dataParseAddressesTruthyUseimapValues()
    {
        return [
            'true' => [true],
            'one integer' => [1],
            'one string' => ['1'],
            'yes string' => ['yes'],
        ];
    }

    /**
     * Data provider for testAddressSplittingNative.
     *
     * @return array
     *      addrstr: string,
     *      expected: array{name: string, address: string}[]
     *      charset: string
     */
    public static function dataAddressSplittingNative()
    {
        return [
            'Valid address: single address without name' => [
                'addrstr' => 'joe@example.com',
                'expected' => [
                    ['name' => '', 'address' => 'joe@example.com'],
                ],
            ],
            'Valid address: two addresses with names' => [
                'addrstr'  => 'Joe User <joe@example.com>, Jill User <jill@example.net>',
                'expected' => [
                    ['name' => 'Joe User', 'address' => 'joe@example.com'],
                    ['name' => 'Jill User', 'address' => 'jill@example.net'],
                ],
            ],
        ];
    }

     /**
     * Test if email addresses are parsed and split into a name and address.
     *
     * @dataProvider dataParseEmailString
     * @covers \PHPMailer\PHPMailer\PHPMailer::parseEmailString
     * @param mixed $addrstr
     * @param mixed $expected
     */
    public function testParseEmailString($addrstr, $expected)
    {
        $reflMethod = new ReflectionMethod(PHPMailer::class, 'parseEmailString');
        (\PHP_VERSION_ID < 80100) && $reflMethod->setAccessible(true);
        $parsed = $reflMethod->invoke(null, $addrstr);
        (\PHP_VERSION_ID < 80100) && $reflMethod->setAccessible(false);
        $this->assertEquals($parsed, $expected);
    }

    /**
     * Data provider for testParseEmailString.
     *
     * @return array The array is expected to have an `addrstr` and an `expected` key.
     */
    public static function dataParseEmailString()
    {
        return [
            'Valid address: simple address' => [
                'addrstr' => 'Joe User <joe@example.com>',
                'expected' => ['name' => 'Joe User', 'email' => 'joe@example.com'],
            ],
            'Valid address: simple address with double quotes' => [
                'addrstr' => '"Joe User" <joe@example.com>',
                'expected' => ['name' => 'Joe User', 'email' => 'joe@example.com'],
            ],
            'Valid address: simple address with single quotes' => [
                'addrstr' => '\'Joe User\' <joe@example.com>',
                'expected' => ['name' => 'Joe User', 'email' => 'joe@example.com'],
            ],
            'Valid address: complex address with single quotes' => [
                'addrstr' => '\'Joe<User\' <joe@example.com>',
                'expected' => ['name' => 'Joe<User', 'email' => 'joe@example.com'],
            ],
            'Valid address: complex address with triangle bracket' => [
                'addrstr' => '"test<stage" <test@example.com>',
                'expected' => ['name' => 'test<stage', 'email' => 'test@example.com'],
            ],
        ];
    }

    /**
     * Test RFC822 address splitting using the PHPMailer implementation
     *
     * @dataProvider dataAddressSplitting
     * @covers \PHPMailer\PHPMailer\PHPMailer::parseAddresses
     *
     * @requires extension imap
     * @requires extension mbstring
     *
     * @param string $addrstr  The address list string.
     * @param array  $expected The expected function output.
     * @param string $charset  Optional. The charset to use.
     */
    public function testAddressSplitting($addrstr, $expected)
    {
        set_error_handler(static function () {
            return true;
        }, E_USER_DEPRECATED);

        try {
            $parsed = PHPMailer::parseAddresses($addrstr, true, PHPMailer::CHARSET_UTF8);
        } finally {
            restore_error_handler();
        }

        $this->verifyExpectations($parsed, $expected);
    }

    /**
     * Data provider.
     *
     * @return array The array is expected to have an `addrstr` and an `expected` key.
     *               The `expected` key should - as a minimum.
     */
    public static function dataAddressSplitting()
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
                'addrstr' => "=?ISO-8859-1?Q?J=F6rg?=\r\n" .
                    ' =?ISO-8859-1?Q?_M=FCller?= <xyz@example.com>',
                'expected' => [
                    ['name' => 'Jörg Müller', 'address' => 'xyz@example.com'],
                ],
            ],
            'Valid address: single RFC2047 address with space encoded as _' => [
                'addrstr' => '=?iso-8859-1?Q?Abcdefgh_ijklm=F1?= <xyz@example.com>',
                'expected' => [
                    ['name' => 'Abcdefgh ijklmñ', 'address' => 'xyz@example.com'],
                ],
            ],
            'Valid address: single address, quotes within name' => [
                'addrstr'  => 'Tim "The Book" O\'Reilly <foo@example.com>',
                'expected' => [
                    ['name' => 'Tim The Book O\'Reilly', 'address' => 'foo@example.com'],
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
                    ' =?UTF-8?Q?Welcome_to_our_caf=C3=A9!?= =?ISO-8859-1?Q?_Willkommen_in_unserem_Caf=E9!?=' .
                    ' =?KOI8-R?Q?_=F0=D2=C9=D7=C5=D4_=D7_=CE=C1=DB=C5_=CB=C1=C6=C5!?= <encoded3@example.org>',
                'expected' => [
                    ['name' => '', 'address' => 'joe@example.com'],
                    ['name' => '', 'address' => 'me@example.com'],
                    ['name' => 'Joe Doe', 'address' => 'doe@example.com'],
                    ['name' => "John O'Groats", 'address' => 'johnog@example.net'],
                    ['name' => 'Название теста', 'address' => 'encoded@example.org'],
                    [
                        'name' => 'Welcome to our café! Willkommen in unserem Café! Привет в наше кафе!',
                        'address' => 'encoded3@example.org'
                    ],
                ]
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
     * Test decodeHeader using the PHPMailer
     * with the Mbstring extension available.
     *
     * @dataProvider dataDecodeHeader
     * @covers \PHPMailer\PHPMailer\PHPMailer::decodeHeader
     *
     * @requires extension mbstring
     *
     * @param string $addrstr  The header string.
     * @param array  $expected The expected function output.
     */
    public function testDecodeHeader($str, $expected)
    {
        $parsed = PHPMailer::decodeHeader($str, PHPMailer::CHARSET_UTF8);

        $this->assertEquals($parsed, $expected);
    }

    /**
     * Data provider for decodeHeader.
     *
     * @return array The array is expected to have an `addrstr` and an `expected` key.
     *               The `expected` key should - as a minimum - have a single value.
     */
    public static function dataDecodeHeader()
    {
        return [
            'UTF-8 B-encoded' => [
                'name'  => '=?utf-8?B?0J3QsNC30LLQsNC90LjQtSDRgtC10YHRgtCw?=',
                'expected' => 'Название теста',
            ],
            'UTF-8 Q-encoded' => [
                'name'  => '=?UTF-8?Q?=D0=9D=D0=B0=D0=B7=D0=B2=D0=B0=D0=BD=D0=B8?=' .
                    ' =?UTF-8?Q?=D0=B5_=D1=82=D0=B5=D1=81=D1=82=D0=B0?=',
                'expected' => 'Название теста',
            ],
            'UTF-8 Q-encoded with multiple wrong labels and space encoded as _' => [
                'name'  => '=?UTF-8?Q?Welcome_to_our_caf=C3=A9!?= =?ISO-8859-1?Q?_Willkommen_in_unserem_Caf=E9!?=' .
                    ' =?KOI8-R?Q?_=F0=D2=C9=D7=C5=D4_=D7_=CE=C1=DB=C5_=CB=C1=C6=C5!?=',
                'expected' => 'Welcome to our café! Willkommen in unserem Café! Привет в наше кафе!',
            ],
            'ISO-8859-1 Q-encoded' => [
                'name'  => '=?ISO-8859-1?Q?Willkommen_in_unserem_Caf=E9!?=',
                'expected' => 'Willkommen in unserem Café!',
            ],
            'Valid but wrongly labeled UTF-8 as ISO-8859-1' => [
                'name'  => '=?iso-8859-1?B?5pyD6K2w5a6k?=',
                'expected' => "æ\xC2\x9C\xC2\x83è­°å®¤",
            ],
            'SMTPUTF8 encoded' => [
                'name' => '=?UTF-8?B?SGVsbG8g8J+MjSDkuJbnlYwgY2Fmw6k=?=',
                'expected' => 'Hello 🌍 世界 café',
            ],
            'Multiple lines' => [
                'name'  => '=?UTF-8?B?0YLQtdGB0YIg0YLQtdGB0YIg0YLQtdGB0YIg0YLQtdGB0YIg0YLQtdGB0YIg?='
                . "\n =?UTF-8?B?0YLQtdGB0YIg0YLQtdGB0YI=?=",
                'expected' => 'тест тест тест тест тест тест тест',
            ],
        ];
    }
}
