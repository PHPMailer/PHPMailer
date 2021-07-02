<?php

/**
 * PHPMailer - Base test class.
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace PHPMailer\Test;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\Test\TestCase;

/**
 * PHPMailer - Test class for tests which need the `PHPMailer::preSend()` method to be called.
 */
abstract class PreSendTestCase extends TestCase
{

    /**
     * Run before each test is started.
     */
    protected function set_up()
    {
        parent::set_up();

        $this->Mail->Priority = 3;
        $this->Mail->Encoding = '8bit';
        $this->Mail->CharSet = PHPMailer::CHARSET_ISO88591;
        $this->Mail->From = 'unit_test@phpmailer.example.com';
        $this->Mail->FromName = 'Unit Tester';
        $this->Mail->Sender = '';
        $this->Mail->Subject = 'Unit Test';
        $this->Mail->Body = '';
        $this->Mail->AltBody = '';
        $this->Mail->WordWrap = 0;
        $this->Mail->Host = 'mail.example.com';
        $this->Mail->Port = 25;
        $this->Mail->Helo = 'localhost.localdomain';
        $this->Mail->SMTPAuth = false;
        $this->Mail->Username = '';
        $this->Mail->Password = '';
        $this->setAddress('no_reply@phpmailer.example.com', 'Reply Guy', 'ReplyTo');
        $this->Mail->Sender = 'unit_test@phpmailer.example.com';
        $this->setAddress('somebody@example.com', 'Test User', 'to');

        if ($this->Mail->Host != '') {
            $this->Mail->isSMTP();
        } else {
            $this->Mail->isMail();
        }
    }
}
