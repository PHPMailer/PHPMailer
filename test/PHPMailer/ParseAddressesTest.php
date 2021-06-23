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
 */
final class ParseAddressesTest extends TestCase
{

    /**
     * Test RFC822 address splitting.
     */
    public function testAddressSplitting()
    {
        //Test built-in address parser
        self::assertCount(
            2,
            PHPMailer::parseAddresses(
                'Joe User <joe@example.com>, Jill User <jill@example.net>'
            ),
            'Failed to recognise address list (IMAP parser)'
        );
        self::assertSame(
            [
                ['name' => 'Joe User', 'address' => 'joe@example.com'],
                ['name' => 'Jill User', 'address' => 'jill@example.net'],
                ['name' => '', 'address' => 'frank@example.com'],
            ],
            PHPMailer::parseAddresses(
                'Joe User <joe@example.com>,'
                . 'Jill User <jill@example.net>,'
                . 'frank@example.com,'
            ),
            'Parsed addresses'
        );
        //Test simple address parser
        self::assertCount(
            2,
            PHPMailer::parseAddresses(
                'Joe User <joe@example.com>, Jill User <jill@example.net>',
                false
            ),
            'Failed to recognise address list'
        );
        //Test single address
        self::assertNotEmpty(
            PHPMailer::parseAddresses(
                'Joe User <joe@example.com>',
                false
            ),
            'Failed to recognise single address'
        );
        //Test quoted name IMAP
        self::assertNotEmpty(
            PHPMailer::parseAddresses(
                'Tim "The Book" O\'Reilly <foo@example.com>'
            ),
            'Failed to recognise quoted name (IMAP)'
        );
        //Test quoted name
        self::assertNotEmpty(
            PHPMailer::parseAddresses(
                'Tim "The Book" O\'Reilly <foo@example.com>',
                false
            ),
            'Failed to recognise quoted name'
        );
        //Test single address IMAP
        self::assertNotEmpty(
            PHPMailer::parseAddresses(
                'Joe User <joe@example.com>'
            ),
            'Failed to recognise single address (IMAP)'
        );
        //Test unnamed address
        self::assertNotEmpty(
            PHPMailer::parseAddresses(
                'joe@example.com',
                false
            ),
            'Failed to recognise unnamed address'
        );
        //Test unnamed address IMAP
        self::assertNotEmpty(
            PHPMailer::parseAddresses(
                'joe@example.com'
            ),
            'Failed to recognise unnamed address (IMAP)'
        );
        //Test invalid addresses
        self::assertEmpty(
            PHPMailer::parseAddresses(
                'Joe User <joe@example.com.>, Jill User <jill.@example.net>'
            ),
            'Failed to recognise invalid addresses (IMAP)'
        );
        //Test invalid addresses
        self::assertEmpty(
            PHPMailer::parseAddresses(
                'Joe User <joe@example.com.>, Jill User <jill.@example.net>',
                false
            ),
            'Failed to recognise invalid addresses'
        );
    }

    /**
     * Test RFC822 address list parsing using PHPMailer's parser.
     */
    public function testImapParsedAddressList_parseAddress_returnsAddressArray()
    {
        $addressLine = 'joe@example.com, <me@example.com>, Joe Doe <doe@example.com>,' .
            ' "John O\'Groats" <johnog@example.net>,' .
            ' =?utf-8?B?0J3QsNC30LLQsNC90LjQtSDRgtC10YHRgtCw?= <encoded@example.org>';

        //Test using PHPMailer's own parser
        $expected = [
            [
                'name' => '',
                'address' => 'joe@example.com',
            ],
            [
                'name' => '',
                'address' => 'me@example.com',
            ],
            [
                'name' => 'Joe Doe',
                'address' => 'doe@example.com',
            ],
            [
                'name' => "John O'Groats",
                'address' => 'johnog@example.net',
            ],
            [
                'name' => 'Название теста',
                'address' => 'encoded@example.org',
            ],
        ];
        $parsed = PHPMailer::parseAddresses($addressLine, false);
        $this->assertSameSize($expected, $parsed);
        for ($i = 0; $i < count($expected); $i++) {
            $this->assertSame($expected[$i], $parsed[$i]);
        }
    }

    /**
     * Test RFC822 address list parsing using the IMAP extension's parser.
     */
    public function testImapParsedAddressList_parseAddress_returnsAddressArray_usingImap()
    {
        if (!extension_loaded('imap')) {
            $this->markTestSkipped("imap extension missing, can't run this test");
        }
        $addressLine = 'joe@example.com, <me@example.com>, Joe Doe <doe@example.com>,' .
            ' "John O\'Groats" <johnog@example.net>,' .
            ' =?utf-8?B?0J3QsNC30LLQsNC90LjQtSDRgtC10YHRgtCw?= <encoded@example.org>';
        $expected = [
            [
                'name' => '',
                'address' => 'joe@example.com',
            ],
            [
                'name' => '',
                'address' => 'me@example.com',
            ],
            [
                'name' => 'Joe Doe',
                'address' => 'doe@example.com',
            ],
            [
                'name' => "John O'Groats",
                'address' => 'johnog@example.net',
            ],
            [
                'name' => 'Название теста',
                'address' => 'encoded@example.org',
            ],
        ];
        $parsed = PHPMailer::parseAddresses($addressLine, true);
        $this->assertSameSize($expected, $parsed);
        for ($i = 0; $i < count($expected); $i++) {
            $this->assertSame($expected[$i], $parsed[$i]);
        }
    }
}
