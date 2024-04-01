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
 * Test adding embedded image(s) functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::addEmbeddedImage
 * @covers \PHPMailer\PHPMailer\PHPMailer::createBody
 * @covers \PHPMailer\PHPMailer\PHPMailer::getAttachments
 * @covers \PHPMailer\PHPMailer\PHPMailer::inlineImageExists
 */
final class AddEmbeddedImageTest extends PreSendTestCase
{
    /**
     * Test successfully adding an embedded image.
     */
    public function testAddEmbeddedImage()
    {
        $pathToFile = realpath(\PHPMAILER_INCLUDE_DIR . '/examples/images/phpmailer.png');
        $expected   = [
            0 => $pathToFile,
            1 => 'phpmailer.png',
            2 => 'phpmailer.png',
            3 => 'base64',
            4 => 'image/png',
            5 => false,
            6 => 'inline',
            7 => 'my-attach',
        ];

        $this->Mail->Body = 'Embedded Image: <img alt="phpmailer" src="' .
            'cid:my-attach">' .
            'Here is an image!';
        $this->Mail->Subject .= ': Embedded Image';
        $this->Mail->isHTML(true);

        // Test attaching the image.
        $result = $this->Mail->addEmbeddedImage(
            $pathToFile,
            'my-attach',
            'phpmailer.png',
            'base64',
            'image/png'
        );

        self::assertTrue($result, $this->Mail->ErrorInfo);
        self::assertTrue($this->Mail->inlineImageExists(), 'Embedded image not present in attachments array');

        $attachments = $this->Mail->getAttachments();
        self::assertIsArray($attachments, 'Attachments is not an array');
        self::assertArrayHasKey(0, $attachments, 'Attachments does not have the expected array entry');
        self::assertSame($expected, $attachments[0], 'Attachment info does not match the expected array');

        // Test that the image was correctly added to the message body.
        $this->buildBody();
        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);

        $sendMessage = $this->Mail->getSentMIMEMessage();
        $LE          = PHPMailer::getLE();

        self::assertStringContainsString(
            'Content-Type: image/png; name=phpmailer.png' . $LE,
            $sendMessage,
            'Embedded image header content type incorrect.'
        );

        self::assertStringContainsString(
            'Content-ID: <my-attach>' . $LE,
            $sendMessage,
            'Embedded image header content ID incorrect.'
        );

        self::assertStringContainsString(
            'Content-Disposition: inline; filename=phpmailer.png' . $LE,
            $sendMessage,
            'Embedded image header content disposition incorrect.'
        );
    }

    /**
     * Test adding an image without explicitly adding a name for the image will set the name as the existing file name.
     */
    public function testAddingImageWithoutExplicitName()
    {
        $result = $this->Mail->addEmbeddedImage(__FILE__, '123');
        self::assertTrue($result, 'File failed to attach');

        self::assertTrue($this->Mail->inlineImageExists(), 'Inline image not present in attachments array');

        $attachments = $this->Mail->getAttachments();
        self::assertIsArray($attachments, 'Attachments is not an array');
        self::assertArrayHasKey(0, $attachments, 'Attachments does not have the expected array entry');
        self::assertSame($attachments[0][1], $attachments[0][2], 'Name is not the same as filename');
    }

    /**
     * Test that embedding an image fails in select use cases.
     *
     * @dataProvider dataFailToAttach
     *
     * @param string $path             Path to the attachment.
     * @param string $cid              Content ID for the attachment.
     * @param string $exceptionMessage Unused in this test.
     * @param string $name             Optional. Attachment name to use.
     * @param string $encoding         Optional. File encoding to pass.
     */
    public function testFailToAttach($path, $cid, $exceptionMessage, $name = '', $encoding = PHPMailer::ENCODING_BASE64)
    {
        $result = $this->Mail->addEmbeddedImage($path, $cid, $name, $encoding);
        self::assertFalse($result, 'Image did not fail to attach');

        self::assertFalse($this->Mail->inlineImageExists(), 'Inline image present in attachments array');
    }

    /**
     * Test that embedding an image throws an exception in select use cases.
     *
     * @dataProvider dataFailToAttach
     *
     * @param string $path             Path to the attachment.
     * @param string $cid              Content ID for the attachment.
     * @param string $exceptionMessage The exception message to expect.
     * @param string $name             Optional. Attachment name to use.
     * @param string $encoding         Optional. File encoding to pass.
     */
    public function testFailToAttachException(
        $path,
        $cid,
        $exceptionMessage,
        $name = '',
        $encoding = PHPMailer::ENCODING_BASE64
    ) {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($exceptionMessage);

        $mail = new PHPMailer(true);
        $mail->addEmbeddedImage($path, $cid, $name, $encoding);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataFailToAttach()
    {
        return [
            'Invalid: non-existent file' => [
                'path'             => 'thisfiledoesntexist',
                'cid'              => 'xyz',
                'exceptionMessage' => 'Could not access file: thisfiledoesntexist',
            ],
            'Invalid: invalid encoding' => [
                'path'             => __FILE__,
                'cid'              => 'cid',
                'exceptionMessage' => 'Unknown encoding: invalidencoding',
                'name'             => 'test.png',
                'encoding'         => 'invalidencoding',
            ],
        ];
    }
}
