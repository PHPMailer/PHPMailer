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
 * Test string encoding functionality.
 */
final class EncodeStringTest extends TestCase
{

    /**
     * Encoding and charset tests.
     */
    public function testEncodings()
    {
        self::assertSame(
            'hello',
            $this->Mail->encodeString('hello', 'binary'),
            'Binary encoding changed input'
        );
        $this->Mail->ErrorInfo = '';
        $this->Mail->encodeString('hello', 'asdfghjkl');
        self::assertNotEmpty($this->Mail->ErrorInfo, 'Invalid encoding not detected');
        self::assertSame(
            base64_encode('hello') . PHPMailer::getLE(),
            $this->Mail->encodeString('hello')
        );
    }
}
