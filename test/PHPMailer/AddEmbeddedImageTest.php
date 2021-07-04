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
 * Test adding embedded image(s) functionality.
 */
final class AddEmbeddedImageTest extends PreSendTestCase
{

    /**
     * Test successfully adding an embedded image.
     */
    public function testAddEmbeddedImage()
    {
        $this->Mail->Body = 'Embedded Image: <img alt="phpmailer" src="' .
            'cid:my-attach">' .
            'Here is an image!';
        $this->Mail->Subject .= ': Embedded Image';
        $this->Mail->isHTML(true);

        $result = $this->Mail->addEmbeddedImage(
            realpath(\PHPMAILER_INCLUDE_DIR . '/examples/images/phpmailer.png'),
            'my-attach',
            'phpmailer.png',
            'base64',
            'image/png'
        );

        self::assertTrue($result, $this->Mail->ErrorInfo);

        $this->buildBody();
        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);
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
     * @param string $path     Path to the attachment.
     * @param string $cid      Content ID for the attachment.
     * @param string $name     Optional. Attachment name to use.
     * @param string $encoding Optional. File encoding to pass.
     */
    public function testFailToAttach($path, $cid, $name = '', $encoding = PHPMailer::ENCODING_BASE64)
    {
        $result = $this->Mail->addEmbeddedImage($path, $cid, $name, $encoding);
        self::assertFalse($result, 'Image did not fail to attach');

        self::assertFalse($this->Mail->inlineImageExists(), 'Inline image present in attachments array');
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
                'path' => 'thisfiledoesntexist',
                'cid'  => 'xyz',
            ],
        ];
    }

    /**
     * Expect exceptions on bad encoding
     */
    public function testEmbeddedImageEncodingException()
    {
        $this->expectException(Exception::class);

        $mail = new PHPMailer(true);
        $mail->addEmbeddedImage(__FILE__, 'cid', 'test.png', 'invalidencoding');
    }
}
