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

    public function testHostValidation()
    {
        $good = [
            'localhost',
            'example.com',
            'smtp.gmail.com',
            '127.0.0.1',
            trim(str_repeat('a0123456789.', 21), '.'),
            '[::1]',
            '[0:1234:dc0:41:216:3eff:fe67:3e01]',
        ];
        $bad = [
            null,
            123,
            1.5,
            new \stdClass(),
            [],
            '',
            '999.0.0.0',
            '[1234]',
            '[1234:::1]',
            trim(str_repeat('a0123456789.', 22), '.'),
            '0:1234:dc0:41:216:3eff:fe67:3e01',
            '[012q:1234:dc0:41:216:3eff:fe67:3e01]',
            '[[::1]]',
        ];
        foreach ($good as $h) {
            self::assertTrue(PHPMailer::isValidHost($h), 'Good hostname denied: ' . $h);
        }
        foreach ($bad as $h) {
            self::assertFalse(PHPMailer::isValidHost($h), 'Bad hostname accepted: ' . var_export($h, true));
        }
    }
}
