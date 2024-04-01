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

namespace PHPMailer\Test\Security;

use PHPMailer\Test\SendTestCase;

/**
 * Test denial of service attack vectors, which have been mitigated.
 *
 * @coversNothing
 */
final class DenialOfServiceVectorsTest extends SendTestCase
{
    /**
     * Test this denial of service attack.
     */
    public function testDenialOfServiceAttack1()
    {
        $this->Mail->Body = 'This should no longer cause a denial of service.';
        $this->buildBody();

        $this->Mail->Subject = substr(str_repeat('0123456789', 100), 0, 998);
        self::assertTrue($this->Mail->send(), $this->Mail->ErrorInfo);
    }

    /**
     * Tests this denial of service attack.
     *
     * According to the ticket, this should get stuck in a loop, though I can't make it happen.
     * @link https://sourceforge.net/p/phpmailer/bugs/383/
     *
     * @doesNotPerformAssertions
     */
    public function testDenialOfServiceAttack2()
    {
        // Encoding name longer than 68 chars.
        $this->Mail->Encoding = '1234567890123456789012345678901234567890123456789012345678901234567890';
        // Call wrapText with a zero length value.
        $this->Mail->wrapText(str_repeat('This should no longer cause a denial of service. ', 30), 0);
    }
}
