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
use ReflectionMethod;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test path validation functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::isPermittedPath
 */
final class IsPermittedPathTest extends TestCase
{
    /**
     * Test whether the validation of whether a path is of a permitted type works correctly.
     *
     * @dataProvider dataIsPermittedPath
     *
     * @param string $input    A relative or absolute path to a file.
     * @param bool   $expected The expected function return value.
     */
    public function testIsPermittedPath($input, $expected)
    {
        $reflMethod = new ReflectionMethod(PHPMailer::class, 'isPermittedPath');
        $reflMethod->setAccessible(true);
        $result = $reflMethod->invoke(null, $input);
        $reflMethod->setAccessible(false);

        self::assertSame($expected, $result);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataIsPermittedPath()
    {
        return [
            'Valid: full, local path; Linux style, forward slashes' => [
                'input'    => '/usr/sbin/subdir/docs.pdf',
                'expected' => true,
            ],
            'Valid: full, local path; Windows style, backslashes' => [
                'input'    => 'D:\subdir\with spaces\subdir\myapp.zip',
                'expected' => true,
            ],
            'Valid: full, local path; Windows style, forward slashes' => [
                'input'    => 'D:/subdir/with spaces/subdir/',
                'expected' => true,
            ],
            'Valid: relative local path; forward slashes' => [
                'input'    => '/etc/hostname',
                'expected' => true,
            ],
            'Valid: relative local path; forward slashes, path traversal' => [
                'input'    => './../../subdir/.htaccess',
                'expected' => true,
            ],
            'Valid: relative local path; backslashes, path traversal' => [
                'input'    => '..\subdir\\',
                'expected' => true,
            ],
            'Valid: file name only' => [
                'input'    => 'composer.json',
                'expected' => true,
            ],
            'Valid: UNC path' => [
                'input'    => '\\\\nowhere\\nothing',
                'expected' => true,
            ],

            'Invalid: phar file reference' => [
                'input'    => 'phar://phar.php',
                'expected' => false,
            ],
            'Invalid: external URL; protocol: https' => [
                'input'    => 'https://github.com/PHPMailer/PHPMailer/',
                'expected' => false,
            ],
            'Invalid: external URL; protocol: http (uppercase)' => [
                'input'    => 'HTTP://github.com/PHPMailer/PHPMailer/',
                'expected' => false,
            ],
            'Invalid: external URL; protocol: ssh2.sftp' => [
                'input'    => 'ssh2.sftp://user:pass@attacker-controlled.example.com:22/tmp/payload.phar',
                'expected' => false,
            ],
            'Invalid: external URL; protocol: x-1.cd+-' => [
                'input'    => 'x-1.cd+-://example.com/test.php',
                'expected' => false,
            ],
        ];
    }
}
