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
 * Test file name to type functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::filenameToType
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
            'Empty string' => [
                'filename' => '',
                'expected' => 'application/octet-stream',
            ],
            'File name without query string' => [
                'filename' => 'abc.png',
                'expected' => 'image/png',
            ],
            'File name with query string' => [
                'filename' => 'abc.jpg?xyz=1',
                'expected' => 'image/jpeg',
            ],
            'Full path to file, linux style' => [
                'filename' => '/usr/sbin/subdir/docs.pdf',
                'expected' => 'application/pdf',
            ],
            'Full path to file, windows style' => [
                'filename' => 'D:\subdir\with spaces\subdir\myapp.zip',
                'expected' => 'application/zip',
            ],
            'Unknown extension, should return default MIME type' => [
                'filename' => 'abc.xyzpdq',
                'expected' => 'application/octet-stream',
            ],
        ];
    }
}
