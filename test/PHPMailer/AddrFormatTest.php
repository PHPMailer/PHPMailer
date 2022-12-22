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

use PHPMailer\Test\TestCase;

/**
 * Test address formatting.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::addrFormat
 */
final class AddrFormatTest extends TestCase
{
    /**
     * Test formatting an address for use in a message header
     *
     * @dataProvider dataAddrFormat
     *
     * @param array  $addr     A 2-element indexed array, element 0 containing an address, element 1 containing a name
     * @param string $expected The expected function output.
     */
    public function testAddrFormat($addr, $expected)
    {
        $actual = $this->Mail->addrFormat($addr);
        self::assertSame(
            $expected,
            $actual,
            'The return value from addrFormat() did not match the expected output.'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataAddrFormat()
    {
        return [
            // Test cases with valid addresses.
            'Valid address: address with empty string name' => [
                'addr'     => ['joe@example.com', ''],
                'expected' => 'joe@example.com',
            ],
            'Valid address: address with null name' => [
                'addr'     => ['joe@example.com', null],
                'expected' => 'joe@example.com',
            ],
            'Valid address: address with falsy name' => [
                'addr'     => ['joe@example.com', '0'],
                'expected' => '0 <joe@example.com>',
            ],
            'Valid address: address with truthy name' => [
                'addr'     => ['joe@example.com', 'Joe'],
                'expected' => 'Joe <joe@example.com>',
            ]
        ];
    }
}
