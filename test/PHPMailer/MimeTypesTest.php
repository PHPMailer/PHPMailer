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
 * Test mime type mapping functionality.
 */
final class MimeTypesTest extends TestCase
{

    /**
     * Miscellaneous calls to improve test coverage and some small tests.
     */
    public function testMiscellaneous()
    {
        self::assertSame('application/pdf', PHPMailer::_mime_types('pdf'), 'MIME TYPE lookup failed');
    }
}
