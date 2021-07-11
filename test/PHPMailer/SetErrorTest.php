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
 * Test error registration functionality.
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
}
