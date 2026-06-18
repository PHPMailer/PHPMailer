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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\Test\PreSendTestCase;

/**
 * Test how the Encoding property is handled while building/sending body content.
 */
final class BodyEncodingTest extends PreSendTestCase
{
    /**
     * For plain ASCII content, requested 8bit encoding should be downgraded to 7bit.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::createBody
     */
    public function testEncodingDowngradesTo7BitForAsciiBody()
    {
        $this->Mail->Body = 'Only ASCII body content.';
        $this->Mail->Encoding = PHPMailer::ENCODING_8BIT;
        $this->buildBody();

        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);
        self::assertSame(PHPMailer::ENCODING_7BIT, $this->Mail->Encoding);
    }

    /**
     * For body content containing 8-bit characters, 8bit encoding should be retained.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::createBody
     */
    public function testEncodingRetains8BitForNonAsciiBody()
    {
        $this->Mail->Body = 'Body with 8-bit char: café';
        $this->Mail->Encoding = PHPMailer::ENCODING_8BIT;
        $this->buildBody();

        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);
        self::assertSame(PHPMailer::ENCODING_8BIT, $this->Mail->Encoding);
        self::assertStringContainsString(
            'Content-Transfer-Encoding: 8bit',
            $this->Mail->getSentMIMEMessage(),
            'Expected 8bit transfer encoding header not found'
        );
    }

    /**
     * Lines longer than RFC limits should force quoted-printable body encoding.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::createBody
     * @covers \PHPMailer\PHPMailer\PHPMailer::hasLineLongerThanMax
     */
    public function testLongLinesForceQuotedPrintableEncoding()
    {
        $this->Mail->Body = str_repeat('A', PHPMailer::MAX_LINE_LENGTH + 5);
        $this->Mail->Encoding = PHPMailer::ENCODING_8BIT;
        $this->buildBody();

        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);
        self::assertSame(PHPMailer::ENCODING_QUOTED_PRINTABLE, $this->Mail->Encoding);
        self::assertStringContainsString(
            'Content-Transfer-Encoding: quoted-printable',
            $this->Mail->getSentMIMEMessage(),
            'Long line did not cause quoted-printable override'
        );
    }

    /**
     * Invalid Encoding property values should fail during preSend.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::createHeader
     * @covers \PHPMailer\PHPMailer\PHPMailer::validateEncoding
     */
    public function testInvalidEncodingFailsPreSend()
    {
        $this->Mail->Body = 'Body for invalid encoding test.';
        $this->Mail->Encoding = 'invalidencoding';
        $this->buildBody();

        self::assertFalse($this->Mail->preSend(), 'Invalid encoding unexpectedly accepted');
        self::assertSame('Unknown encoding: invalidencoding', $this->Mail->ErrorInfo);
    }
}
