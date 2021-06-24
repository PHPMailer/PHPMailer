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

use PHPMailer\Test\TestCase;

/**
 * Test UTF8 character boundary functionality.
 */
final class Utf8CharBoundaryTest extends TestCase
{

    public function testEncodedText_utf8CharBoundary_returnsCorrectMaxLength()
    {
        $encodedWordWithMultiByteCharFirstByte = 'H=E4tten';
        $encodedSingleByteCharacter = '=0C';
        $encodedWordWithMultiByteCharMiddletByte = 'L=C3=B6rem';

        $this->assertSame(1, $this->Mail->utf8CharBoundary($encodedWordWithMultiByteCharFirstByte, 3));
        $this->assertSame(3, $this->Mail->utf8CharBoundary($encodedSingleByteCharacter, 3));
        $this->assertSame(1, $this->Mail->utf8CharBoundary($encodedWordWithMultiByteCharMiddletByte, 6));
    }
}
