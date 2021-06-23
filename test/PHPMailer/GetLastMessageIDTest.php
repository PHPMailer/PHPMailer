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
 */
final class GetLastMessageIDTest extends TestCase
{

    /**
     * Test setting and retrieving an invalid message ID.
     */
    public function testMessageIDInvalid()
    {
        $this->Mail->Body = 'Test message ID.';
        $id = hash('sha256', 12345);
        $this->Mail->MessageID = $id;
        $this->buildBody();
        $this->Mail->preSend();
        $lastid = $this->Mail->getLastMessageID();
        self::assertNotSame($id, $lastid, 'Invalid Message ID allowed');
    }

    /**
     * Test setting and retrieving a valid, custom message ID.
     */
    public function testMessageIDValid()
    {
        $this->Mail->Body = 'Test message ID.';
        $id = '<' . hash('sha256', 12345) . '@example.com>';
        $this->Mail->MessageID = $id;
        $this->buildBody();
        $this->Mail->preSend();
        $lastid = $this->Mail->getLastMessageID();
        self::assertSame($id, $lastid, 'Custom Message ID not used');
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
