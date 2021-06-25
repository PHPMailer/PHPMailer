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
 * Test IDN to ASCII functionality.
 */
final class PunyencodeAddressTest extends TestCase
{

    public function testGivenIdnAddress_punyencodeAddress_returnsCorrectCode()
    {
        if (file_exists(\PHPMAILER_INCLUDE_DIR . '/test/fakefunctions.php') === false) {
            $this->markTestSkipped('/test/fakefunctions.php file not found');
        }

        include \PHPMAILER_INCLUDE_DIR . '/test/fakefunctions.php';
        //This source file is in UTF-8, so characters here are in native charset
        $this->Mail->CharSet = PHPMailer::CHARSET_UTF8;
        $result = $this->Mail->punyencodeAddress(
            html_entity_decode('test@fran&ccedil;ois.ch', ENT_COMPAT, PHPMailer::CHARSET_UTF8)
        );
        $this->assertSame('test@xn--franois-xxa.ch', $result);
        //To force working another charset, decode an ASCII string to avoid literal string charset issues
        $this->Mail->CharSet = PHPMailer::CHARSET_ISO88591;
        $result = $this->Mail->punyencodeAddress(
            html_entity_decode('test@fran&ccedil;ois.ch', ENT_COMPAT, PHPMailer::CHARSET_ISO88591)
        );
        $this->assertSame('test@xn--franois-xxa.ch', $result);
    }
}
