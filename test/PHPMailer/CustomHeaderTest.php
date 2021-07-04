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
        self::assertFalse($this->Mail->addCustomHeader('SomeHeader', "Some\n Value"));
        self::assertFalse($this->Mail->addCustomHeader("Some\nHeader", 'Some Value'));
    }

    /**
     * Check whether setting a bad custom header throws exceptions.
     *
     * @throws Exception
     */
    public function testHeaderException()
    {
        $this->expectException(Exception::class);

        $mail = new PHPMailer(true);
        $mail->addCustomHeader('SomeHeader', "Some\n Value");
    }
}
