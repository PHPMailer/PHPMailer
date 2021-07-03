<?php

/**
 * PHPMailer - Base test class.
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace PHPMailer\Test;

use PHPMailer\PHPMailer\PHPMailer;
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

        'Priority'    => 3,
        'Encoding'    => '8bit',
        'CharSet'     => PHPMailer::CHARSET_ISO88591,
        'From'        => 'unit_test@phpmailer.example.com',
        'FromName'    => 'Unit Tester',
        'Sender'      => 'unit_test@phpmailer.example.com',
        'Subject'     => 'Unit Test',
        'Body'        => '',
        'AltBody'     => '',
        'WordWrap'    => 0,
        'Host'        => 'mail.example.com',
        'Port'        => 25,
        'Helo'        => 'localhost.localdomain',
        'SMTPAuth'    => false,
        'Username'    => '',
        'Password'    => '',
        'ReplyTo'     => [
            'address' => 'no_reply@phpmailer.example.com',
            'name'    => 'Reply Guy',
        ],
        'to'          => [
            'address' => 'somebody@example.com',
            'name'    => 'Test User',
        ],
    ];
}
