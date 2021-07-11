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

use PHPMailer\Test\SendTestCase;

/**
 * Test error registration functionality.
 */
final class SetErrorTest extends SendTestCase
{

    /**
     * Test error handling.
     */
    public function testError()
    {
        $this->Mail->Subject .= ': Error handling test - this should be sent ok';
        $this->buildBody();
        $this->Mail->clearAllRecipients(); //No addresses should cause an error
        self::assertTrue($this->Mail->isError() == false, 'Error found');
        self::assertTrue($this->Mail->send() == false, 'send succeeded');
        self::assertTrue($this->Mail->isError(), 'No error found');
        self::assertSame('You must provide at least one recipient email address.', $this->Mail->ErrorInfo);
        $this->Mail->addAddress($_REQUEST['mail_to']);
        self::assertTrue($this->Mail->send(), 'send failed');
    }
}
