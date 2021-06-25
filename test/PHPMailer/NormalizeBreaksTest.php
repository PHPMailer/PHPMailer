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
 * Test normalize line breaks functionality.
 */
final class NormalizeBreaksTest extends TestCase
{

    /**
     * Test line break reformatting.
     */
    public function testLineBreaks()
    {
        $unixsrc = "hello\nWorld\nAgain\n";
        $macsrc = "hello\rWorld\rAgain\r";
        $windowssrc = "hello\r\nWorld\r\nAgain\r\n";
        $mixedsrc = "hello\nWorld\rAgain\r\n";
        $target = "hello\r\nWorld\r\nAgain\r\n";
        self::assertSame($target, PHPMailer::normalizeBreaks($unixsrc), 'UNIX break reformatting failed');
        self::assertSame($target, PHPMailer::normalizeBreaks($macsrc), 'Mac break reformatting failed');
        self::assertSame($target, PHPMailer::normalizeBreaks($windowssrc), 'Windows break reformatting failed');
        self::assertSame($target, PHPMailer::normalizeBreaks($mixedsrc), 'Mixed break reformatting failed');
    }

    /**
     * Miscellaneous calls to improve test coverage and some small tests.
     */
    public function testMiscellaneous()
    {
        //Line break normalization
        $eol = PHPMailer::getLE();
        $b1 = "1\r2\r3\r";
        $b2 = "1\n2\n3\n";
        $b3 = "1\r\n2\r3\n";
        $t1 = "1{$eol}2{$eol}3{$eol}";
        self::assertSame($t1, PHPMailer::normalizeBreaks($b1), 'Failed to normalize line breaks (1)');
        self::assertSame($t1, PHPMailer::normalizeBreaks($b2), 'Failed to normalize line breaks (2)');
        self::assertSame($t1, PHPMailer::normalizeBreaks($b3), 'Failed to normalize line breaks (3)');
    }
}
