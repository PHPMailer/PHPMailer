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
 * Test quoted string functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::quotedString
 */
final class QuotedStringTest extends TestCase
{
    /**
     * Test quoting of a string depending on the content of the string.
     *
     * @dataProvider dataQuotedString
     *
     * @param string $input     Input text string.
     * @param string $expected  Expected funtion output.
     */
    public function testQuotedString($input, $expected)
    {
        $result = PHPMailer::quotedString($input);
        self::assertSame($expected, $result);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataQuotedString()
    {
        return [
            'No special chars' => [
                'input'    => 'phpmailer.png',
                'expected' => 'phpmailer.png',
            ],
            'Text containing double quote char' => [
                'input'    => 'phpmailer_mini".png',
                'expected' => '"phpmailer_mini\".png"',
            ],
            'Text containing pre-escaped double quote char' => [
                'input'    => 'phpmailer_mini\".png',
                'expected' => '"phpmailer_mini\\\".png"',
            ],
            'Text containing spaces' => [
                'input'    => 'PHPMailer card logo.png',
                'expected' => '"PHPMailer card logo.png"',
            ],
            'Text containing variety of "special" chars' => [
                'input'    => 'php@ma;ler=m:ni,p<g>?q=[foo]',
                'expected' => '"php@ma;ler=m:ni,p<g>?q=[foo]"',
            ],
        ];
    }
}
