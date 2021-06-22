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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\Test\TestCase;

/**
 * Test email address validation using a custom validator.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::validateAddress
 */
final class ValidateAddressCustomValidatorTest extends TestCase
{

    /**
     * Test injecting a custom validator.
     */
    public function testCustomValidator()
    {
        //Inject a one-off custom validator
        self::assertTrue(
            PHPMailer::validateAddress(
                'user@example.com',
                static function ($address) {
                    return strpos($address, '@') !== false;
                }
            ),
            'Custom validator false negative'
        );
        self::assertFalse(
            PHPMailer::validateAddress(
                'userexample.com',
                static function ($address) {
                    return strpos($address, '@') !== false;
                }
            ),
            'Custom validator false positive'
        );
        //Set the default validator to an injected function
        PHPMailer::$validator = static function ($address) {
            return 'user@example.com' === $address;
        };
        self::assertTrue(
            $this->Mail->addAddress('user@example.com'),
            'Custom default validator false negative'
        );
        self::assertFalse(
        //Need to pick a failing value which would pass all other validators
        //to be sure we're using our custom one
            $this->Mail->addAddress('bananas@example.com'),
            'Custom default validator false positive'
        );
        //Set validator back to default
        PHPMailer::$validator = 'php';
        self::assertFalse(
        //This is a valid address that FILTER_VALIDATE_EMAIL thinks is invalid
            $this->Mail->addAddress('first.last@example.123'),
            'PHP validator not behaving as expected'
        );

        //Test denying function name callables as validators
        //See SECURITY.md and CVE-2021-3603
        //If a `php` function defined in validators.php successfully overrides this built-in validator name,
        //this would return false – and we don't want to allow that
        self::assertTrue(PHPMailer::validateAddress('test@example.com', 'php'));
        //Check that a non-existent validator name falls back to a built-in validator
        //and does not call a global function with that name
        self::assertTrue(PHPMailer::validateAddress('test@example.com', 'phpx'));
    }
}
