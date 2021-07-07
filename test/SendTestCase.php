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

use Exception;
use PHPMailer\Test\PreSendTestCase;

/**
 * PHPMailer - Test class for tests which need the `PHPMailer::send()` method to be called.
 */
abstract class SendTestCase extends PreSendTestCase
{

    /**
     * Translation map for supported $REQUEST keys to the property name in the PHPMailer class.
     *
     * @var array
     */
    private $requestKeys = [
        'mail_from'     => 'From',
        'mail_host'     => 'Host',
        'mail_port'     => 'Port',
        'mail_useauth'  => 'SMTPAuth',
        'mail_username' => 'Username',
        'mail_userpass' => 'Password',
        'mail_to'       => 'to',
        'mail_cc'       => 'cc',
        'mail_bcc'      => 'bcc',
    ];

    /**
     * Run before each test is started.
     */
    protected function set_up()
    {
        /*
         * Make sure the testbootstrap.php file is available.
         * Pretty much everything will fail due to unset recipient if this is not done, so error
         * the tests out before they run if the file does not exist.
         */
        if (file_exists(\PHPMAILER_INCLUDE_DIR . '/test/testbootstrap.php') === false) {
            throw new Exception(
                'Test config params missing - copy testbootstrap-dist.php to testbootstrap.php and change'
                . ' as appropriate for your own test environment setup.'
            );
        }

        include \PHPMAILER_INCLUDE_DIR . '/test/testbootstrap.php'; // Overrides go in here.

        /*
         * Process the $REQUEST values and add them to the list of properties
         * to change at class initialization.
         */
        foreach ($this->requestKeys as $requestKey => $phpmailerKey) {
            if (array_key_exists($requestKey, $_REQUEST) === false) {
                continue;
            }

            switch ($requestKey) {
                case 'mail_to':
                    $this->propertyChanges[$phpmailerKey] = [
                        'address' => $_REQUEST[$requestKey],
                        'name'    => 'Test User',
                    ];
                    break;

                case 'mail_cc':
                    $this->propertyChanges[$phpmailerKey] = [
                        'address' => $_REQUEST[$requestKey],
                        'name'    => 'Carbon User',
                    ];
                    break;

                case 'mail_bcc':
                    $this->propertyChanges[$phpmailerKey] = [
                        'address' => $_REQUEST[$requestKey],
                        'name'    => 'Blind Carbon User',
                    ];
                    break;

                default:
                    $this->propertyChanges[$phpmailerKey] = $_REQUEST[$requestKey];
                    break;
            }
        }

        // Initialize the PHPMailer class.
        parent::set_up();
    }
}
/*
 * This is a sample form for setting appropriate test values through a browser
 * These values can also be set using a file called testbootstrap.php (not in repo) in the same folder as this script
 * which is probably more useful if you run these tests a lot
 * <html>
 * <body>
 * <h3>PHPMailer Unit Test</h3>
 * By entering a SMTP hostname it will automatically perform tests with SMTP.
 *
 * <form name="phpmailer_unit" action=__FILE__ method="get">
 * <input type="hidden" name="submitted" value="1"/>
 * From Address: <input type="text" size="50" name="mail_from" value="<?php echo get("mail_from"); ?>"/>
 * <br/>
 * To Address: <input type="text" size="50" name="mail_to" value="<?php echo get("mail_to"); ?>"/>
 * <br/>
 * Cc Address: <input type="text" size="50" name="mail_cc" value="<?php echo get("mail_cc"); ?>"/>
 * <br/>
 * Bcc Address: <input type="text" size="50" name="mail_bcc" value="<?php echo get("mail_bcc"); ?>"/>
 * <br/>
 * SMTP Hostname: <input type="text" size="50" name="mail_host" value="<?php echo get("mail_host"); ?>"/>
 * <p/>
 * <input type="submit" value="Run Test"/>
 *
 * </form>
 * </body>
 * </html>
 */
