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

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\Test\TestCase;

/**
 * Test custom header functionality.
 */
final class CustomHeaderTest extends TestCase
{

    /**
     * Tests the Custom header getter.
     */
    public function testCustomHeaderGetter()
    {
        $this->Mail->addCustomHeader('foo', 'bar');
        self::assertSame([['foo', 'bar']], $this->Mail->getCustomHeaders());

        $this->Mail->addCustomHeader('foo', 'baz');
        self::assertSame(
            [
                ['foo', 'bar'],
                ['foo', 'baz'],
            ],
            $this->Mail->getCustomHeaders()
        );

        $this->Mail->clearCustomHeaders();
        self::assertEmpty($this->Mail->getCustomHeaders());

        $this->Mail->addCustomHeader('yux');
        self::assertSame([['yux', '']], $this->Mail->getCustomHeaders());

        $this->Mail->addCustomHeader('Content-Type: application/json');
        self::assertSame(
            [
                ['yux', ''],
                ['Content-Type', 'application/json'],
            ],
            $this->Mail->getCustomHeaders()
        );
        $this->Mail->clearCustomHeaders();
        $this->Mail->addCustomHeader('SomeHeader: Some Value');
        $headers = $this->Mail->getCustomHeaders();
        self::assertSame(['SomeHeader', 'Some Value'], $headers[0]);
        $this->Mail->clearCustomHeaders();
        $this->Mail->addCustomHeader('SomeHeader', 'Some Value');
        $headers = $this->Mail->getCustomHeaders();
        self::assertSame(['SomeHeader', 'Some Value'], $headers[0]);
        $this->Mail->clearCustomHeaders();
    }

    /**
     * Tests failing to set custom headers when the header info provided does not validate.
     *
     * @dataProvider dataAddCustomHeaderInvalid
     *
     * @param string $name  Custom header name.
     * @param mixed  $value Optional. Custom header value.
     */
    public function testAddCustomHeaderInvalid($name, $value = null)
    {
        self::assertFalse($this->Mail->addCustomHeader($name, $value));
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataAddCustomHeaderInvalid()
    {
        return [
            'Invalid: new line in value' => [
                'name'  => 'SomeHeader',
                'value' => "Some\n Value",
            ],
            'Invalid: new line in name' => [
                'name'  => "Some\nHeader",
                'value' => 'Some Value',
            ],
        ];
    }

    /**
     * Test removing previously set custom headers.
     */
    public function testClearCustomHeaders()
    {
        $this->Mail->addCustomHeader('foo', 'bar');
        self::assertSame([['foo', 'bar']], $this->Mail->getCustomHeaders());

        $this->Mail->clearCustomHeaders();

        $cleared = $this->Mail->getCustomHeaders();
        self::assertIsArray($cleared);
        self::assertEmpty($cleared);
    }

    /**
     * Check whether setting a bad custom header throws exceptions.
     *
     * @throws Exception
     */
    public function testInvalidHeaderException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid header name or value');

        $mail = new PHPMailer(true);
        $mail->addCustomHeader('SomeHeader', "Some\n Value");
    }
}
