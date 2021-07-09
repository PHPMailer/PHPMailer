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
use ReflectionMethod;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test file accessibility verification functionality.
 *
 * {@internal There is one test case known to be missing, which is a test
 * with a valid UNC path. If someone can figure out a way to add a test for
 * that case, that would be awesome!}
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::fileIsAccessible
 */
final class FileIsAccessibleTest extends TestCase
{

    /**
     * Verify whether the "is a file accessible" check works correctly.
     *
     * @dataProvider dataFileIsAccessible
     *
     * @param string $input    A relative or absolute path to a file.
     * @param bool   $expected The expected function return value.
     */
    public function testFileIsAccessible($input, $expected)
    {
        $reflMethod = new ReflectionMethod(PHPMailer::class, 'fileIsAccessible');
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
    public function dataFileIsAccessible()
    {
        $fixturesPath = dirname(__DIR__) . '/Fixtures/FileIsAccessibleTest/';

        return [
            'Valid: accessible file' => [
                'input'    => $fixturesPath . 'accessible.txt',
                'expected' => true,
            ],
            'Invalid: path not permitted' => [
                'input'    => 'https://github.com/PHPMailer/PHPMailer/',
                'expected' => false,
            ],
            'Invalid: file does not exist' => [
                'input'    => $fixturesPath . 'thisfiledoesnotexist.txt',
                'expected' => false,
            ],
            'Invalid: file in UNC path does not exist' => [
                'input'    => '\\\\nowhere\nothing',
                'expected' => false,
            ],
        ];
    }

    /**
     * Test that the "is a file accessible" check correctly fails when the file permissions make
     * the file unreadable.
     */
    public function testFileIsAccessibleFailsOnUnreadableFile()
    {
        if (\DIRECTORY_SEPARATOR === '\\') {
            // Windows does not respect chmod permissions.
            $this->markTestSkipped('This test requires a non-Windows OS.');
        }

        $path = dirname(__DIR__) . '/Fixtures/FileIsAccessibleTest/';
        $file = $path . 'inaccessible.txt';
        chmod($file, octdec('0'));

        $reflMethod = new ReflectionMethod(PHPMailer::class, 'fileIsAccessible');
        $reflMethod->setAccessible(true);
        $result = $reflMethod->invoke(null, $file);
        $reflMethod->setAccessible(false);

        // Reset to the default for git files before running assertions.
        chmod($file, octdec('644'));

        self::assertFalse($result);
    }
}
