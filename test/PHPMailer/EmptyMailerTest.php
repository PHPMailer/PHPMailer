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

use PHPMailer\Test\TestCase;

/**
 * Test sending a letter with an empty Mailer value
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::postSend
 */
final class EmptyMailerTest extends TestCase
{
    /**
     * @dataProvider provideEmptyMailerValues
     *
     * @param mixed $mailer
     */
    public function testSendWithEmptyMailerDoesNotCrash($mailer)
    {
        $this->Mail->setFrom('from@example.com', 'First Last');
        $this->Mail->addAddress('whoto@example.com', 'John Doe');
        $this->Mail->Subject = 'test';
        $this->Mail->Body = 'Test';

        $this->Mail->Mailer = $mailer;

        self::assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Data provider for empty Mailer values.
     *
     * @return array
     */
    public static function provideEmptyMailerValues()
    {
        return [
            'empty string' => [''],
            'null' => [null],
        ];
    }
}
