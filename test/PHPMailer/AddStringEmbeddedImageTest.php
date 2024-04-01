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

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\Test\PreSendTestCase;

/**
 * Test adding stringified attachments functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::addStringEmbeddedImage
 * @covers \PHPMailer\PHPMailer\PHPMailer::getAttachments
 * @covers \PHPMailer\PHPMailer\PHPMailer::inlineImageExists
 */
final class AddStringEmbeddedImageTest extends PreSendTestCase
{
    /**
     * Test successfully adding a stingified embedded image without a name.
     */
    public function testHtmlStringEmbedNoName()
    {
        $attachmentFile   = realpath(\PHPMAILER_INCLUDE_DIR . '/examples/images/phpmailer_mini.png');
        $attachmentString = file_get_contents($attachmentFile);
        $cid              = hash('sha256', 'phpmailer_mini.png') . '@phpmailer.0';

        $expected = [
            0 => $attachmentString,
            1 => '',
            2 => '',
            3 => 'base64',
            4 => '',
            5 => true,
            6 => 'inline',
            7 => $cid,
        ];

        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->Subject .= ': HTML + unnamed embedded image';
        $this->Mail->isHTML(true);

        $result = $this->Mail->addStringEmbeddedImage(
            $attachmentString,
            $cid,
            '', // Intentionally empty name.
            'base64',
            '', // Intentionally empty MIME type.
            'inline'
        );

        self::assertTrue($result, $this->Mail->ErrorInfo);
        self::assertTrue($this->Mail->inlineImageExists(), 'Inline image not present in attachments array');

        $attachments = $this->Mail->getAttachments();
        self::assertIsArray($attachments, 'Attachments is not an array');
        self::assertArrayHasKey(0, $attachments, 'Attachments does not have the expected array entry');
        self::assertSame($expected, $attachments[0], 'Attachment info does not match the expected array');

        $this->buildBody();
        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);

        $sendMessage = $this->Mail->getSentMIMEMessage();
        $LE          = PHPMailer::getLE();

        self::assertStringContainsString(
            'Content-Type: ' . $LE,
            $sendMessage,
            'Embedded image header content type incorrect.'
        );

        self::assertStringContainsString(
            'Content-ID: <' . $cid . '>' . $LE,
            $sendMessage,
            'Embedded image header encoding incorrect.'
        );

        self::assertStringContainsString(
            'Content-Disposition: inline' . $LE,
            $sendMessage,
            'Embedded image header content disposition incorrect.'
        );
    }

    /**
     * Test that embedding a stringified attachment fails in select use cases.
     *
     * @dataProvider dataFailToAttach
     *
     * @param string $string           The attachment binary data.
     * @param string $cid              Content ID for the attachment.
     * @param string $exceptionMessage Unused in this test.
     * @param string $name             Optional. Attachment name to use.
     * @param string $encoding         Optional. File encoding to pass.
     */
    public function testFailToAttach(
        $string,
        $cid,
        $exceptionMessage,
        $name = '',
        $encoding = PHPMailer::ENCODING_BASE64
    ) {
        $result = $this->Mail->addStringEmbeddedImage($string, $cid, $name, $encoding);
        self::assertFalse($result, 'Stringified attachment did not fail to attach');

        self::assertFalse($this->Mail->inlineImageExists(), 'Stringified attachment present in attachments array');
    }

    /**
     * Test that embedding a stringified attachment throws an exception in select use cases.
     *
     * @dataProvider dataFailToAttach
     *
     * @param string $string           The attachment binary data.
     * @param string $cid              Content ID for the attachment.
     * @param string $exceptionMessage The exception message to expect.
     * @param string $name             Optional. Attachment name to use.
     * @param string $encoding         Optional. File encoding to pass.
     */
    public function testFailToAttachException(
        $string,
        $cid,
        $exceptionMessage,
        $name = '',
        $encoding = PHPMailer::ENCODING_BASE64
    ) {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($exceptionMessage);

        $mail = new PHPMailer(true);
        $mail->addStringEmbeddedImage($string, $cid, $name, $encoding);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataFailToAttach()
    {
        return [
            'Invalid: invalid encoding' => [
                'string'           => 'hello',
                'cid'              => 'cid',
                'exceptionMessage' => 'Unknown encoding: invalidencoding',
                'name'             => 'test.png',
                'encoding'         => 'invalidencoding',
            ],
        ];
    }
}
