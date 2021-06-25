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
            'Domain: lowercase'         => ['example.com'],
            'Domain: with subdomain'    => ['smtp.gmail.com'],
            'IPv4 address: 127.0.0.1'   => ['127.0.0.1'],
            'Long hex address'          => [trim(str_repeat('a0123456789.', 21), '.')],
            'Bracketed IPv6: localhost' => ['[::1]'],
            'Bracketed IPv6'            => ['[0:1234:dc0:41:216:3eff:fe67:3e01]'],
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
            'Invalid input: IPv4 address outside range' => ['999.0.0.0'],
            'Invalid input: hex address, wrong size'    => [trim(str_repeat('a0123456789.', 22), '.')],
            'Invalid input: bracketed, not IPv6 (num)'  => ['[1234]'],
            'Invalid input: bracketed, triple :'        => ['[1234:::1]'],
            'Invalid input: IPv6 without brackets'      => ['0:1234:dc0:41:216:3eff:fe67:3e01'],
            'Invalid input: IPv6 non-hex char'          => ['[012q:1234:dc0:41:216:3eff:fe67:3e01]'],
            'Invalid input: IPv6 double bracketed'      => ['[[::1]]'],
        ];
    }
}
