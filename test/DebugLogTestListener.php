<?php

/**
 * PHPMailer - language file tests.
 *
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @author    Juliette Reinders Folmer
 * @copyright 2010 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @copyright 2020 Juliette Reinders Folmer
 * @license   https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License
 */

namespace PHPMailer\Test;

use PHPUnit\Framework\TestListener;
use Yoast\PHPUnitPolyfills\TestListeners\TestListenerDefaultImplementation;

class DebugLogTestListener implements TestListener
{
    use TestListenerDefaultImplementation;

    private static $debugLog = '';

    public function add_error($test, $e, $time)
    {
        echo self::$debugLog;
    }

    public function add_failure($test, $e, $time)
    {
        echo self::$debugLog;
    }

    public function start_test($test)
    {
        self::$debugLog = '';
    }

    public static function debugLog($str)
    {
        self::$debugLog .= $str . PHP_EOL;
    }
}
