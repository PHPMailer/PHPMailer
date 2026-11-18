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
use PHPMailer\Test\SendTestCase;

/**
 * Test DKIM signing functionality.
 *
 * @group dkim
 */
final class DKIMTest extends SendTestCase
{
    /**
     * Whether or not to initialize the PHPMailer object to throw exceptions.
     *
     * @var bool|null
     */
    const USE_EXCEPTIONS = true;

    const PRIVATE_KEY_FILE = 'dkim_private.pem';

    /**
     * Run after each test is completed.
     */
    protected function tear_down()
    {
        if (file_exists(self::PRIVATE_KEY_FILE)) {
            unlink(self::PRIVATE_KEY_FILE);
        }

        parent::tear_down();
    }

    /**
     * DKIM body canonicalization tests.
     *
     * @link https://www.rfc-editor.org/rfc/rfc6376.html#section-3.4.4
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_BodyC
     */
    public function testDKIMBodyCanonicalization()
    {
        // Example from https://www.rfc-editor.org/rfc/rfc6376.html#section-3.4.5.
        $prebody = " C \r\nD \t E\r\n\r\n\r\n";
        $postbody = " C \r\nD \t E\r\n";

        self::assertSame(
            "\r\n",
            $this->Mail->DKIM_BodyC(''),
            'DKIM empty body canonicalization incorrect (Empty body)'
        );
        self::assertSame(
            'frcCV1k9oG9oKj3dpUqdJg1PxRT2RSN/XKdLCPjaYaY=',
            base64_encode(hash('sha256', $this->Mail->DKIM_BodyC(''), true)),
            'DKIM canonicalized empty body hash mismatch'
        );
        self::assertSame($postbody, $this->Mail->DKIM_BodyC($prebody), 'DKIM body canonicalization incorrect');

        //Ensure that non-break trailing whitespace in the body is preserved
        $prebody = " C \r\nD \t E \r\n\r\n\r\n";
        $postbody = " C \r\nD \t E \r\n";
        self::assertSame(
            $postbody,
            $this->Mail->DKIM_BodyC($prebody),
            'DKIM body canonicalization incorrect (trailing WSP)'
        );
    }

    /**
     * DKIM header canonicalization tests.
     *
     * @link https://www.rfc-editor.org/rfc/rfc6376.html#section-3.4.2
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_HeaderC
     */
    public function testDKIMHeaderCanonicalization()
    {
        // Example from https://www.rfc-editor.org/rfc/rfc6376.html#section-3.4.5.
        $preheaders = "A: X\r\nB : Y\t\r\n\tZ  \r\n";
        $postheaders = "a:X\r\nb:Y Z\r\n";
        self::assertSame(
            $postheaders,
            $this->Mail->DKIM_HeaderC($preheaders),
            'DKIM header canonicalization incorrect'
        );

        // Check that long folded lines with runs of spaces are canonicalized properly.
        $preheaders = 'Long-Header-1: <https://example.com/somescript.php?' .
            "id=1234567890&name=Abcdefghijklmnopquestuvwxyz&hash=\r\n abc1234\r\n" .
            "Long-Header-2: This  is  a  long  header  value  that  contains  runs  of  spaces and trailing    \r\n" .
            ' and   is   folded   onto   2   lines';
        $postheaders = 'long-header-1:<https://example.com/somescript.php?id=1234567890&' .
            "name=Abcdefghijklmnopquestuvwxyz&hash= abc1234\r\nlong-header-2:This is a long" .
            ' header value that contains runs of spaces and trailing and is folded onto 2 lines';
        self::assertSame(
            $postheaders,
            $this->Mail->DKIM_HeaderC($preheaders),
            'DKIM header canonicalization of long lines incorrect'
        );
    }

    /**
     * DKIM copied header fields tests.
     *
     * @link https://www.rfc-editor.org/rfc/rfc6376.html#section-3.5
     *
     * @requires extension openssl
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_Add
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_Sign
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_QP
     */
    public function testDKIMOptionalHeaderFieldsCopy()
    {
        $pk = $this->makeKey();
        $this->exportKeyToFile($pk, self::PRIVATE_KEY_FILE);
        $this->Mail->DKIM_private = self::PRIVATE_KEY_FILE;

        // Example from https://www.rfc-editor.org/rfc/rfc6376.html#section-3.5.
        $from = 'from@example.com';
        $to = 'to@example.com';
        $date = 'date';
        $subject = 'example';

        $headerLines = "From:$from\r\nTo:$to\r\nDate:$date\r\n";
        $copyHeaderFields = " z=From:$from\r\n |To:$to\r\n |Date:$date\r\n |Subject:$subject;\r\n";

        $this->Mail->DKIM_copyHeaderFields = true;
        self::assertStringContainsString(
            $copyHeaderFields,
            $this->Mail->DKIM_Add($headerLines, $subject, ''),
            'DKIM header with copied header fields incorrect'
        );

        $this->Mail->DKIM_copyHeaderFields = false;
        self::assertStringNotContainsString(
            $copyHeaderFields,
            $this->Mail->DKIM_Add($headerLines, $subject, ''),
            'DKIM header without copied header fields incorrect'
        );
    }

    /**
     * DKIM signing extra headers tests.
     *
     * @requires extension openssl
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_Add
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_Sign
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_QP
     */
    public function testDKIMExtraHeaders()
    {
        $pk = $this->makeKey();
        $this->exportKeyToFile($pk, self::PRIVATE_KEY_FILE);
        $this->Mail->DKIM_private = self::PRIVATE_KEY_FILE;

        // Example from https://www.rfc-editor.org/rfc/rfc6376.html#section-3.5.
        $from = 'from@example.com';
        $to = 'to@example.com';
        $date = 'date';
        $subject = 'example';
        $anyHeader = 'foo';
        $unsubscribeUrl = '<https://www.example.com/unsubscribe/?newsletterId=anytoken&amp;actionToken=anyToken' .
                            '&otherParam=otherValue&anotherParam=anotherVeryVeryVeryLongValue>';

        $this->Mail->addCustomHeader('X-AnyHeader', $anyHeader);
        $this->Mail->addCustomHeader('Baz', 'bar');
        $this->Mail->addCustomHeader('List-Unsubscribe', $unsubscribeUrl);

        $this->Mail->DKIM_extraHeaders = ['Baz', 'List-Unsubscribe'];

        $headerLines = "From:$from\r\nTo:$to\r\nDate:$date\r\n";
        $headerLines .= "X-AnyHeader:$anyHeader\r\nBaz:bar\r\n";
        $headerLines .= 'List-Unsubscribe:' . $this->Mail->encodeHeader($unsubscribeUrl) . "\r\n";

        $headerFields = 'h=From:To:Date:Baz:List-Unsubscribe:Subject';

        $result = $this->Mail->DKIM_Add($headerLines, $subject, '');

        self::assertStringContainsString($headerFields, $result, 'DKIM header with extra headers incorrect');
    }

    /**
     * DKIM Signing tests.
     *
     * @requires extension openssl
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_Add
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_Sign
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_QP
     */
    public function testDKIMSigningMail()
    {
        $this->Mail->Subject .= ': DKIM signing';
        $this->Mail->Body = 'This message is DKIM signed.';
        $this->buildBody();

        // Make a new key pair.
        // Note: 2048 bits is the recommended minimum key length - gmail won't accept less than 1024 bits.
        $pk = $this->makeKey();
        $this->exportKeyToFile($pk, self::PRIVATE_KEY_FILE);

        $this->Mail->DKIM_domain = 'example.com';
        $this->Mail->DKIM_private = self::PRIVATE_KEY_FILE;
        $this->Mail->DKIM_selector = 'phpmailer';
        $this->Mail->DKIM_passphrase = ''; //key is not encrypted
        self::assertTrue($this->Mail->send(), 'DKIM signed mail failed');

        $this->Mail->isMail();
        self::assertTrue($this->Mail->send(), 'DKIM signed mail via mail() failed');
    }

    /**
     * Verify behaviour of the DKIM_Sign method when Open SSL is not available and exceptions is enabled.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_Sign
     */
    public function testDKIMSignOpenSSLNotAvailableException()
    {
        if (extension_loaded('openssl')) {
            self::markTestSkipped('Test requires OpenSSL *not* to be available');
        }

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Extension missing: openssl');

        $this->Mail->DKIM_Sign('foo');
    }

    /**
     * Check DKIM signatures on messages as the SMTP test server received them.
     * SMTP::data() splits long lines and dot-stuffs messages after they have been signed,
     * so read each one back from the Maildir the server stores it in, and verify it with
     * the public half of the key instead of looking it up in DNS.
     *
     * @dataProvider dataDKIMSignatureSurvivesSMTP
     *
     * @requires extension openssl
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_Add
     * @covers \PHPMailer\PHPMailer\SMTP::data
     *
     * @param callable $setup Sets up the message to send.
     */
    public function testDKIMSignatureSurvivesSMTP($setup)
    {
        if (empty($_REQUEST['mail_mailbox_dir'])) {
            self::markTestSkipped('No Maildir for the SMTP test server; set mail_mailbox_dir in testbootstrap.php');
        }

        $this->Mail->Subject .= ': DKIM signature after SMTP';
        $setup($this->Mail);
        $this->buildBody();

        $pk = $this->makeKey();
        $this->exportKeyToFile($pk, self::PRIVATE_KEY_FILE);

        $this->Mail->DKIM_domain = 'example.com';
        $this->Mail->DKIM_private = self::PRIVATE_KEY_FILE;
        $this->Mail->DKIM_selector = 'phpmailer';
        $this->Mail->DKIM_passphrase = ''; //key is not encrypted
        self::assertTrue($this->Mail->send(), 'DKIM signed mail failed');

        $message = $this->getReceivedMessage($this->Mail->getLastMessageID());
        $details = openssl_pkey_get_details($pk);
        $this->assertDKIMSignature($message, $details['key']);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public static function dataDKIMSignatureSurvivesSMTP()
    {
        return [
            'Plain text' => [
                'setup' => static function (PHPMailer $mail) {
                    // SMTP::data() dot-stuffs the second line.
                    $mail->Body = "This message is DKIM signed.\r\n.This line starts with a dot.";
                },
            ],
            'HTML with AltBody' => [
                'setup' => static function (PHPMailer $mail) {
                    $mail->isHTML(true);
                    $mail->Body = 'This is the <strong>HTML</strong> part of the email.';
                    $mail->AltBody = 'This is the text part of the email.';
                },
            ],
            'Attachment' => [
                'setup' => static function (PHPMailer $mail) {
                    $mail->Body = 'This message has an attachment.';
                    $mail->addAttachment(__FILE__, 'test.txt');
                },
            ],
            'Embedded image' => [
                'setup' => static function (PHPMailer $mail) {
                    $mail->isHTML(true);
                    $mail->Body = 'Embedded Image: <img alt="phpmailer" src="cid:my-attach">';
                    $mail->addEmbeddedImage(
                        realpath(\PHPMAILER_INCLUDE_DIR . '/examples/images/phpmailer.png'),
                        'my-attach'
                    );
                },
            ],
            'UTF-8 subject' => [
                'setup' => static function (PHPMailer $mail) {
                    $mail->CharSet = PHPMailer::CHARSET_UTF8;
                    $mail->Subject .= ' Пустое тело сообщения';
                    $mail->Body = 'This message has a UTF-8 subject.';
                },
            ],
        ];
    }

    /**
     * Find a message in the SMTP test server's Maildir by its Message-ID,
     * allowing the server a moment to store it.
     *
     * @param string $messageId
     *
     * @return string
     */
    private function getReceivedMessage($messageId)
    {
        for ($i = 0; $i < 50; ++$i) {
            foreach (glob($_REQUEST['mail_mailbox_dir'] . '/new/*') as $file) {
                $message = file_get_contents($file);
                if (strpos($message, $messageId) !== false) {
                    return $message;
                }
            }
            usleep(100000);
        }

        self::fail('Message ' . $messageId . ' not found in ' . $_REQUEST['mail_mailbox_dir']);
    }

    /**
     * Verify the DKIM signature of a received message with the signer's public key.
     *
     * @param string $message   The message as stored by the SMTP test server.
     * @param string $publicKey The public key in PEM format.
     */
    private function assertDKIMSignature($message, $publicKey)
    {
        // badsmtp 0.3.3 stores the message with LF line breaks, after its own From, To and Received headers
        // and a blank line, so convert the line breaks back to CRLF (they can't be checked here), and start
        // from the header block that holds the DKIM-Signature field.
        $message = PHPMailer::normalizeBreaks($message, "\r\n");
        $blankLine = strrpos(strstr($message, "\r\nDKIM-Signature:", true), "\r\n\r\n");
        if ($blankLine !== false) {
            $message = substr($message, $blankLine + 4);
        }
        list($header, $body) = explode("\r\n\r\n", $message, 2);
        // Index the header fields by lower-case name, keeping folded lines with their field.
        $fields = [];
        foreach (preg_split('/\r\n(?![ \t])/', $header) as $field) {
            $fields[strtolower(strstr($field, ':', true))] = $field;
        }
        preg_match_all('/(\w+)=([^;]*)/', $fields['dkim-signature'], $tags);
        $tags = array_combine($tags[1], preg_replace('/\s+/', '', $tags[2]));

        self::assertSame(
            $tags['bh'],
            base64_encode(hash('sha256', $this->Mail->DKIM_BodyC($body), true)),
            'DKIM body hash mismatch'
        );

        $signedFields = [];
        foreach (explode(':', $tags['h']) as $name) {
            $signedFields[] = $fields[strtolower($name)];
        }
        // The signature also covers the DKIM-Signature field itself, with an empty b= tag.
        $signedFields[] = preg_replace('/(;\s*b=)[^;]*/', '$1', $fields['dkim-signature']);
        self::assertSame(
            1,
            openssl_verify(
                $this->Mail->DKIM_HeaderC(implode("\r\n", $signedFields)),
                base64_decode($tags['b']),
                $publicKey,
                'sha256WithRSAEncryption'
            ),
            'DKIM signature verification failed'
        );
    }
}
