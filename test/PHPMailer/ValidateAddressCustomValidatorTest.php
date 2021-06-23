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
     * Test injecting a one-off custom validator.
     */
    public function testOneOffCustomValidator()
    {
        $callback = static function ($address) {
            return strpos($address, '@') !== false;
        };

        self::assertTrue(
            PHPMailer::validateAddress('user@example.com', $callback),
            'Custom validator false negative'
        );
        self::assertFalse(
            PHPMailer::validateAddress('userexample.com', $callback),
            'Custom validator false positive'
        );
    }

    /**
     * Test setting the default validator to an injected function.
     */
    public function testSetDefaultValidatorToCustom()
    {
        // Set the default validator to an injected function.
        PHPMailer::$validator = static function ($address) {
            return 'user@example.com' === $address;
        };

        self::assertTrue(
            $this->Mail->addAddress('user@example.com'),
            'Custom default validator false negative'
        );

        // Need to pick a failing value which would pass all other validators
        // to be sure we're using our custom one.
        self::assertFalse(
            $this->Mail->addAddress('bananas@example.com'),
            'Custom default validator false positive'
        );

        // Set validator back to default
        PHPMailer::$validator = 'php';

        // This is a valid address that FILTER_VALIDATE_EMAIL thinks is invalid.
        self::assertFalse(
            $this->Mail->addAddress('first.last@example.123'),
            'PHP validator not behaving as expected'
        );
    }

    /**
     * Test denying function name callables as validators.
     *
     * See SECURITY.md and CVE-2021-3603.
     *
     * @dataProvider dataRejectCallables
     *
     * @param string $callback Callback function name.
     * @param string $message  Message to display if the test would fail.
     */
    public function testRejectCallables($callback, $message)
    {
        require_once \PHPMAILER_INCLUDE_DIR . '/test/validators.php';

        self::assertTrue(PHPMailer::validateAddress('test@example.com', $callback), $message);
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataRejectCallables()
    {
        return [
            // If a `php` function defined in validators.php successfully overrides this built-in validator name,
            // this would return false - and we don't want to allow that.
            'php'  => [
                'callback' => 'php',
                'message'  => 'Build-in php validator overridden',
            ],
            // Check that a non-existent validator name falls back to a built-in validator
            // and does not call a global function with that name.
            'phpx' => [
                'callback' => 'phpx',
                'message'  => 'Global function called instead of default validator',
            ],
        ];
    }
}
