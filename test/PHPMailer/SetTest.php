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
 * Test property setting functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::set
 */
final class SetTest extends TestCase
{
    /**
     * Test setting the value of a class property.
     *
     * @dataProvider dataSetValidProperty
     *
     * @param string $name  The property name to set
     * @param mixed  $value The value to set the property to
     */
    public function testSetValidProperty($name, $value)
    {
        self::assertTrue($this->Mail->set($name, $value), 'Valid property set failed');
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataSetValidProperty()
    {
        return [
            'Valid: property exists, public' => [
                'name'  => 'Timeout',
                'value' => '11',
            ],
            'Valid: property exists, protected' => [
                'name'  => 'MIMEBody',
                'value' => 'Some text',
            ],
            // Note: no test for private property as the PHPMailer class doesn't have any.
        ];
    }

    /**
     * Test setting a property to `null` and then resetting it to a non-null value.
     */
    public function testTogglingPropertyValueAwayFromNull()
    {
        self::assertTrue($this->Mail->set('AllowEmpty', null), 'Null property set failed');
        self::assertTrue($this->Mail->set('AllowEmpty', false), 'Valid property set of null property failed');
    }

    /**
     * Test setting the value of a class property which doesn't exist.
     */
    public function testSetInvalidProperty()
    {
        self::assertFalse($this->Mail->set('x', 'y'), 'Invalid property set succeeded');

        // Verify that an error has been registered.
        self::assertSame(
            'Cannot set or reset variable: x',
            $this->Mail->ErrorInfo,
            'Error info not correctly registered'
        );
    }
}
