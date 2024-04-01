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
 * Test Multi-byte-safe pathinfo replacement functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::mb_pathinfo
 */
final class MbPathinfoTest extends TestCase
{
    /**
     * Verify retrieving information about a file path when the $options parameter has been passed.
     *
     * @dataProvider dataMb_pathinfoWithoutOptions
     *
     * @param string $path     Path input.
     * @param string $expected Expected function output.
     */
    public function testMb_pathinfoWithoutOptions($path, $expected)
    {
        $result = PHPMailer::mb_pathinfo($path);
        self::assertSame($expected, $result);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataMb_pathinfoWithoutOptions()
    {
        return [
            'Empty string' => [
                'path'     => '',
                'expected' => [
                    'dirname'   => '',
                    'basename'  => '',
                    'extension' => '',
                    'filename'  => '',
                ],
            ],
            'Unix path with singlebyte filename' => [
                'path'     => '/mnt/music/music.mp3',
                'expected' => [
                    'dirname'   => '/mnt/music',
                    'basename'  => 'music.mp3',
                    'extension' => 'mp3',
                    'filename'  => 'music',
                ],
            ],
            'Windows path with singlebyte filename' => [
                'path'     => 'c:\mnt\music\music.mp3',
                'expected' => [
                    'dirname'   => 'c:\mnt\music',
                    'basename'  => 'music.mp3',
                    'extension' => 'mp3',
                    'filename'  => 'music',
                ],
            ],
            'Unix path with multibyte filename' => [
                'path'     => '/mnt/files/飛兒樂 團光茫.mp3',
                'expected' => [
                    'dirname'   => '/mnt/files',
                    'basename'  => '飛兒樂 團光茫.mp3',
                    'extension' => 'mp3',
                    'filename'  => '飛兒樂 團光茫',
                ],
            ],
            'Windows path with multibyte filename' => [
                'path'     => 'c:\mnt\files\飛兒樂 團光茫.mp3',
                'expected' => [
                    'dirname'   => 'c:\mnt\files',
                    'basename'  => '飛兒樂 團光茫.mp3',
                    'extension' => 'mp3',
                    'filename'  => '飛兒樂 團光茫',
                ],
            ],
            'Filename, not path, contains spaces' => [
                'path'     => 'my file.png',
                'expected' => [
                    'dirname'   => '',
                    'basename'  => 'my file.png',
                    'extension' => 'png',
                    'filename'  => 'my file',
                ],
            ],
            'Path, no file name, linux style, contains spaces' => [
                'path'     => '/mnt/sub directory/another sub/',
                'expected' => [
                    'dirname'   => '/mnt/sub directory',
                    'basename'  => 'another sub',
                    'extension' => '',
                    'filename'  => 'another sub',
                ],
            ],
        ];
    }

    /**
     * Verify retrieving information about a file path when the $options parameter has been passed.
     *
     * @dataProvider dataMb_pathinfoWithOptions
     *
     * @param int|string $options  Input to pass to the $options parameter.
     * @param string     $expected Expected function output.
     */
    public function testMb_pathinfoWithOptions($options, $expected)
    {
        $path   = '/mnt/files/飛兒樂 團光茫.mp3';
        $result = PHPMailer::mb_pathinfo($path, $options);
        self::assertSame($expected, $result);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataMb_pathinfoWithOptions()
    {
        return [
            'Option: PATHINFO_DIRNAME' => [
                'options'  => PATHINFO_DIRNAME,
                'expected' => '/mnt/files',
            ],
            'Option: PATHINFO_BASENAME' => [
                'options'  => PATHINFO_BASENAME,
                'expected' => '飛兒樂 團光茫.mp3',
            ],
            'Option: PATHINFO_EXTENSION' => [
                'options'  => PATHINFO_EXTENSION,
                'expected' => 'mp3',
            ],
            'Option: PATHINFO_FILENAME' => [
                'options'  => PATHINFO_FILENAME,
                'expected' => '飛兒樂 團光茫',
            ],
            'Option: dirname' => [
                'options'  => 'dirname',
                'expected' => '/mnt/files',
            ],
            'Option: basename' => [
                'options'  => 'basename',
                'expected' => '飛兒樂 團光茫.mp3',
            ],
            'Option: extension' => [
                'options'  => 'extension',
                'expected' => 'mp3',
            ],
            'Option: filename' => [
                'options'  => 'filename',
                'expected' => '飛兒樂 團光茫',
            ],
        ];
    }
}
