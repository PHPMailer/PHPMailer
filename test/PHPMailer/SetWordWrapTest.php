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
 */
final class SetWordWrapTest extends PreSendTestCase
{

    /**
     * Word-wrap an ASCII message.
     */
    public function testWordWrap()
    {
        $this->Mail->WordWrap = 40;
        $my_body = str_repeat(
            'Here is the main body of this message.  It should ' .
            'be quite a few lines.  It should be wrapped at ' .
            '40 characters.  Make sure that it is. ',
            10
        );
        $nBodyLen = strlen($my_body);
        $my_body .= "\n\nThis is the above body length: " . $nBodyLen;

        $this->Mail->Body = $my_body;
        $this->Mail->Subject .= ': Wordwrap';

        $this->buildBody();
        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);
    }

    /**
     * Word-wrap a multibyte message.
     */
    public function testWordWrapMultibyte()
    {
        $this->Mail->WordWrap = 40;
        $my_body = str_repeat(
            '飛兒樂 團光茫 飛兒樂 團光茫 飛兒樂 團光茫 飛兒樂 團光茫 ' .
            '飛飛兒樂 團光茫兒樂 團光茫飛兒樂 團光飛兒樂 團光茫飛兒樂 團光茫兒樂 團光茫 ' .
            '飛兒樂 團光茫飛兒樂 團飛兒樂 團光茫光茫飛兒樂 團光茫. ',
            10
        );
        $nBodyLen = strlen($my_body);
        $my_body .= "\n\nThis is the above body length: " . $nBodyLen;

        $this->Mail->Body = $my_body;
        $this->Mail->Subject .= ': Wordwrap multibyte';

        $this->buildBody();
        self::assertTrue($this->Mail->preSend(), $this->Mail->ErrorInfo);
    }
}
