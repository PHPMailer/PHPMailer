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
 * Test ICal calendar events handling.
 */
final class ICalTest extends PreSendTestCase
{
    /**
     * Test ICal method.
     *
     * @dataProvider dataICalMethod
     * @covers       \PHPMailer\PHPMailer\PHPMailer::createBody
     *
     * @param string $methodLine The Ical method line to use.
     * @param string $expected   The expected method in the content type header.
     */
    public function testICalMethod($methodLine, $expected)
    {
        $this->Mail->Subject .= ': ICal method';
        $this->Mail->Body = '<h3>ICal method test.</h3>';
        $this->Mail->AltBody = 'ICal method test.';
        $this->Mail->Ical = 'BEGIN:VCALENDAR'
            . "\r\nVERSION:2.0"
            . "\r\nPRODID:-//PHPMailer//PHPMailer Calendar Plugin 1.0//EN"
            . $methodLine
            . "\r\nCALSCALE:GREGORIAN"
            . "\r\nX-MICROSOFT-CALSCALE:GREGORIAN"
            . "\r\nBEGIN:VEVENT"
            . "\r\nUID:201909250755-42825@test"
            . "\r\nDTSTART;20190930T080000Z"
            . "\r\nSEQUENCE:2"
            . "\r\nTRANSP:OPAQUE"
            . "\r\nSTATUS:CONFIRMED"
            . "\r\nDTEND:20190930T084500Z"
            . "\r\nLOCATION:[London] London Eye"
            . "\r\nSUMMARY:Test ICal method"
            . "\r\nATTENDEE;CN=Attendee, Test;ROLE=OPT-PARTICIPANT;PARTSTAT=NEEDS-ACTION;RSVP="
            . "\r\n TRUE:MAILTO:attendee-test@example.com"
            . "\r\nCLASS:PUBLIC"
            . "\r\nDESCRIPTION:Some plain text"
            . "\r\nORGANIZER;CN=\"Example, Test\":MAILTO:test@example.com"
            . "\r\nDTSTAMP:20190925T075546Z"
            . "\r\nCREATED:20190925T075709Z"
            . "\r\nLAST-MODIFIED:20190925T075546Z"
            . "\r\nEND:VEVENT"
            . "\r\nEND:VCALENDAR";
        $this->buildBody();
        $this->Mail->preSend();

        $expected = 'Content-Type: text/calendar; method=' . $expected . ';';

        self::assertStringContainsString(
            $expected,
            $this->Mail->getSentMIMEMessage(),
            'Wrong ICal method in Content-Type header'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataICalMethod()
    {
        return [
            'Valid method: request (default)' => [
                'methodLine' => "\r\nMETHOD:REQUEST",
                'expected'   => 'REQUEST',
            ],
            'Valid method: publish' => [
                'methodLine' => "\r\nMETHOD:PUBLISH",
                'expected'   => 'PUBLISH',
            ],
            'Valid method: reply' => [
                'methodLine' => "\r\nMETHOD:REPLY",
                'expected'   => 'REPLY',
            ],
            'Valid method: add' => [
                'methodLine' => "\r\nMETHOD:ADD",
                'expected'   => 'ADD',
            ],
            'Valid method: cancel' => [
                'methodLine' => "\r\nMETHOD:CANCEL",
                'expected'   => 'CANCEL',
            ],
            'Valid method: refresh' => [
                'methodLine' => "\r\nMETHOD:REFRESH",
                'expected'   => 'REFRESH',
            ],
            'Valid method: counter' => [
                'methodLine' => "\r\nMETHOD:COUNTER",
                'expected'   => 'COUNTER',
            ],
            'Valid method: declinecounter' => [
                'methodLine' => "\r\nMETHOD:DECLINECOUNTER",
                'expected'   => 'DECLINECOUNTER',
            ],
            // Test ICal invalid method to use default (REQUEST).
            'Invalid method' => [
                'methodLine' => "\r\nMETHOD:INVALID",
                'expected'   => 'REQUEST',
            ],
            // Test ICal missing method to use default (REQUEST).
            'Missing method' => [
                'methodLine' => '',
                'expected'   => 'REQUEST',
            ],
        ];
    }
}
