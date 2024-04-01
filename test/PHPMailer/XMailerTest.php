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
 * Test XMailer header setting functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::createHeader
 */
final class XMailerTest extends TestCase
{
    /**
     * Test wrapping text.
     *
     * @dataProvider dataXMailer
     *
     * @param string $xmailer  Input text string.
     * @param string $expected Expected function output.
     */
    public function testXMailer($xmailer, $expected)
    {
        $this->Mail->XMailer = $xmailer;
        $headers = $this->Mail->createHeader();
        if ($expected !== null) {
            self::assertStringContainsString($expected, $headers);
        } else {
            self::assertStringNotContainsString('X-Mailer', $headers);
        }
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataXMailer()
    {
        return [
            'empty string' => [
                'xmailer' => '',
                'expected' => 'X-Mailer: PHPMailer ' . PHPMailer::VERSION . ' (https://github.com/PHPMailer/PHPMailer)',
            ],
            'null' => [
                'xmailer' => null,
                'expected' => null,
            ],
            'whitespace' => [
                'xmailer' => ' ',
                'expected' => null,
            ],
            'non-empty string' => [
                'xmailer' => 'test',
                'expected' => 'X-Mailer: test',
            ],
            'invalid value' => [
                'xmailer' => [],
                'expected' => null,
            ],
        ];
    }
}
