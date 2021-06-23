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
 * Test RFC822 address splitting.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::parseAddresses
 */
final class ParseAddressesTest extends TestCase
{

    /**
     * Test RFC822 address splitting using the PHPMailer native implementation.
     *
     * @dataProvider dataAddressSplitting
     *
     * @param string $addrstr  The address list string.
     * @param array  $expected The expected function output.
     */
    public function testAddressSplittingNative($addrstr, $expected)
    {
        $parsed = PHPMailer::parseAddresses($addrstr, false);

        self::assertIsArray($parsed, 'parseAddresses() did not return an array');
        self::assertSame(
            $expected,
            $parsed,
            'The return value from parseAddresses() did not match the expected output'
        );
    }

    /**
     * Test RFC822 address splitting using the IMAP implementation.
     *
     * @dataProvider dataAddressSplitting
     *
     * @requires extension imap
     *
     * @param string $addrstr      The address list string.
     * @param array  $expected     The expected function output.
     * @param array  $expectedImap Optional. The expected function output via IMAP if different.
     */
    public function testAddressSplittingImap($addrstr, $expected, $expectedImap = [])
    {
        $parsed = PHPMailer::parseAddresses($addrstr, true);

        self::assertIsArray($parsed, 'parseAddresses() did not return an array');

        $expected = empty($expectedImap) ? $expected : $expectedImap;
        self::assertSame(
            $expected,
            $parsed,
            'The return value from parseAddresses() did not match the expected output'
        );
    }

    /**
     * Data provider.
     *
     * @return array
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
            'Valid address: single address, quotes within name' => [
                'addrstr'  => 'Tim "The Book" O\'Reilly <foo@example.com>',
                'expected' => [
                    ['name' => 'Tim "The Book" O\'Reilly', 'address' => 'foo@example.com'],
                ],
                'expectedImap' => [
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
            'Valid address: multiple address, various formats, including one utf8-encoded address' => [
                'addrstr'  => 'joe@example.com, <me@example.com>, Joe Doe <doe@example.com>,' .
                    ' "John O\'Groats" <johnog@example.net>,' .
                    ' =?utf-8?B?0J3QsNC30LLQsNC90LjQtSDRgtC10YHRgtCw?= <encoded@example.org>',
                'expected' => [
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
}
