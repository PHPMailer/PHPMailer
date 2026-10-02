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
 * Test creating address headers.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::addrAppend
 */
final class AddrAppendTest extends TestCase
{
    /**
     * A short address list stays on one line.
     */
    public function testShortListIsNotFolded()
    {
        $header = $this->Mail->addrAppend('To', [['joe@example.com', 'Joe'], ['zoe@example.com', '']]);

        self::assertSame('To: Joe <joe@example.com>, zoe@example.com' . PHPMailer::CRLF, $header);
    }

    /**
     * A long address list is folded between addresses so no line exceeds the RFC 5322 limit.
     */
    public function testLongListIsFolded()
    {
        $addresses = [];
        $formatted = [];
        for ($i = 1; $i <= 100; ++$i) {
            $addresses[] = ['recipient' . $i . '@example.com', 'Recipient ' . $i];
            $formatted[] = 'Recipient ' . $i . ' <recipient' . $i . '@example.com>';
        }

        $header = $this->Mail->addrAppend('Cc', $addresses);
        $lines = explode(PHPMailer::CRLF, substr($header, 0, -strlen(PHPMailer::CRLF)));

        self::assertGreaterThan(1, count($lines), 'The header was not folded');
        foreach ($lines as $index => $line) {
            self::assertLessThanOrEqual(PHPMailer::MAX_LINE_LENGTH, strlen($line), "Line $index is too long");
            if ($index > 0) {
                self::assertSame(' ', $line[0], "Line $index is not a folded continuation");
            }
        }
        self::assertSame(
            'Cc: ' . implode(', ', $formatted) . PHPMailer::CRLF,
            str_replace(PHPMailer::CRLF . ' ', ' ', $header),
            'Unfolding the header did not give back the address list'
        );
    }
}
