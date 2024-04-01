<?php

/**
 * PHPMailer - Base test class.
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License
 */

namespace PHPMailer\Test;

use PHPMailer\PHPMailer\SMTP;
use PHPMailer\Test\TestCase;

/**
 * PHPMailer - Test class for tests which need the `PHPMailer::preSend()` method to be called.
 */
abstract class PreSendTestCase extends TestCase
{
    /**
     * Property names and their values for the test instance of the PHPMailer class.
     *
     * These properties will be set in the `set_up()` method.
     *
     * This property can be enhanced/overloaded in concrete test classes to change the presets
     * or add additional properties.
     *
     * @var array
     */
    protected $propertyChanges = [
        // Generic changes.
        'SMTPDebug'   => SMTP::DEBUG_CONNECTION, // Full debug output.
        'Debugoutput' => ['PHPMailer\Test\DebugLogTestListener', 'debugLog'],

        // Minimal set of properties which are needed for the preSend() command to succeed.
        'From'        => 'unit_test@phpmailer.example.com',
        'to'          => [
            'address' => 'somebody@example.com',
            'name'    => 'Test User',
        ],
    ];
}
