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
 * Test mime type mapping functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::_mime_types
 */
final class MimeTypesTest extends TestCase
{
    /**
     * Test mime type mapping.
     *
     * @dataProvider dataMime_Types
     *
     * @param string $input     Input text string.
     * @param string $expected  Expected function output.
     */
    public function testMime_Types($input, $expected)
    {
        $result = PHPMailer::_mime_types($input);
        self::assertSame($expected, $result, 'MIME TYPE lookup failed');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataMime_Types()
    {
        return [
            'Extension: pdf (lowercase)' => [
                'input'    => 'pdf',
                'expected' => 'application/pdf',
            ],
            'Extension: PHP (uppercase)' => [
                'input'    => 'PHP',
                'expected' => 'application/x-httpd-php',
            ],
            'Extension: Doc (mixed case)' => [
                'input'    => 'Doc',
                'expected' => 'application/msword',
            ],
            'Extension which is not in the list' => [
                'input'    => 'md',
                'expected' => 'application/octet-stream',
            ],
            'Empty string' => [
                'input'    => '',
                'expected' => 'application/octet-stream',
            ],
        ];
    }
}
