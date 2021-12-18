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

use PHPMailer\Test\PreSendTestCase;

/**
 * Test automatic wordwrap functionality.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::setWordWrap
 * @covers \PHPMailer\PHPMailer\PHPMailer::wrapText
 */
final class SetWordWrapTest extends PreSendTestCase
{
    /**
     * Test word-wrapping a message.
     *
     * @dataProvider dataWordWrap
     *
     * @param int    $wrapAt        The number of characters to wrap at.
     * @param string $message       The message to use.
     * @param string $subjectSuffix Subject suffix to use.
     */
    public function testWordWrap($wrapAt, $message, $subjectSuffix)
    {
        $this->Mail->WordWrap = $wrapAt;
        $my_body = str_repeat($message, 10);
        $nBodyLen = strlen($my_body);
        $my_body .= "\n\nThis is the above body length: " . $nBodyLen;

        $this->Mail->Body = $my_body;
        $this->Mail->Subject .= ': ' . $subjectSuffix;

        $this->buildBody();
        $originalLines = explode("\n", $this->Mail->Body);
        $this->Mail->preSend();

        $lines = explode("\n", $this->Mail->Body);
        self::assertGreaterThanOrEqual(
            count($originalLines),
            count($lines),
            'Line count of message less than expected'
        );

        foreach ($lines as $i => $line) {
            self::assertLessThanOrEqual(
                $wrapAt,
                strlen(trim($line)),
                'Character count for line ' . ($i + 1) . ' does not comply with the expected ' . $wrapAt
                . ' characters.' . PHP_EOL . 'Line contents: "' . $line . '"'
            );
        }
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataWordWrap()
    {
        return [
            'ascii message' => [
                'wrapAt'        => 40,
                'message'       => 'Here is the main body of this message.  It should ' .
                    'be quite a few lines.  It should be wrapped at ' .
                    '40 characters.  Make sure that it is. ',
                'subjectSuffix' => 'Wordwrap',
            ],
            'multibyte message' => [
                'wrapAt'        => 40,
                'message'       => '飛兒樂 團光茫 飛兒樂 團光茫 飛兒樂 團光茫 飛兒樂 團光茫 ' .
                    '飛飛兒樂 團光茫兒樂 團光茫飛兒樂 團光飛兒樂 團光茫飛兒樂 團光茫兒樂 團光茫 ' .
                    '飛兒樂 團光茫飛兒樂 團飛兒樂 團光茫光茫飛兒樂 團光茫. ',
                'subjectSuffix' => 'Wordwrap multibyte',
            ],
        ];
    }

    /**
     * Test explicitly NOT word-wrapping a message.
     */
    public function testNoWordWrap()
    {
        $this->Mail->WordWrap = 0;
        $my_body = str_repeat('Irrelevant text, we\'re not wrapping', 10);
        $nBodyLen = strlen($my_body);
        $my_body .= "\n\nLong unwrapped message: " . $nBodyLen;

        $this->Mail->Body = $my_body;
        $this->Mail->Subject .= ': No wordwrap';

        $this->buildBody();
        $originalLines = explode("\n", $this->Mail->Body);
        $this->Mail->preSend();

        $lines = explode("\n", $this->Mail->Body);
        self::assertSameSize($originalLines, $lines, 'Line count of message has changed');

        foreach ($lines as $i => $line) {
            self::assertSame(
                $originalLines[$i],
                $line,
                'Line ' . ($i + 1) . ' has been changed while it shouldn\'t have been'
                    . PHP_EOL . 'Line contents: "' . $originalLines[$i] . '"'
            );
        }
    }
}
