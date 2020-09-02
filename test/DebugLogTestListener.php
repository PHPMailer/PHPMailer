<?php
/**
 * PHPMailer - language file tests.
 *
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2010 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace PHPMailer\Test;

class DebugLogTestListener extends \PHPUnit_Framework_BaseTestListener
{
    private static $debugLog = '';

    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        echo self::$debugLog;
    }

    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        echo self::$debugLog;
    }

    public function startTest(\PHPUnit_Framework_Test $test)
    {
        self::$debugLog = '';
    }

    public static function debugLog($str)
    {
        self::$debugLog .= $str . PHP_EOL;
    }
}
