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

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\Test\PreSendTestCase;

/**
 * Test adding string attachments functionality.
 */
final class AddStringAttachmentTest extends PreSendTestCase
{

    /**
     * Test successfully adding a simple plain string attachment.
     */
    public function testAddPlainStringAttachment()
    {
        $sAttachment = 'These characters are the content of the ' .
            "string attachment.\nThis might be taken from a " .
            'database or some other such thing. ';

        $expected   = [
            0 => $sAttachment,
            1 => 'string_attach.txt',
            2 => 'string_attach.txt',
            3 => 'base64',
            4 => 'text/plain',
            5 => true,
            6 => 'attachment',
            7 => 0,
        ];

        $this->Mail->Body = 'Here is the text body';
        $this->Mail->Subject .= ': Plain + StringAttachment';

        // Test attaching the plain string attachment.
        $result = $this->Mail->addStringAttachment($sAttachment, 'string_attach.txt');

        self::assertTrue($result, $this->Mail->ErrorInfo);
        self::assertTrue($this->Mail->attachmentExists(), 'Plain text attachment not present in attachments array');

        $attachments = $this->Mail->getAttachments();
        self::assertIsArray($attachments, 'Attachments is not an array');
        self::assertArrayHasKey(0, $attachments, 'Attachments does not have the expected array entry');
        self::assertSame($expected, $attachments[0], 'Attachment info does not match the expected array');

        // Test that the plain text attachment was correctly added to the message body.
        $this->buildBody();
        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);

        $sendMessage = $this->Mail->getSentMIMEMessage();
        $LE          = PHPMailer::getLE();

        self::assertStringContainsString(
            'Content-Type: text/plain; name=string_attach.txt' . $LE,
            $sendMessage,
            'Embedded image header content type incorrect.'
        );

        self::assertStringNotContainsString(
            'Content-ID: ' . $LE,
            $sendMessage,
            'Embedded image header content ID not empty.'
        );

        self::assertStringContainsString(
            'Content-Disposition: attachment; filename=string_attach.txt' . $LE,
            $sendMessage,
            'Embedded image header content disposition incorrect.'
        );
    }

    /**
     * Expect exceptions on bad encoding
     */
    public function testStringAttachmentEncodingException()
    {
        $this->expectException(Exception::class);

        $mail = new PHPMailer(true);
        $mail->addStringAttachment('hello', 'test.txt', 'invalidencoding');
    }
}
