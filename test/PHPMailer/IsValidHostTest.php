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
 * Test host validation functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::isValidHost
 */
final class IsValidHostTest extends TestCase
{
    /**
     * Test host validation when a valid host is passed.
     *
     * @dataProvider dataValidHost
     *
     * @param string $input Input text string.
     */
    public function testValidHost($input)
    {
        self::assertTrue(PHPMailer::isValidHost($input), 'Good hostname denied');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataValidHost()
    {
        return [
            'localhost'                 => ['localhost'],
            'Domain alias'              => ['a'],
            'Domain: lowercase'         => ['example.com'],
            'Domain: uppercase'         => ['EXAMPLE.COM'],
            'Domain: mixed case'        => ['Example.Com'],
            'Domain: with dash'         => ['my-example.com'],
            'Domain: with subdomain'    => ['smtp.gmail.com'],
            'IPv4 address: 127.0.0.1'   => ['127.0.0.1'],
            'IPv4 address: external'    => ['27.145.58.181'],
            'Long hex address'          => [trim(str_repeat('a0123456789.', 21), '.')],
            'Bracketed IPv6: localhost' => ['[::1]'],
            'Bracketed IPv6'            => ['[0:1234:dc0:41:216:3eff:fe67:3e01]'],
            'Bracketed IPv6 uppercase'  => ['[0:1234:DC0:41:216:3EFF:FE67:3E01]'],
        ];
    }

    /**
     * Test host validation when an invalid host is passed.
     *
     * @dataProvider dataInvalidHost
     *
     * @param string $input Input text string.
     */
    public function testInvalidHost($input)
    {
        self::assertFalse(PHPMailer::isValidHost($input), 'Bad hostname accepted');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataInvalidHost()
    {
        return [
            'Invalid type: null'                        => [null],
            'Invalid type: int'                         => [123],
            'Invalid type: float'                       => [1.5],
            'Invalid type: object'                      => [new \stdClass()],
            'Invalid type: array'                       => [[]],
            'Invalid input: empty string'               => [''],
            'Invalid input: domain - empty subdomain'   => ['.example.com'],
            'Invalid input: domain - with underscore'   => ['my_example.com'],
            'Invalid input: IPv4, no dots'              => ['127001'],
            'Invalid input: IPv4, too few groups'       => ['168.0.001'],
            'Invalid input: IPv4, too many groups'      => ['22.55.14.73.9'],
            'Invalid input: IPv4 address outside range' => ['999.0.0.0'],
            'Invalid input: hex address, wrong size'    => [trim(str_repeat('a0123456789.', 22), '.')],
            'Invalid input: bracketed, not IPv6 (num)'  => ['[1234]'],
            'Invalid input: bracketed, not IPv6 (hex)'  => ['[abCDef]'],
            'Invalid input: bracketed, triple :'        => ['[1234:::1]'],
            'Invalid input: IPv6 empty brackets'        => ['[]'],
            'Invalid input: IPv6 without brackets'      => ['0:1234:dc0:41:216:3eff:fe67:3e01'],
            'Invalid input: IPv6 too few groups'        => ['[0:1234:dc0:41]'],
            'Invalid input: IPv6 too many groups'       => ['[012q:1234:dc0:41:216:3eff:fe67:3e01:6fa5]'],
            'Invalid input: IPv6 non-hex char'          => ['[012q:1234:dc0:41:216:3eff:fe67:3e01]'],
            'Invalid input: IPv6 disallowed chars'      => ['[0:12_4:dc$:41:216:3e+f:fe67:3e01]'],
            'Invalid input: IPv6 double bracketed'      => ['[[::1]]'],
        ];
    }
}
