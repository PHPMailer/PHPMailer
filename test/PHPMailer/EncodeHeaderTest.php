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

use PHPMailer\Test\TestCase;

/**
 * Test the charset label chosen when encoding header values.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::encodeHeader
 */
final class EncodeHeaderTest extends TestCase
{
    /**
     * Test the charset label used for 7-bit header values.
     *
     * @dataProvider dataEncodeHeaderCharset
     *
     * @param string $input    The header value to encode.
     * @param string $expected The expected function return value.
     * @param string $charset  The charset to use.
     */
    public function testEncodeHeaderCharset($input, $expected, $charset)
    {
        $this->Mail->CharSet = $charset;

        self::assertSame($expected, $this->Mail->encodeHeader($input));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public static function dataEncodeHeaderCharset()
    {
        return [
            'ISO-2022-JP text keeps its charset label even though it is 7-bit' => [
                //"Test" in Japanese katakana, converted to ISO-2022-JP
                'input'    => "\x1B\$B%F%9%H\x1B(B",
                'expected' => '=?ISO-2022-JP?Q?=1B$B%F%9%H=1B(B?=',
                'charset'  => 'ISO-2022-JP',
            ],
            'ISO-2022-JP text in the middle of ASCII text keeps its charset label' => [
                'input'    => "Re: \x1B\$B%F%9%H\x1B(B",
                'expected' => '=?ISO-2022-JP?Q?Re:_=1B$B%F%9%H=1B(B?=',
                'charset'  => 'ISO-2022-JP',
            ],
            'Plain ASCII text with an ISO-2022-JP charset is not encoded' => [
                'input'    => 'Test',
                'expected' => 'Test',
                'charset'  => 'ISO-2022-JP',
            ],
            'ASCII text with a control character still falls back to us-ascii' => [
                'input'    => "Joe\x7FBloggs",
                'expected' => '=?us-ascii?Q?Joe=7FBloggs?=',
                'charset'  => 'utf-8',
            ],
        ];
    }
}
