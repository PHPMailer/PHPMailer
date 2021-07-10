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
 * Test setting the "from" address.
 */
final class SetFromTest extends TestCase
{

    /**
     * Test addressing.
     */
    public function testAddressing()
    {
        self::assertTrue($this->Mail->setFrom('a@example.com', 'some name'), 'setFrom failed');
        $this->Mail->Sender = '';
        $this->Mail->setFrom('a@example.com', 'some name', true);
        self::assertSame('a@example.com', $this->Mail->Sender, 'setFrom failed to set sender');
        $this->Mail->Sender = '';
        $this->Mail->setFrom('a@example.com', 'some name', false);
        self::assertSame('', $this->Mail->Sender, 'setFrom should not have set sender');
    }

    /**
     * Test addressing.
     */
    public function testAddressing2()
    {
        $this->buildBody();
        $this->Mail->setFrom('bob@example.com', '"Bob\'s Burgers" (Bob\'s "Burgers")', true);
        $this->Mail->isSMTP();
        $this->Mail->Subject .= ': quotes in from name';
        self::assertTrue($this->Mail->send(), 'send failed');
    }

    /**
     * Test unsuccesfully setting the From, FromName and Sender properties.
     */
    public function testSetFromFail()
    {
        self::assertFalse($this->Mail->setFrom('a@example.com.', 'some name'), 'setFrom accepted invalid address');
    }
}
