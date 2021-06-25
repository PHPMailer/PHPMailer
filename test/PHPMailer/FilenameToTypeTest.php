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
     * Miscellaneous calls to improve test coverage and some small tests.
     */
    public function testMiscellaneous()
    {
        self::assertSame(
            'image/jpeg',
            PHPMailer::filenameToType('abc.jpg?xyz=1'),
            'Query string not ignored in filename'
        );
        self::assertSame(
            'application/octet-stream',
            PHPMailer::filenameToType('abc.xyzpdq'),
            'Default MIME type not applied to unknown extension'
        );
    }
}
