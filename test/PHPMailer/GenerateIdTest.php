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
use PHPMailer\Test\PreSendTestCase;

/**
 * Test unique ID generation functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::generateId
 */
final class GenerateIdTest extends PreSendTestCase
{

    /**
     * Test generating a unique ID.
     *
     * To fully test the method under test, the tests should be run on the following system configurations:
     * - PHP >= 7.0 to hit `random_bytes()`;
     * - PHP < 7.0 with OpenSSL enabled to hit `openssl_random_pseudo_bytes()`;
     * - PHP < 7.0 with the OpenSSL extension disabled;
     * - PHP >= 7.0 with the OpenSSL extension disabled and the `random_bytes()` function
     *   in the php.ini `disable_functions` list.
     *
     * Note: The exact text string length of result may vary due to the str_replace() in the final statement
     * of the method, but it should always be at least 32 characters long.
     */
    public function testGenerateID()
    {
        $this->Mail->Body = 'Testing 1, 2, 3';
        $this->Mail->isHTML();
        $this->Mail->AltBody = $this->Mail->Body;
        $this->buildBody();
        $this->Mail->preSend();
        $message = $this->Mail->getSentMIMEMessage();

        // Find the generated ID in the message.
        self::assertSame(
            1,
            preg_match(
                '`Content-Type: multipart/alternative;\s+boundary="(b[1-3]_[A-Za-z0-9]{32,})"`',
                $message,
                $matches
            ),
            'Boundary identifier header line not found in message'
        );

        // Check that the generated ID is used in at least one boundary.
        $LE = PHPMailer::getLE();
        self::assertStringContainsString(
            $LE . '--' . $matches[1] . $LE,
            $message,
            'No boundaries using the generated ID found in message'
        );
    }
}
