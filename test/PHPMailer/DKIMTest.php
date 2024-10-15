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
        $pk = openssl_pkey_new(
            [
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]
        );
        openssl_pkey_export_to_file($pk, self::PRIVATE_KEY_FILE);
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
        $pk = openssl_pkey_new(
            [
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]
        );
        openssl_pkey_export_to_file($pk, self::PRIVATE_KEY_FILE);
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
        $pk = openssl_pkey_new(
            [
                'private_key_bits' => 2048,
                'private_key_type' => OPENSSL_KEYTYPE_RSA,
            ]
        );
        openssl_pkey_export_to_file($pk, self::PRIVATE_KEY_FILE);

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
            $this->markTestSkipped('Test requires OpenSSL *not* to be available');
        }

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Extension missing: openssl');

        $this->Mail->DKIM_Sign('foo');
    }
}
