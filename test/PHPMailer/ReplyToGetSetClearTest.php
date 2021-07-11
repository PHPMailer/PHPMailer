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
 * Test reply-to address setting, getting and clearing functionality.
 */
final class ReplyToGetSetClearTest extends PreSendTestCase
{

    /**
     * Test adding a non-IDN reply-to address.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::addReplyTo
     * @covers \PHPMailer\PHPMailer\PHPMailer::addOrEnqueueAnAddress
     * @covers \PHPMailer\PHPMailer\PHPMailer::addAnAddress
     * @covers \PHPMailer\PHPMailer\PHPMailer::getReplyToAddresses
     *
     * @dataProvider dataAddReplyToValidAddressNonIdn
     *
     * @param string $address  The email address to set.
     * @param string $name     Optional. The name to set.
     * @param string $expected Optional. The email address and name as they are expected to be set.
     *                         Only needs to be passed if different than the original inputs.
     */
    public function testAddReplyToValidAddressNonIdn($address, $name = null, $expected = null)
    {
        if (isset($name)) {
            $result = $this->Mail->addReplyTo($address, $name);
        } else {
            $result = $this->Mail->addReplyTo($address);
            $name   = '';
        }

        if (isset($expected) === false) {
            $expected = [
                'key'     => $address,
                'address' => $address,
                'name'    => $name,
            ];
        }

        // Test the setting is successful.
        self::assertTrue($result, 'Replyto Addressing failed');

        // Verify that the address was correctly added to the array.
        $retrieved = $this->Mail->getReplyToAddresses();
        self::assertIsArray($retrieved, 'ReplyTo property is not an array');
        self::assertCount(1, $retrieved, 'ReplyTo property does not contain exactly one address');

        $key = $expected['key'];
        self::assertArrayHasKey(
            $key,
            $retrieved,
            'ReplyTo property does not contain an entry with this address as the key'
        );
        self::assertCount(
            2,
            $retrieved[$key],
            'ReplyTo array for this address does not contain exactly two array items'
        );
        self::assertSame(
            $expected['address'],
            $retrieved[$key][0],
            'ReplyTo array for this address does not contain added address'
        );
        self::assertSame(
            $expected['name'],
            $retrieved[$key][1],
            'ReplyTo array for this address does not contain added name'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataAddReplyToValidAddressNonIdn()
    {
        return [
            'Valid address' => [
                'address' => 'a@example.com',
            ],
            'Valid address with surrounding whitespace and mixed case' => [
                'address' => " \tMiXeD@Example.Com  \r\n",
                'name'    => null,
                'expected' => [
                    'key'     => 'mixed@example.com',
                    'address' => 'MiXeD@Example.Com',
                    'name'    => '',
                ],
            ],
            'Valid address with name' => [
                'address' => 'a@example.com',
                'name'    => 'ReplyTo name',
            ],
            'Valid address with name; name with whitespace and line breaks' => [
                'address'  => 'a@example.com',
                'name'     => "\t\t  ReplyTo\r\nname  ",
                'expected' => [
                    'key'     => 'a@example.com',
                    'address' => 'a@example.com',
                    'name'    => 'ReplyToname',
                ],
            ],
        ];
    }

    /**
     * Test adding an invalid non-IDN reply-to address.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::addOrEnqueueAnAddress
     * @covers \PHPMailer\PHPMailer\PHPMailer::addAnAddress
     *
     * @dataProvider dataAddReplyToInvalidAddressNonIdn
     *
     * @param string $address The email address to set.
     */
    public function testAddReplyToInvalidAddressNonIdn($address)
    {
        // Test the setting fails.
        $result = $this->Mail->addReplyTo($address);
        self::assertFalse($result, 'Invalid Replyto address accepted');

        // Verify that the address was not added to the array.
        $retrieved = $this->Mail->getReplyToAddresses();
        self::assertIsArray($retrieved, 'ReplyTo property is not an array');
        self::assertCount(0, $retrieved, 'ReplyTo property is not empty');
    }

    /**
     * Test receiving an excepting when adding an invalid non-IDN reply-to address.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::addOrEnqueueAnAddress
     * @covers \PHPMailer\PHPMailer\PHPMailer::addAnAddress
     *
     * @dataProvider dataAddReplyToInvalidAddressNonIdn
     *
     * @param string $address The email address to set.
     */
    public function testAddReplyToInvalidAddressNonIdnException($address)
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid address:  (Reply-To): ' . $address);

        $mail = new PHPMailer(true);
        $mail->addReplyTo($address);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataAddReplyToInvalidAddressNonIdn()
    {
        return [
            'Invalid domain' => ['a@example..com'],
            'Missing @ sign' => ['example.com'],
        ];
    }

    /**
     * Test that the correct Reply-To message header has been added to the message.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::addReplyTo
     * @covers \PHPMailer\PHPMailer\PHPMailer::createHeader
     * @covers \PHPMailer\PHPMailer\PHPMailer::addrAppend
     *
     * @dataProvider dataReplyToInMessageHeader
     *
     * @param string $addresses The email address(es) to set for Reply-To.
     * @param string $expected  The expected message header.
     */
    public function testReplyToInMessageHeader($addresses, $expected)
    {
        $this->Mail->Body = 'Here is the main body.  There should be ' .
            'a reply to header in this message.';
        $this->Mail->Subject .= ': Reply to header';

        foreach ($addresses as $address) {
            if (isset($address['name'])) {
                $this->Mail->addReplyTo($address['address'], $address['name']);
            } else {
                $this->Mail->addReplyTo($address['address']);
            }
        }

        $this->buildBody();
        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);

        $message = $this->Mail->getSentMIMEMessage();
        self::assertStringContainsString($expected, $message, 'Message does not contain the expected reply-to header');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataReplyToInMessageHeader()
    {
        $LE = PHPMailer::getLE();

        return [
            'Single address' => [
                'addresses' => [
                    [
                        'address' => 'nobody@nobody.com',
                    ],
                ],
                'expected'  => $LE . 'Reply-To: nobody@nobody.com' . $LE,
            ],
            'Single address + name' => [
                'addresses' => [
                    [
                        'address' => 'nobody@nobody.com',
                        'name'    => 'Nobody (Unit Test)',
                    ],
                ],
                'expected'  => $LE . 'Reply-To: "Nobody (Unit Test)" <nobody@nobody.com>' . $LE,
            ],
            'Multiple addresses, including no name and mixed case email' => [
                'addresses' => [
                    [
                        'address' => 'nobody@nobody.com',
                        'name'    => 'Nobody (Unit Test)',
                    ],
                    [
                        'address' => 'Somebody@SomeBody.com',
                        'name'    => 'Somebody (Unit Test)',
                    ],
                    [
                        'address' => 'noname@noname.com',
                    ],
                ],
                'expected'  => $LE . 'Reply-To: "Nobody (Unit Test)" <nobody@nobody.com>,'
                    . ' "Somebody (Unit Test)" <Somebody@SomeBody.com>, noname@noname.com' . $LE,
            ],
        ];
    }

    /**
     * Tests handling of IDN reply-to addresses.
     *
     * Verifies that:
     * - CharSet and Unicode -> ASCII conversions for addresses with IDN gets executed correctly.
     * - IDN addresses initially get enqueued.
     * - IDN addresses correctly get added during `preSend()`.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::addReplyTo
     * @covers \PHPMailer\PHPMailer\PHPMailer::addOrEnqueueAnAddress
     * @covers \PHPMailer\PHPMailer\PHPMailer::addAnAddress
     * @covers \PHPMailer\PHPMailer\PHPMailer::preSend
     *
     * @requires extension mbstring
     * @requires function idn_to_ascii
     */
    public function testEnqueueAndAddIdnAddress()
    {
        // This file is UTF-8 encoded. Create a domain encoded in "iso-8859-1".
        $letter  = html_entity_decode('&ccedil;', ENT_COMPAT, PHPMailer::CHARSET_ISO88591);
        $domain  = '@' . 'fran' . $letter . 'ois.ch';
        $address = 'test+replyto' . $domain;
        self::assertTrue($this->Mail->addReplyTo($address), 'Replyto Addressing failed');

        // Queued addresses are not returned by get*Addresses() before send() call.
        self::assertEmpty($this->Mail->getReplyToAddresses(), 'Unexpected "reply-to" address added');

        // Check that the queue has been set correctly.
        $queue = $this->getPropertyValue($this->Mail, 'ReplyToQueue');
        self::assertCount(1, $queue, 'Queue does not contain exactly 1 entry');
        self::assertArrayHasKey($address, $queue, 'Queue does not contain an entry for the IDN address');

        $this->buildBody();
        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);

        // Addresses with IDN are returned by get*Addresses() after preSend() call.
        $domain = $this->Mail->punyencodeAddress($domain);
        self::assertSame(
            ['test+replyto' . $domain => ['test+replyto' . $domain, '']],
            $this->Mail->getReplyToAddresses(),
            'Bad "reply-to" addresses'
        );
    }

    /**
     * Tests removal of duplicate recipients and reply-tos.
     *
     * @requires extension mbstring
     * @requires function idn_to_ascii
     */
    public function testDuplicateIDNRemoved()
    {
        $this->Mail->clearReplyTos();

        $this->Mail->CharSet = PHPMailer::CHARSET_UTF8;

        self::assertTrue($this->Mail->addReplyTo('test+replyto@françois.ch'));
        self::assertFalse($this->Mail->addReplyTo('test+replyto@françois.ch'));
        self::assertTrue($this->Mail->addReplyTo('test+replyto@FRANÇOIS.CH'));
        self::assertFalse($this->Mail->addReplyTo('test+replyto@FRANÇOIS.CH'));
        self::assertTrue($this->Mail->addReplyTo('test+replyto@xn--franois-xxa.ch'));
        self::assertFalse($this->Mail->addReplyTo('test+replyto@xn--franois-xxa.ch'));
        self::assertFalse($this->Mail->addReplyTo('test+replyto@XN--FRANOIS-XXA.CH'));

        $this->buildBody();
        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);

        //There should be only one "Reply-To" address.
        self::assertCount(
            1,
            $this->Mail->getReplyToAddresses(),
            'Bad count of "reply-to" addresses'
        );
    }

    public function testGivenIdnAddress_addReplyTo_returns_true()
    {
        if (file_exists(\PHPMAILER_INCLUDE_DIR . '/test/fakefunctions.php') === false) {
            $this->markTestSkipped('/test/fakefunctions.php file not found');
        }

        include \PHPMAILER_INCLUDE_DIR . '/test/fakefunctions.php';
        $this->assertTrue($this->Mail->addReplyTo('test@françois.ch'));
    }
}
