<?php

/**
 * PHPMailer - PHP email transport unit tests.
 * PHP version 5.5.
 *
 * @author    Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author    Andy Prevost
 * @copyright 2012 - 2020 Marcus Bointon
 * @copyright 2004 - 2009 Andy Prevost
 * @license   https://www.gnu.org/licenses/old-licenses/lgpl-2.1.html GNU Lesser General Public License
 */

namespace PHPMailer\Test\PHPMailer;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\Test\TestCase;

/**
 * Test DKIM signing functionality.
 *
 * @group dkim
 */
final class DKIMWithoutExceptionsTest extends TestCase
{
    /**
     * Whether or not to initialize the PHPMailer object to throw exceptions.
     *
     * @var bool|null
     */
    const USE_EXCEPTIONS = false;

    /**
     * Verify behaviour of the DKIM_Sign method when Open SSL is not available and exceptions is disabled.
     *
     * @covers \PHPMailer\PHPMailer\PHPMailer::DKIM_Sign
     */
    public function testDKIMSignOpenSSLNotAvailable()
    {
        if (extension_loaded('openssl')) {
            $this->markTestSkipped('Test requires OpenSSL *not* to be available');
        }

        $signature = $this->Mail->DKIM_Sign('foo');
        self::assertSame('', $signature);
    }
}
