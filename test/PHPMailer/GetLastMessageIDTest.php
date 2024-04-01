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

use PHPMailer\Test\PreSendTestCase;

/**
 * Test setting and retrieving message ID.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::createHeader
 * @covers \PHPMailer\PHPMailer\PHPMailer::getLastMessageID
 */
final class GetLastMessageIDTest extends PreSendTestCase
{
    /**
     * Test setting and retrieving an invalid message ID.
     *
     * @dataProvider dataMessageIDInvalid
     *
     * @param string $id Custom, invalid message ID.
     */
    public function testMessageIDInvalid($id)
    {
        $this->Mail->Body = 'Test message ID.';
        $this->Mail->MessageID = $id;
        $this->buildBody();
        $this->Mail->preSend();
        $lastid = $this->Mail->getLastMessageID();
        self::assertNotSame($id, $lastid, 'Invalid Message ID allowed');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataMessageIDInvalid()
    {
        $hash = hash('sha256', 12345);

        return [
            'Invalid: plain hash' => [$hash],
            'Invalid: missing brackets' => [$hash . '@example.com'],
            'Invalid: missing @' => ['<' . $hash . 'example.com>'],
            'Invalid: new line after bracket' => ['<' . $hash . "@example.com>\n"],
            'Invalid: no text before @' => ['<@example.com>'],
            'Invalid: no text after @' => ['<' . $hash . '@>'],
            'Invalid: no text before or after @' => ['<@>'],
            'Invalid: multiple @ signs' => ['<' . $hash . '@example@com>'],
            'Invalid: new line after end bracket' => ['<' . $hash . "@>\n"],
            'Invalid: brackets within 1' => ['<' . $hash . '<@>example.com>'],
            'Invalid: brackets within 2' => ['<<' . $hash . '@example>com>'],
        ];
    }

    /**
     * Test setting and retrieving a valid, custom message ID.
     *
     * @dataProvider dataMessageIDValid
     *
     * @param string $id Custom, valid message ID.
     */
    public function testMessageIDValid($id)
    {
        $this->Mail->Body = 'Test message ID.';
        $this->Mail->MessageID = $id;
        $this->buildBody();
        $this->Mail->preSend();
        $lastid = $this->Mail->getLastMessageID();
        self::assertSame($id, $lastid, 'Custom Message ID not used');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataMessageIDValid()
    {
        $hash = hash('sha256', 12345);

        return [
            'hashed pre @' => ['<' . $hash . '@example.com>'],
        ];
    }

    /**
     * Test setting and retrieving an empty message ID.
     */
    public function testMessageIDEmpty()
    {
        $this->Mail->Body = 'Test message ID.';
        $this->Mail->MessageID = '';
        $this->buildBody();
        $this->Mail->preSend();
        $lastid = $this->Mail->getLastMessageID();
        self::assertMatchesRegularExpression('/^<.+@.+>$/D', $lastid, 'Invalid default Message ID');
    }
}
