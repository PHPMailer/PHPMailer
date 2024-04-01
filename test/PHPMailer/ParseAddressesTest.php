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
     * with the Mbstring extension available.
     *
     * @requires extension mbstring
     *
     * @dataProvider dataAddressSplitting
     *
     * @param string $addrstr  The address list string.
     * @param array  $expected The expected function output.
     * @param string $charset  Optional. The charset to use.
     */
    public function testAddressSplittingNative($addrstr, $expected, $charset = null)
    {
        if (isset($charset)) {
            $parsed = PHPMailer::parseAddresses($addrstr, false, $charset);
        } else {
            $parsed = PHPMailer::parseAddresses($addrstr, false);
        }

        $expectedOutput = $expected['default'];
        if (empty($expected['native+mbstring']) === false) {
            $expectedOutput = $expected['native+mbstring'];
        } elseif (empty($expected['native']) === false) {
            $expectedOutput = $expected['native'];
        }

        $this->verifyExpectations($parsed, $expectedOutput);
    }

    /**
     * Test RFC822 address splitting using the IMAP implementation
     * with the Mbstring extension available.
     *
     * @requires extension imap
     * @requires extension mbstring
     *
     * @dataProvider dataAddressSplitting
     *
     * @param string $addrstr  The address list string.
     * @param array  $expected The expected function output.
     * @param string $charset  Optional. The charset to use.
     */
    public function testAddressSplittingImap($addrstr, $expected, $charset = null)
    {
        if (isset($charset)) {
            $parsed = PHPMailer::parseAddresses($addrstr, true, $charset);
        } else {
            $parsed = PHPMailer::parseAddresses($addrstr, true);
        }

        $expectedOutput = $expected['default'];
        if (empty($expected['imap+mbstring']) === false) {
            $expectedOutput = $expected['imap+mbstring'];
        } elseif (empty($expected['imap']) === false) {
            $expectedOutput = $expected['imap'];
        }

        $this->verifyExpectations($parsed, $expectedOutput);
    }

    /**
     * Test RFC822 address splitting using the PHPMailer native implementation
     * without the Mbstring extension.
     *
     * @dataProvider dataAddressSplitting
     *
     * @param string $addrstr  The address list string.
     * @param array  $expected The expected function output.
     * @param string $charset  Optional. The charset to use.
     */
    public function testAddressSplittingNativeNoMbstring($addrstr, $expected, $charset = null)
    {
        if (extension_loaded('mbstring')) {
            $this->markTestSkipped('Test requires MbString *not* to be available');
        }

        if (isset($charset)) {
            $parsed = PHPMailer::parseAddresses($addrstr, false, $charset);
        } else {
            $parsed = PHPMailer::parseAddresses($addrstr, false);
        }

        $expectedOutput = $expected['default'];
        if (empty($expected['native--mbstring']) === false) {
            $expectedOutput = $expected['native--mbstring'];
        } elseif (empty($expected['native']) === false) {
            $expectedOutput = $expected['native'];
        }

        $this->verifyExpectations($parsed, $expectedOutput);
    }

    /**
     * Test RFC822 address splitting using the IMAP implementation
     * without the Mbstring extension.
     *
     * @requires extension imap
     *
     * @dataProvider dataAddressSplitting
     *
     * @param string $addrstr  The address list string.
     * @param array  $expected The expected function output.
     * @param string $charset  Optional. The charset to use.
     */
    public function testAddressSplittingImapNoMbstring($addrstr, $expected, $charset = null)
    {
        if (extension_loaded('mbstring')) {
            $this->markTestSkipped('Test requires MbString *not* to be available');
        }

        if (isset($charset)) {
            $parsed = PHPMailer::parseAddresses($addrstr, true, $charset);
        } else {
            $parsed = PHPMailer::parseAddresses($addrstr, true);
        }

        $expectedOutput = $expected['default'];
        if (empty($expected['imap--mbstring']) === false) {
            $expectedOutput = $expected['imap--mbstring'];
        } elseif (empty($expected['imap']) === false) {
            $expectedOutput = $expected['imap'];
        }

        $this->verifyExpectations($parsed, $expectedOutput);
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
     *               The `expected` key should - as a minimum - have a `default` key.
     *               Optionally, the following extra keys are supported:
     *               - `native`           Expected output from the native implementation with or without Mbstring.
     *               - `native+mbstring`  Expected output from the native implementation with Mbstring.
     *               - `native--mbstring` Expected output from the native implementation without Mbstring.
     *               - `imap`             Expected output from the IMAP implementation with or without Mbstring.
     *               - `imap+mbstring`    Expected output from the IMAP implementation with Mbstring.
     *               - `imap--mbstring`   Expected output from the IMAP implementation without Mbstring.
     *               Also optionally, an additional `charset` key can be passed,
     */
    public function dataAddressSplitting()
    {
        return [
            // Test cases with valid addresses.
            'Valid address: single address without name' => [
                'addrstr'  => 'joe@example.com',
                'expected' => [
                    'default' => [
                        ['name' => '', 'address' => 'joe@example.com'],
                    ],
                ],
            ],
            'Valid address: single address with name' => [
                'addrstr'  => 'Joe User <joe@example.com>',
                'expected' => [
                    'default' => [
                        ['name' => 'Joe User', 'address' => 'joe@example.com'],
                    ],
                ],
            ],
            'Valid address: single RFC2047 address folded onto multiple lines' => [
                'addrstr' => "=?iso-8859-1?B?QWJjZGVmZ2ggSWprbG3DsSDmnIPorbDlrqTpoJDntITn?=\r\n" .
                    ' =?iso-8859-1?B?s7vntbE=?= <xyz@example.com>',
                'expected' => [
                    'default' => [
                        ['name' => 'Abcdefgh Ijklmñ 會議室預約系統', 'address' => 'xyz@example.com'],
                    ],
                ],
            ],
            'Valid address: single RFC2047 address with space encoded as _' => [
                'addrstr' => '=?iso-8859-1?Q?Abcdefgh_ijklm=C3=B1?= <xyz@example.com>',
                'expected' => [
                    'default' => [
                        ['name' => 'Abcdefgh ijklmñ', 'address' => 'xyz@example.com'],
                    ],
                ],
            ],
            'Valid address: single address, quotes within name' => [
                'addrstr'  => 'Tim "The Book" O\'Reilly <foo@example.com>',
                'expected' => [
                    'default' => [
                        ['name' => 'Tim "The Book" O\'Reilly', 'address' => 'foo@example.com'],
                    ],
                    'imap' => [
                        ['name' => 'Tim The Book O\'Reilly', 'address' => 'foo@example.com'],
                    ],
                ],
            ],
            'Valid address: two addresses with names' => [
                'addrstr'  => 'Joe User <joe@example.com>, Jill User <jill@example.net>',
                'expected' => [
                    'default' => [
                        ['name' => 'Joe User', 'address' => 'joe@example.com'],
                        ['name' => 'Jill User', 'address' => 'jill@example.net'],
                    ],
                ],
            ],
            'Valid address: two addresses with names, one without' => [
                'addrstr'  => 'Joe User <joe@example.com>,'
                    . 'Jill User <jill@example.net>,'
                    . 'frank@example.com,',
                'expected' => [
                    'default' => [
                        ['name' => 'Joe User', 'address' => 'joe@example.com'],
                        ['name' => 'Jill User', 'address' => 'jill@example.net'],
                        ['name' => '', 'address' => 'frank@example.com'],
                    ],
                ],
            ],
            'Valid address: multiple address, various formats, including one utf8-encoded name' => [
                'addrstr'  => 'joe@example.com, <me@example.com>, Joe Doe <doe@example.com>,' .
                    ' "John O\'Groats" <johnog@example.net>,' .
                    ' =?utf-8?B?0J3QsNC30LLQsNC90LjQtSDRgtC10YHRgtCw?= <encoded@example.org>',
                'expected' => [
                    'default' => [
                        [
                            'name'    => '',
                            'address' => 'joe@example.com',
                        ],
                        [
                            'name'    => '',
                            'address' => 'me@example.com',
                        ],
                        [
                            'name'    => 'Joe Doe',
                            'address' => 'doe@example.com',
                        ],
                        [
                            'name'    => "John O'Groats",
                            'address' => 'johnog@example.net',
                        ],
                        [
                            'name'    => 'Название теста',
                            'address' => 'encoded@example.org',
                        ],
                    ],
                    'native--mbstring' => [
                        [
                            'name'    => '',
                            'address' => 'joe@example.com',
                        ],
                        [
                            'name'    => '',
                            'address' => 'me@example.com',
                        ],
                        [
                            'name'    => 'Joe Doe',
                            'address' => 'doe@example.com',
                        ],
                        [
                            'name'    => "John O'Groats",
                            'address' => 'johnog@example.net',
                        ],
                        [
                            'name'    => '=?utf-8?B?0J3QsNC30LLQsNC90LjQtSDRgtC10YHRgtCw?=',
                            'address' => 'encoded@example.org',
                        ],
                    ],
                    'imap--mbstring' => [
                        [
                            'name'    => '',
                            'address' => 'joe@example.com',
                        ],
                        [
                            'name'    => '',
                            'address' => 'me@example.com',
                        ],
                        [
                            'name'    => 'Joe Doe',
                            'address' => 'doe@example.com',
                        ],
                        [
                            'name'    => "John O'Groats",
                            'address' => 'johnog@example.net',
                        ],
                        [
                            'name'    => '=?utf-8?B?0J3QsNC30LLQsNC90LjQtSDRgtC10YHRgtCw?=',
                            'address' => 'encoded@example.org',
                        ],
                    ],
                ],
                'charset' => PHPMailer::CHARSET_UTF8,
            ],

            // Test cases with invalid addresses.
            'Invalid address: single address, incomplete email' => [
                'addrstr'  => 'Jill User <doug@>',
                'expected' => [
                    'default' => [],
                ],
            ],
            'Invalid address: single address, invalid characters in email' => [
                'addrstr'  => 'Joe User <{^c\@**Dog^}@cartoon.com>',
                'expected' => [
                    'default' => [],
                ],
            ],
            'Invalid address: multiple addresses, invalid periods' => [
                'addrstr'  => 'Joe User <joe@example.com.>, Jill User <jill.@example.net>',
                'expected' => [
                    'default' => [],
                ],
            ],
        ];
    }
}
