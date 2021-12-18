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
use PHPMailer\Test\TestCase;

/**
 * Test encoding a string using Q encoding functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::encodeQ
 */
final class EncodeQTest extends TestCase
{
    /**
     * Test encoding a string using Q encoding.
     *
     * @dataProvider dataEncodeQ
     *
     * @param string $input    The text to encode.
     * @param string $expected The expected function return value.
     * @param string $position Optional. Input for the position parameter.
     * @param string $charset  Optional. The charset to use.
     */
    public function testEncodeQ($input, $expected, $position = null, $charset = null)
    {
        if (isset($charset)) {
            $this->Mail->CharSet = $charset;
        }

        if (isset($position)) {
            $result = $this->Mail->encodeQ($input, $position);
        } else {
            $result = $this->Mail->encodeQ($input);
        }

        self::assertSame($expected, $result);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataEncodeQ()
    {
        return [
            'Encode for text; char encoding default (iso88591)' => [
                'input'    => "\xa1Hola! Se\xf1or!",
                'expected' => '=A1Hola!_Se=F1or!',
                'position' => 'text',
            ],
            'Encode for TEXT (uppercase); char encoding default (iso88591)' => [
                'input'    => "\xa1Hola! Se\xf1or!",
                'expected' => '=A1Hola!_Se=F1or!',
                'position' => 'TEXT',
            ],
            'Encode for comment; char encoding default (iso88591)' => [
                'input'    => "\xa1Hola! Se\xf1or!",
                'expected' => '=A1Hola!_Se=F1or!',
                'position' => 'comment',
            ],
            'Encode for Phrase (mixed case); char encoding default (iso88591)' => [
                'input'    => "\xa1Hola! Se\xf1or!",
                'expected' => '=A1Hola!_Se=F1or!',
                'position' => 'Phrase',
            ],
            'Encode for text; char encoding explicit: utf-8' => [
                'input'    => "\xc2\xa1Hola! Se\xc3\xb1or!",
                'expected' => '=C2=A1Hola!_Se=C3=B1or!',
                'position' => 'text',
                'charset'  => PHPMailer::CHARSET_UTF8,
            ],
            'Encode for text; char encoding explicit: utf-8; string contains "=" character' => [
                'input'    => "Nov\xc3\xa1=",
                'expected' => 'Nov=C3=A1=3D',
                'position' => 'text',
                'charset'  => PHPMailer::CHARSET_UTF8,
            ],
            'Encode for text; char encoding default (iso88591); string containing new lines' => [
                'input'    => "\xa1Hola!\r\nSe\xf1or!\r\n",
                'expected' => '=A1Hola!Se=F1or!',
                'position' => 'text',
            ],
            'Encode for text; char encoding explicit: utf-8; phrase vs text regex (text)' => [
                'input'    => "Hello?\xbdWorld\x5e\xa9",
                'expected' => 'Hello=3F=BDWorld^=A9',
                'position' => 'text',
                'charset'  => PHPMailer::CHARSET_UTF8,
            ],
            'Encode for phrase; char encoding explicit: utf-8;  phrase vs text regex (phrase)' => [
                'input'    => "Hello?\xbdWorld\x5e\xa9",
                'expected' => 'Hello=3F=BDWorld=5E=A9',
                'position' => 'phrase',
                'charset'  => PHPMailer::CHARSET_UTF8,
            ],
        ];
    }
}
