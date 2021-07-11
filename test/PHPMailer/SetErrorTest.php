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

use PHPMailer\PHPMailer\SMTP;
use PHPMailer\Test\TestCase;

/**
 * Test error registration functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::isError
 * @covers \PHPMailer\PHPMailer\PHPMailer::setError
 */
final class SetErrorTest extends TestCase
{

    /**
     * Test simple, non-STMP, error registration.
     */
    public function testSetErrorNonSmtp()
    {
        self::assertFalse($this->Mail->isError(), 'Errors found after class initialization');

        // "Abuse" the `PHPMailer::set()` method to register an error.
        self::assertFalse($this->Mail->set('nonexistentproperty', 'value'));

        self::assertTrue($this->Mail->isError(), 'Error count not incremented');
        self::assertSame(
            'Cannot set or reset variable: nonexistentproperty',
            $this->Mail->ErrorInfo,
            'Error info not correctly set'
        );
    }

    /**
     * Test simple, non-STMP, error registration, where only one of the two SMTP conditions is met.
     */
    public function testSetErrorNonSmtpWithMailerSmtp()
    {
        $this->Mail->Mailer = 'smtp';

        $this->testSetErrorNonSMTP();
    }

    /**
     * Test simple, non-STMP, error registration, where the other one of the two SMTP conditions is met.
     */
    public function testSetErrorNonSmtpWithSmtpInstanceSet()
    {
        $stub = $this->getMockBuilder(SMTP::class)->getMock();
        $this->Mail->setSMTPInstance($stub);

        $this->testSetErrorNonSMTP();
    }

    /**
     * Test error registration with SMTP enabled and instantiated.
     *
     * @dataProvider dataSetErrorSmtp
     *
     * @param array  $mockReturn The value the `SMTP::getError()` method mock should return.
     * @param string $expected   The error message which is expected to be registered.
     */
    public function testSetErrorSmtp($mockReturn, $expected)
    {
        $stub = $this->getMockBuilder(SMTP::class)->getMock();
        $stub->method('getError')
             ->willReturn($mockReturn);

        $this->Mail->Mailer = 'smtp';
        $this->Mail->setSMTPInstance($stub);

        // "Abuse" the `PHPMailer::set()` method to register an error.
        self::assertFalse($this->Mail->set('nonexistentproperty', 'value'));

        self::assertTrue($this->Mail->isError(), 'Error count not incremented');
        self::assertSame($expected, $this->Mail->ErrorInfo, 'Error info not correctly set');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataSetErrorSmtp()
    {
        return [
            'No SMTP error' => [
                'mockReturn' => [
                    'error'        => '',
                    'detail'       => '',
                    'smtp_code'    => '',
                    'smtp_code_ex' => '',
                ],
                'expected' => 'Cannot set or reset variable: nonexistentproperty',
            ],
            'SMTP error, no details' => [
                'mockReturn' => [
                    'error'        => 'Fake error',
                    'detail'       => '',
                    'smtp_code'    => '',
                    'smtp_code_ex' => '',
                ],
                'expected' => 'Cannot set or reset variable: nonexistentpropertySMTP server error: Fake error',
            ],
            'SMTP error, full details' => [
                'mockReturn' => [
                    'error'        => 'Fake error',
                    'detail'       => 'Fake detail',
                    'smtp_code'    => 'Fake code',
                    'smtp_code_ex' => 'Fake code ex',
                ],
                'expected' => 'Cannot set or reset variable: nonexistentpropertySMTP server error: '
                    . 'Fake error Detail: Fake detail SMTP code: Fake code Additional SMTP info: Fake code ex',
            ],
        ];
    }

    /**
     * Verify that only the last error registered via `setError()` is available.
     */
    public function testErrorInfoOnlyContainsLastError()
    {
        // "Abuse" the `PHPMailer::set()` method to register a few errors.
        self::assertFalse($this->Mail->set('property1', 'value'));
        self::assertFalse($this->Mail->set('property2', 'value'));
        self::assertFalse($this->Mail->set('property3', 'value'));

        self::assertTrue($this->Mail->isError(), 'Error count not incremented');
        self::assertSame(
            'Cannot set or reset variable: property3',
            $this->Mail->ErrorInfo,
            'Error info not correctly set'
        );
    }

    /**
     * Verify that when no errors have been registered, `isError()` returns false.
     */
    public function testIsErrorWithoutError()
    {
        self::assertFalse($this->Mail->isError(), 'Error count not 0');
    }
}
