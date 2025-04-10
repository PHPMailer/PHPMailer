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
use PHPMailer\Test\TestCase;

/**
 * Test setting the "from" address.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::setFrom
 */
final class SetFromTest extends TestCase
{
    /**
     * Test successfully setting the From, FromName and Sender properties.
     *
     * @dataProvider dataSetFromSuccess
     *
     * @param string $expected Expected function output.
     * @param string $address  Email address input to pass to the function.
     * @param string $name     Optional. Name input to pass to the function.
     */
    public function testSetFromSuccess($expected, $address, $name = '')
    {
        $result = $this->Mail->setFrom($address, $name);
        self::assertTrue($result, 'setFrom failed');

        self::assertSame($expected['From'], $this->Mail->From, 'From has not been set');
        self::assertSame($expected['FromName'], $this->Mail->FromName, 'From name has not been set');
        self::assertSame($expected['From'], $this->Mail->Sender, 'Sender has not been set');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataSetFromSuccess()
    {
        return [
            'Email, no name' => [
                'expected' => [
                    'From'     => 'a@example.com',
                    'FromName' => '',
                ],
                'address'  => 'a@example.com',
            ],
            'Email, no name; whitespace padding around email' => [
                'expected' => [
                    'From'     => 'whitespacepadding@example.com',
                    'FromName' => '',
                ],
                'address'  => " \t  whitespacepadding@example.com   \n",
            ],
            'Email + name' => [
                'expected' => [
                    'From'     => 'a@example.com',
                    'FromName' => 'some name',
                ],
                'address'  => 'a@example.com',
                'name'     => 'some name',
            ],
            'Email + name; quotes in the name' => [
                'expected' => [
                    'From'     => 'bob@example.com',
                    'FromName' => '"Bob\'s Burgers" (Bob\'s "Burgers")',
                ],
                'address'  => 'bob@example.com',
                'name'     => '"Bob\'s Burgers" (Bob\'s "Burgers")',
            ],
            'Email + name; line breaks in name' => [
                'expected' => [
                    'From'     => 'removebreaks@example.com',
                    'FromName' => 'somename',
                ],
                'address'  => 'removebreaks@example.com',
                'name'     => "\r\nsome\r\nname\r\n",
            ],
            'Email + name; whitespace padding around name' => [
                'expected' => [
                    'From'     => 'a@example.com',
                    'FromName' => 'some name',
                ],
                'address'  => 'a@example.com',
                'name'     => "\t\tsome name    \r\n",
            ],
        ];
    }

    /**
     * Test setting the From address, but not overruling the Sender value when the $auto parameter is set to false.
     */
    public function testSetFromDoesNotOverruleSenderWithAutoFalse()
    {
        $result = $this->Mail->setFrom('overruled@example.com', 'some name', false);

        self::assertTrue($result, 'setFrom failed');
        self::assertSame('', $this->Mail->Sender, 'Sender has been overruled');
    }

    /**
     * Test setting the From address, but not overruling an existing, non-empty Sender value.
     */
    public function testSetFromDoesNotOverruleExistingSender()
    {
        $sender             = 'donotoverrule@example.com';
        $this->Mail->Sender = $sender;

        $result = $this->Mail->setFrom('overruled@example.com');

        self::assertTrue($result, 'setFrom failed');
        self::assertSame($sender, $this->Mail->Sender, 'Sender has been overruled');
    }

    /**
     * Test unsuccessfully setting the From, FromName and Sender properties.
     *
     * @dataProvider dataSetFromFail
     *
     * @param string $address Email address input to pass to the function.
     * @param string $name    Optional. Name input to pass to the function.
     */
    public function testSetFromFail($address, $name = '')
    {
        // Get the original, default values from the class.
        $expectedFrom     = $this->Mail->From;
        $expectedFromName = $this->Mail->FromName;

        $result = $this->Mail->setFrom($address, $name);
        self::assertFalse($result, 'setFrom did not fail');
        self::assertTrue($this->Mail->isError(), 'Error count not incremented');

        self::assertSame($expectedFrom, $this->Mail->From, 'From has been overruled');
        self::assertSame($expectedFromName, $this->Mail->FromName, 'From name has been overruled');
        self::assertSame('', $this->Mail->Sender, 'Sender has been overruled');
    }

    /**
     * Test that setting an invalid email address results in an exception.
     *
     * @dataProvider dataSetFromFail
     *
     * @param string $address Email address input to pass to the function.
     * @param string $name    Optional. Name input to pass to the function.
     */
    public function testInvalidAddressException($address, $name = '')
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid address:  (From):');

        $mail = new PHPMailer(true);
        $mail->setFrom($address, $name);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataSetFromFail()
    {
        return [
            'Invalid email address' => [
                'address' => 'a@example.com.',
                'name'    => 'some name',
            ],
            'Not an email address: missing @ sign' => [
                'address' => 'example.com',
            ],
            'Not an email address: nothing after the @ sign' => [
                'address' => 'example@',
            ],
        ];
    }

    /**
     * Test unsuccessfully setting the From, FromName and Sender properties when an email address
     * containing an 8bit character is passed and either the MbString or the Intl extension are
     * not available.
     */
    public function testSetFromFailsOn8BitCharInDomainWithoutOptionalExtensions()
    {
        if (extension_loaded('mbstring') && function_exists('idn_to_ascii')) {
            self::markTestSkipped('Test requires MbString and/or Intl *not* to be available');
        }

        $this->testSetFromFail("8bit@ex\x80mple.com");
    }
}
