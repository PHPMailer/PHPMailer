<?php
/**
 * PHPMailer - language file tests.
 *
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2010 - 2017 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 */

namespace PHPMailer\Test;

use Exception;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\BaseTestListener;
use PHPUnit\Framework\Test;

class DebugLogTestListener extends BaseTestListener
{
    private static $debugLog = '';

    public function addError(Test $test, Exception $e, $time)
    {
        echo self::$debugLog;
    }

    public function addFailure(Test $test, AssertionFailedError $e, $time)
    {
        echo self::$debugLog;
    }

    public function startTest(Test $test)
    {
        self::$debugLog = '';
    }

    public static function debugLog($str)
    {
        self::$debugLog .= $str . PHP_EOL;
    }
}
