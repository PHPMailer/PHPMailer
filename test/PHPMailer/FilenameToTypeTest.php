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
 * Test file name to type functionality.
 */
final class FilenameToTypeTest extends TestCase
{

    /**
     * Verify mapping a file name to a MIME type.
     *
     * @dataProvider dataFilenameToType
     *
     * @param string $filename Filename input.
     * @param string $expected Expected function output.
     */
    public function testFilenameToType($filename, $expected)
    {
        $result = PHPMailer::filenameToType($filename);
        self::assertSame($expected, $result, 'Failed to map file name to a MIME type');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataFilenameToType()
    {
        return [
            'File name with query string' => [
                'filename' => 'abc.jpg?xyz=1',
                'expected' => 'image/jpeg',
            ],
            'Unknown extension, should return default MIME type' => [
                'filename' => 'abc.xyzpdq',
                'expected' => 'application/octet-stream',
            ],
        ];
    }
}
