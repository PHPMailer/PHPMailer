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
     * Tests setting and getting custom headers.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::addCustomHeader
     * @covers \PHPMailer\PHPMailer\PHPMailer::getCustomHeaders
     *
     * @dataProvider dataAddAndGetCustomHeader
     *
     * @param array      $headers  Array of headers to set.
     * @param array|null $expected Optional. The expected set headers.
     *                             Only needs to be passed if different from the $headers array.
     */
    public function testAddAndGetCustomHeader($headers, $expected = null)
    {
        if (isset($expected) === false) {
            $expected = $headers;
        }

        // Test setting the custom header(s).
        foreach ($headers as $header) {
            if (isset($header[1])) {
                $set = $this->Mail->addCustomHeader($header[0], $header[1]);
            } else {
                $set = $this->Mail->addCustomHeader($header[0]);
            }

            self::assertTrue($set, 'Custom header failed to set for ' . var_export($header, true));
        }

        // Test retrieving the custom header(s) and verify they have been correctly set.
        self::assertSame(
            $expected,
            $this->Mail->getCustomHeaders(),
            'Custom headers retrieved not the same as expected'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataAddAndGetCustomHeader()
    {
        return [
            'Single custom header' => [
                'headers' => [
                    ['foo', 'bar'],
                ],
            ],
            'Multiple custom headers' => [
                'headers' => [
                    ['foo', 'bar'],
                    ['foo', 'baz'],
                ],
            ],
            'Custom header: only name, no colon' => [
                'headers' => [
                    ['yux'],
                ],
                'expected' => [
                    ['yux', ''],
                ],
            ],
            'Custom header: whitespace around name and value' => [
                'headers' => [
                    ['  name  ', '  value  '],
                ],
                'expected' => [
                    ['name', 'value'],
                ],
            ],
            'Custom headers: "name: value" sets' => [
                'headers' => [
                    ['Content-Type: application/json'],
                    ['SomeHeader: Some Value'],
                ],
                'expected' => [
                    ['Content-Type', 'application/json'],
                    ['SomeHeader', 'Some Value'],
                ],
            ],
            'Custom headers: "name:value" sets, no space and lots of space' => [
                'headers' => [
                    ['Content-Type:application/json'],
                    ['SomeHeader    :     Some Value'],
                ],
                'expected' => [
                    ['Content-Type', 'application/json'],
                    ['SomeHeader', 'Some Value'],
                ],
            ],
            'Custom headers: "name: value" set with a colon in the value' => [
                'headers' => [
                    ['name: value:value'],
                ],
                'expected' => [
                    ['name', 'value:value'],
                ],
            ],
            'Custom headers: "name: value" set without a value' => [
                'headers' => [
                    ['name:'],
                ],
                'expected' => [
                    ['name', ''],
                ],
            ],
            'Custom header: "name: value" set with whitespace around name and value' => [
                'headers' => [
                    ['  name  :  value  '],
                ],
                'expected' => [
                    ['name', 'value'],
                ],
            ],
            'Custom headers: duplicate headers' => [
                'headers' => [
                    ['SomeHeader: Some Value'],
                    ['SomeHeader', 'Some Value']
                ],
                'expected' => [
                    ['SomeHeader', 'Some Value'],
                    ['SomeHeader', 'Some Value'],
                ],
            ],
        ];
    }

    /**
     * Tests failing to set custom headers when the header info provided does not validate.
     *
     * @covers       \PHPMailer\PHPMailer\PHPMailer::addCustomHeader
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
            'Invalid: empty name' => [
                'name'  => '   ',
            ],
            'Invalid: empty name and empty value' => [
                'name'  => '  :  ',
            ],
        ];
    }

    /**
     * Test removing previously set custom headers.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::clearCustomHeaders
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
     * @covers \PHPMailer\PHPMailer\PHPMailer::addCustomHeader
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
