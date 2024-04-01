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
 * Test UTF8 character boundary functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::utf8CharBoundary
 *
 * @todo Add more testcases to properly cover all paths in the method!
 */
final class Utf8CharBoundaryTest extends TestCase
{
    /**
     * Verify that the utf8CharBoundary() returns the correct last character boundary for encoded text.
     *
     * @dataProvider dataUtf8CharBoundary
     *
     * @param string $encodedText UTF-8 QP text to use as input string.
     * @param int    $maxLength   Max length to pass to the function.
     * @param int    $expected    Expected function output.
     */
    public function testUtf8CharBoundary($encodedText, $maxLength, $expected)
    {
        $this->assertSame($expected, $this->Mail->utf8CharBoundary($encodedText, $maxLength));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataUtf8CharBoundary()
    {
        return [
            'Encoded word with multibyte char first byte' => [
                'encodedText' => 'H=E4tten',
                'maxLength'   => 3,
                'expected'    => 1,
            ],
            'Encoded single byte char' => [
                'encodedText' => '=0C',
                'maxLength'   => 3,
                'expected'    => 3,
            ],
            'Encoded word with multi byte char middle byte' => [
                'encodedText' => 'L=C3=B6rem',
                'maxLength'   => 6,
                'expected'    => 1,
            ],
        ];
    }
}
