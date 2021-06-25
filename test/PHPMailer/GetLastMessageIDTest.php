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

use PHPMailer\Test\TestCase;

/**
 * Test setting and retrieving message ID.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::createHeader
 * @covers \PHPMailer\PHPMailer\PHPMailer::getLastMessageID
 */
final class GetLastMessageIDTest extends TestCase
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
            'Invalid: plain hash'       => [ $hash ],
            'Invalid: missing brackets' => [ $hash . '@example.com' ],
            'Invalid: missing @'        => [ '<' . $hash . 'example.com>' ],
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
            'hashed pre @'               => [ '<' . $hash . '@example.com>' ],
            'no text before @'           => [ '<@example.com>' ],
            'no text after @'            => [ '<' . $hash . '@>' ],
            'no text before or after @'  => [ '<@>' ],
            'new line after end bracket' => [ '<' . $hash . "@>\n" ],
            'multiple @ signs'           => [ '<' . $hash . '@example@com>' ],
            'brackets within 1'          => [ '<' . $hash . '<@>example.com>' ],
            'brackets within 2'          => [ '<<' . $hash . '@example>com>' ],
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
        self::assertMatchesRegularExpression('/^<.*@.*>$/', $lastid, 'Invalid default Message ID');
    }
}
