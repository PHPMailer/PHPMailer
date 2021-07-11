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
 * Test line length detection and handling.
 */
final class HasLineLongerThanMaxTest extends PreSendTestCase
{

    /**
     * Test constructing a multipart message that contains lines that are too long for RFC compliance.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::hasLineLongerThanMax
     */
    public function testLongBody()
    {
        $oklen = str_repeat(str_repeat('0', PHPMailer::MAX_LINE_LENGTH) . PHPMailer::getLE(), 2);
        // Use +2 to ensure line length is over limit - LE may only be 1 char.
        $badlen = str_repeat(str_repeat('1', PHPMailer::MAX_LINE_LENGTH + 2) . PHPMailer::getLE(), 2);

        $this->Mail->Body = 'This message contains lines that are too long.' .
            PHPMailer::getLE() . $oklen . $badlen . $oklen;
        self::assertTrue(
            PHPMailer::hasLineLongerThanMax($this->Mail->Body),
            'Test content does not contain long lines!'
        );

        $this->Mail->isHTML();
        $this->buildBody();
        $this->Mail->AltBody = $this->Mail->Body;
        $this->Mail->Encoding = '8bit';
        $this->Mail->preSend();
        $message = $this->Mail->getSentMIMEMessage();
        self::assertFalse(
            PHPMailer::hasLineLongerThanMax($message),
            'Long line not corrected (Max: ' . (PHPMailer::MAX_LINE_LENGTH + strlen(PHPMailer::getLE())) . ' chars)'
        );
        self::assertStringContainsString(
            'Content-Transfer-Encoding: quoted-printable',
            $message,
            'Long line did not cause transfer encoding switch.'
        );
    }

    /**
     * Test constructing a message that does NOT contain lines that are too long for RFC compliance.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::hasLineLongerThanMax
     */
    public function testShortBody()
    {
        $oklen = str_repeat(str_repeat('0', PHPMailer::MAX_LINE_LENGTH) . PHPMailer::getLE(), 10);

        $this->Mail->Body = 'This message does not contain lines that are too long.' .
            PHPMailer::getLE() . $oklen;
        self::assertFalse(
            PHPMailer::hasLineLongerThanMax($this->Mail->Body),
            'Test content contains long lines!'
        );

        $this->buildBody();
        $this->Mail->Encoding = '8bit';
        $this->Mail->preSend();
        $message = $this->Mail->getSentMIMEMessage();
        self::assertFalse(PHPMailer::hasLineLongerThanMax($message), 'Long line not corrected.');
        self::assertStringNotContainsString(
            'Content-Transfer-Encoding: quoted-printable',
            $message,
            'Short line caused transfer encoding switch.'
        );
    }

    /**
     * Test line length detection.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::createBody
     * @covers \PHPMailer\PHPMailer\PHPMailer::hasLineLongerThanMax
     */
    public function testLineLength()
    {
        // May have been altered by earlier tests, can interfere with line break format.
        $this->Mail->isSMTP();
        $this->Mail->preSend();
        $oklen = str_repeat(str_repeat('0', PHPMailer::MAX_LINE_LENGTH) . "\r\n", 2);
        $badlen = str_repeat(str_repeat('1', PHPMailer::MAX_LINE_LENGTH + 1) . "\r\n", 2);
        self::assertTrue(PHPMailer::hasLineLongerThanMax($badlen), 'Long line not detected (only)');
        self::assertTrue(PHPMailer::hasLineLongerThanMax($oklen . $badlen), 'Long line not detected (first)');
        self::assertTrue(PHPMailer::hasLineLongerThanMax($badlen . $oklen), 'Long line not detected (last)');
        self::assertTrue(
            PHPMailer::hasLineLongerThanMax($oklen . $badlen . $oklen),
            'Long line not detected (middle)'
        );
        self::assertFalse(PHPMailer::hasLineLongerThanMax($oklen), 'Long line false positive');

        $this->Mail->isHTML(false);
        $this->Mail->Subject .= ': Line length test';
        $this->Mail->CharSet = 'UTF-8';
        $this->Mail->Encoding = '8bit';
        $this->Mail->Body = $oklen . $badlen . $oklen . $badlen;
        $this->buildBody();
        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);
        self::assertSame('quoted-printable', $this->Mail->Encoding, 'Long line did not override transfer encoding');
    }
}
