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

namespace PHPMailer\Test\POP3;

use PHPMailer\PHPMailer\POP3;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Test Pop before Smtp functionality.
 *
 * @group pop3
 *
 * @covers PHPMailer\PHPMailer\POP3
 */
final class PopBeforeSmtpTest extends TestCase
{
    /**
     * PIDs of any processes we need to kill.
     *
     * @var array
     */
    protected $pids = [];

    /**
     * Run before each test class.
     */
    public static function set_up_before_class()
    {
        if (defined('PHPMAILER_INCLUDE_DIR') === false) {
            /*
             * Set up default include path.
             * Default to the dir above the test dir, i.e. the project home dir.
             */
            define('PHPMAILER_INCLUDE_DIR', dirname(dirname(__DIR__)));
        }
    }

    /**
     * Run before each test is started.
     */
    protected function set_up()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $this->markTestSkipped('This test needs a non-Windows OS to run');
        }

        // Chdir to test directory as runfakepopserver.sh runs fakepopserver.sh
        // from its working directory.
        chdir(PHPMAILER_INCLUDE_DIR . "/test");
    }

    /**
     * Run after each test is completed.
     */
    protected function tear_down()
    {
        foreach ($this->pids as $pid) {
            $p = escapeshellarg($pid);
            shell_exec("ps $p && kill -TERM $p");
        }
    }

    /**
     * Use a fake POP3 server to test POP-before-SMTP auth with a known-good login.
     */
    public function testPopBeforeSmtpGood()
    {
        // Start a fake POP server.
        $pid = shell_exec(
            '/usr/bin/nohup ' .
            \PHPMAILER_INCLUDE_DIR .
            '/test/runfakepopserver.sh 1100 >/dev/null 2>/dev/null & printf "%u" $!'
        );
        $this->pids[] = $pid;

        sleep(1);

        // Test a known-good login.
        self::assertTrue(
            POP3::popBeforeSmtp('localhost', 1100, 10, 'user', 'test'),
            'POP before SMTP failed'
        );

        // Kill the fake server, don't care if it fails.
        @shell_exec('kill -TERM ' . escapeshellarg($pid));
        sleep(2);
    }

    /**
     * Use a fake POP3 server to test POP-before-SMTP auth
     * with a known-bad login.
     */
    public function testPopBeforeSmtpBad()
    {
        // Start a fake POP server on a different port,
        // so we don't inadvertently connect to the previous instance.
        $pid = shell_exec(
            '/usr/bin/nohup ' .
            \PHPMAILER_INCLUDE_DIR .
            '/test/runfakepopserver.sh 1101 >/dev/null 2>/dev/null & printf "%u" $!'
        );
        $this->pids[] = $pid;

        sleep(2);

        // Test a known-bad login.
        self::assertFalse(
            POP3::popBeforeSmtp('localhost', 1101, 10, 'user', 'xxx'),
            'POP before SMTP should have failed'
        );

        // Kill the fake server, don't care if it fails.
        @shell_exec('kill -TERM ' . escapeshellarg($pid));
        sleep(2);
    }

    /**
     * Test case when POP3 server is unreachable.
     */
    public function testPopBeforeSmtpUnreachable()
    {
        // There is no POP3 server at all. Port is different again.
        self::assertFalse(
            POP3::popBeforeSmtp('localhost', 1102, 10, 'user', 'xxx'),
            'POP before SMTP should have failed'
        );
    }
}
