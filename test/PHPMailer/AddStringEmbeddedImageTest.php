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
 * Test adding stringified attachments functionality.
 */
final class AddStringEmbeddedImageTest extends PreSendTestCase
{

    /**
     * Test successfully adding a stingified embedded image without a name.
     */
    public function testHtmlStringEmbedNoName()
    {
        $this->Mail->Body = 'This is the <strong>HTML</strong> part of the email.';
        $this->Mail->Subject .= ': HTML + unnamed embedded image';
        $this->Mail->isHTML(true);

        $result = $this->Mail->addStringEmbeddedImage(
            file_get_contents(realpath(\PHPMAILER_INCLUDE_DIR . '/examples/images/phpmailer_mini.png')),
            hash('sha256', 'phpmailer_mini.png') . '@phpmailer.0',
            '', // Intentionally empty name.
            'base64',
            '', // Intentionally empty MIME type.
            'inline'
        );

        self::assertTrue($result, $this->Mail->ErrorInfo);

        $this->buildBody();
        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);
    }

    /**
     * Expect exceptions on bad encoding
     */
    public function testStringEmbeddedImageEncodingException()
    {
        $this->expectException(Exception::class);

        $mail = new PHPMailer(true);
        $mail->addStringEmbeddedImage('hello', 'cid', 'test.png', 'invalidencoding');
    }
}
