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

use PHPMailer\Test\PreSendTestCase;

/**
 * PHPMailer - Test class for tests which need the `PHPMailer::send()` method to be called.
 */
abstract class SendTestCase extends PreSendTestCase
{

    /**
     * Run before each test is started.
     */
    protected function set_up()
    {
        parent::set_up();

        if (file_exists(\PHPMAILER_INCLUDE_DIR . '/test/testbootstrap.php')) {
            include \PHPMAILER_INCLUDE_DIR . '/test/testbootstrap.php'; // Overrides go in here.
        }

        if (array_key_exists('mail_from', $_REQUEST)) {
            $this->Mail->From = $_REQUEST['mail_from'];
        }
        if (array_key_exists('mail_host', $_REQUEST)) {
            $this->Mail->Host = $_REQUEST['mail_host'];
        }
        if (array_key_exists('mail_port', $_REQUEST)) {
            $this->Mail->Port = $_REQUEST['mail_port'];
        }
        if (array_key_exists('mail_useauth', $_REQUEST)) {
            $this->Mail->SMTPAuth = $_REQUEST['mail_useauth'];
        }
        if (array_key_exists('mail_username', $_REQUEST)) {
            $this->Mail->Username = $_REQUEST['mail_username'];
        }
        if (array_key_exists('mail_userpass', $_REQUEST)) {
            $this->Mail->Password = $_REQUEST['mail_userpass'];
        }
        if (array_key_exists('mail_to', $_REQUEST)) {
            $this->setAddress($_REQUEST['mail_to'], 'Test User', 'to');
        }
        if (array_key_exists('mail_cc', $_REQUEST) && $_REQUEST['mail_cc'] !== '') {
            $this->setAddress($_REQUEST['mail_cc'], 'Carbon User', 'cc');
        }

        if ($this->Mail->Host != '') {
            $this->Mail->isSMTP();
        } else {
            $this->Mail->isMail();
        }
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
 * SMTP Hostname: <input type="text" size="50" name="mail_host" value="<?php echo get("mail_host"); ?>"/>
 * <p/>
 * <input type="submit" value="Run Test"/>
 *
 * </form>
 * </body>
 * </html>
 */
