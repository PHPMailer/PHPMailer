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

use PHPMailer\Test\TestCase;

/**
 * Test property setting functionality.
 */
final class SetTest extends TestCase
{

    /**
     * Miscellaneous calls to improve test coverage and some small tests.
     */
    public function testMiscellaneous()
    {
        self::assertFalse($this->Mail->set('x', 'y'), 'Invalid property set succeeded');
        self::assertTrue($this->Mail->set('Timeout', 11), 'Valid property set failed');
        self::assertTrue($this->Mail->set('AllowEmpty', null), 'Null property set failed');
        self::assertTrue($this->Mail->set('AllowEmpty', false), 'Valid property set of null property failed');
    }
}
