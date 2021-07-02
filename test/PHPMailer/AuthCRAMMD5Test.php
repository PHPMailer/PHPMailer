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
 * Test CRAM-MD5 authentication functionality.
 */
final class AuthCRAMMD5Test extends SendTestCase
{

    /**
     * Test CRAM-MD5 authentication.
     * Needs a connection to a server that supports this auth mechanism, so commented out by default.
     */
    public function testAuthCRAMMD5()
    {
        $this->markTestIncomplete(
            'Test needs a connection to a server supporting the CRAMMD5 auth mechanism.'
        );

        $this->Mail->Host = 'hostname';
        $this->Mail->Port = 587;
        $this->Mail->SMTPAuth = true;
        $this->Mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->Mail->AuthType = 'CRAM-MD5';
        $this->Mail->Username = 'username';
        $this->Mail->Password = 'password';
        $this->Mail->Body = 'Test body';
        $this->Mail->Subject .= ': Auth CRAM-MD5';
        $this->Mail->From = 'from@example.com';
        $this->Mail->Sender = 'from@example.com';
        $this->Mail->clearAllRecipients();
        $this->Mail->addAddress('user@example.com');
        //self::assertTrue($this->mail->send(), $this->mail->ErrorInfo);
    }
}
