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
 * Test Multi-byte-safe pathinfo replacement functionality.
 */
final class MbPathinfoTest extends TestCase
{

    /**
     * Miscellaneous calls to improve test coverage and some small tests.
     */
    public function testMiscellaneous()
    {
        $a = '/mnt/files/飛兒樂 團光茫.mp3';
        $q = PHPMailer::mb_pathinfo($a);
        self::assertSame('/mnt/files', $q['dirname'], 'UNIX dirname not matched');
        self::assertSame('飛兒樂 團光茫.mp3', $q['basename'], 'UNIX basename not matched');
        self::assertSame('mp3', $q['extension'], 'UNIX extension not matched');
        self::assertSame('飛兒樂 團光茫', $q['filename'], 'UNIX filename not matched');
        self::assertSame(
            '/mnt/files',
            PHPMailer::mb_pathinfo($a, PATHINFO_DIRNAME),
            'Dirname path element not matched'
        );
        self::assertSame(
            '飛兒樂 團光茫.mp3',
            PHPMailer::mb_pathinfo($a, PATHINFO_BASENAME),
            'Basename path element not matched'
        );
        self::assertSame('飛兒樂 團光茫', PHPMailer::mb_pathinfo($a, 'filename'), 'Filename path element not matched');
        $a = 'c:\mnt\files\飛兒樂 團光茫.mp3';
        $q = PHPMailer::mb_pathinfo($a);
        self::assertSame('c:\mnt\files', $q['dirname'], 'Windows dirname not matched');
        self::assertSame('飛兒樂 團光茫.mp3', $q['basename'], 'Windows basename not matched');
        self::assertSame('mp3', $q['extension'], 'Windows extension not matched');
        self::assertSame('飛兒樂 團光茫', $q['filename'], 'Windows filename not matched');
    }
}
