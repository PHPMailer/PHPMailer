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

use DateTime;
use DateTimeImmutable;
use PHPMailer\Test\TestCase;

/**
 * Tests for MessageDate handling / the sanitiseDate() method.
 *
 * sanitiseDate() is private and is exercised via createHeader(), whose output
 * contains the "Date:" header line built from whatever was in $Mail->MessageDate.
 *
 * @covers \PHPMailer\PHPMailer\PHPMailer::createHeader
 * @covers \PHPMailer\PHPMailer\PHPMailer::sanitiseDate
 */
final class MessageDateTest extends TestCase
{
    /**
     * Pattern for a well-formed RFC 5322 date-time string as produced by
     * PHPMailer::rfcDate(): "Day, D Mon YYYY HH:MM:SS ±HHMM".
     */
    const RFC5322_PATTERN = '/^
        (?:Mon|Tue|Wed|Thu|Fri|Sat|Sun),\x20
        \d{1,2}\x20
        (?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\x20
        \d{4}\x20\d{2}:\d{2}:\d{2}\x20[+-]\d{4}
    $/x';

    /**
     * Run createHeader() and return only the value portion of the Date header,
     * with the trailing line-ending stripped.
     *
     * @return string
     */
    private function getDateHeaderValue()
    {
        $headers = $this->Mail->createHeader();
        if (preg_match('/^Date: (.+)$/m', $headers, $matches)) {
            return rtrim($matches[1]);
        }
        return '';
    }

    // -----------------------------------------------------------------------
    // Tests: valid inputs that should produce a correctly-formatted date
    // -----------------------------------------------------------------------

    /**
     * An empty MessageDate should fall through to the current date.
     */
    public function testEmptyMessageDateUsesCurrent()
    {
        $this->Mail->MessageDate = '';
        $value = $this->getDateHeaderValue();
        self::assertMatchesRegularExpression(
            self::RFC5322_PATTERN,
            $value,
            'Empty MessageDate should fall back to a valid RFC 5322 current date'
        );
    }

    /**
     * A pre-formatted RFC 5322 string supplied by the caller should be accepted
     * and re-emitted in the same format.
     *
     * @dataProvider dataValidPastDates
     *
     * @param mixed  $input    Value to assign to $Mail->MessageDate.
     * @param string $label    Human-readable description for assertion messages.
     */
    public function testValidPastDateAccepted($input, $label)
    {
        $this->Mail->MessageDate = $input;
        $value = $this->getDateHeaderValue();
        self::assertMatchesRegularExpression(
            self::RFC5322_PATTERN,
            $value,
            $label . ': expected a valid RFC 5322 date in the header'
        );
        // Must be a single-line value — no newlines whatsoever.
        self::assertStringNotContainsString("\r", $value, $label . ': Date value must not contain CR');
        self::assertStringNotContainsString("\n", $value, $label . ': Date value must not contain LF');
    }

    /**
     * Data provider: inputs that represent valid past dates and should each
     * produce a valid RFC 5322 date string in the header.
     *
     * @return array
     */
    public static function dataValidPastDates()
    {
        return [
            'Already RFC 5322 with day-of-week' => [
                'input' => 'Wed, 1 Jan 2020 00:00:00 +0000',
                'label' => 'Already RFC 5322 with day-of-week',
            ],
            'Already RFC 5322 without day-of-week' => [
                'input' => '1 Jan 2020 00:00:00 +0000',
                'label' => 'Already RFC 5322 without day-of-week',
            ],
            'ISO 8601 with timezone' => [
                'input' => '2022-06-15T14:30:00+01:00',
                'label' => 'ISO 8601 with timezone',
            ],
            'ISO 8601 UTC' => [
                'input' => '2021-12-31T23:59:59Z',
                'label' => 'ISO 8601 UTC',
            ],
            'Unix @timestamp (past)' => [
                'input' => '@1700000000',   // 2023-11-14
                'label' => 'Unix @timestamp (past)',
            ],
            'DateTime object' => [
                'input' => new DateTime('2022-03-10 09:00:00'),
                'label' => 'DateTime object',
            ],
            'DateTimeImmutable object' => [
                'input' => new DateTimeImmutable('2020-07-04 12:00:00'),
                'label' => 'DateTimeImmutable object',
            ],
            'Natural language: yesterday' => [
                'input' => 'yesterday',
                'label' => 'Natural language: yesterday',
            ],
            'Natural language: last week' => [
                'input' => 'last Monday',
                'label' => 'Natural language: last Monday',
            ],
        ];
    }

    // -----------------------------------------------------------------------
    // Tests: inputs that must fall back to the current date
    // -----------------------------------------------------------------------

    /**
     * Garbage / unparseable input should fall back to the current date.
     *
     * @dataProvider dataUnparseableDates
     *
     * @param mixed  $input Value to assign to $Mail->MessageDate.
     * @param string $label Human-readable description for assertion messages.
     */
    public function testUnparseableDateFallsBack($input, $label)
    {
        $this->Mail->MessageDate = $input;
        $value = $this->getDateHeaderValue();
        self::assertMatchesRegularExpression(
            self::RFC5322_PATTERN,
            $value,
            $label . ': expected a valid RFC 5322 current date on unparseable input'
        );
    }

    /**
     * Data provider: values that cannot be parsed as a date.
     *
     * @return array
     */
    public static function dataUnparseableDates()
    {
        return [
            'Completely invalid string' => [
                'input' => 'this is not a date',
                'label' => 'Completely invalid string',
            ],
            'Numbers only' => [
                'input' => '9999999999999',
                'label' => 'Numbers only (not a Unix timestamp with @ prefix)',
            ],
            'Null byte string' => [
                'input' => "Mon, 13 May 2020 10:00:00 +0000\0Bcc: evil@example.com",
                'label' => 'Null-byte embedded string',
            ],
        ];
    }

    // -----------------------------------------------------------------------
    // Tests: header injection attempts
    // -----------------------------------------------------------------------

    /**
     * Header injection attempts via MessageDate must not result in extra
     * headers appearing in the output and must not corrupt the Date header.
     *
     * @dataProvider dataInjectionAttempts
     *
     * @param string $input          Malicious MessageDate value.
     * @param string $injectedMarker A substring that must NOT appear in headers.
     * @param string $label          Human-readable description for assertion messages.
     */
    public function testHeaderInjectionPrevented($input, $injectedMarker, $label)
    {
        $this->Mail->MessageDate = $input;
        $headers = $this->Mail->createHeader();

        // The Date value itself must be a clean, single-line RFC 5322 string.
        $dateValue = '';
        if (preg_match('/^Date: (.+)$/m', $headers, $matches)) {
            $dateValue = rtrim($matches[1]);
        }
        self::assertMatchesRegularExpression(
            self::RFC5322_PATTERN,
            $dateValue,
            $label . ': Date header value must be a well-formed RFC 5322 string'
        );

        // The injected marker must not appear anywhere in the headers.
        self::assertStringNotContainsString(
            $injectedMarker,
            $headers,
            $label . ': injected content must not appear in the headers'
        );

        // No bare LF or CR should appear inside the Date header value.
        self::assertStringNotContainsString("\n", $dateValue, $label . ': Date value must not contain LF');
        self::assertStringNotContainsString("\r", $dateValue, $label . ': Date value must not contain CR');
    }

    /**
     * Data provider: header injection payloads.
     *
     * Each entry contains the malicious input, a marker string that must NOT
     * appear anywhere in the resulting headers, and a label.
     *
     * @return array
     */
    public static function dataInjectionAttempts()
    {
        // A recognisable value we can search for in the headers output.
        $markerAddr = 'evil@inject.example.com';
        $markerHdr  = 'X-Injected';

        return [
            'CRLF before Bcc' => [
                'input'          => "Wed, 1 Jan 2020 00:00:00 +0000\r\nBcc: $markerAddr",
                'injectedMarker' => $markerAddr,
                'label'          => 'CRLF before Bcc',
            ],
            'LF before Bcc' => [
                'input'          => "Wed, 1 Jan 2020 00:00:00 +0000\nBcc: $markerAddr",
                'injectedMarker' => $markerAddr,
                'label'          => 'LF before Bcc',
            ],
            'CR before Bcc' => [
                'input'          => "Wed, 1 Jan 2020 00:00:00 +0000\rBcc: $markerAddr",
                'injectedMarker' => $markerAddr,
                'label'          => 'CR before Bcc',
            ],
            'CRLF before X- header' => [
                'input'          => "Wed, 1 Jan 2020 00:00:00 +0000\r\n$markerHdr: injected",
                'injectedMarker' => $markerHdr,
                'label'          => 'CRLF before X- header',
            ],
            'Double CRLF (blank line, headers+body split)' => [
                'input'          => "Wed, 1 Jan 2020 00:00:00 +0000\r\n\r\n$markerHdr: injected",
                'injectedMarker' => $markerHdr,
                'label'          => 'Double CRLF (blank line, headers+body split)',
            ],
            'ISO 8601 with appended CRLF injection' => [
                'input'          => "2020-01-01T00:00:00+00:00\r\nBcc: $markerAddr",
                'injectedMarker' => $markerAddr,
                'label'          => 'ISO 8601 with appended CRLF injection',
            ],
            'Tab-folded fake header' => [
                'input'          => "Wed, 1 Jan 2020 00:00:00 +0000\r\n\tBcc: $markerAddr",
                'injectedMarker' => $markerAddr,
                'label'          => 'Tab-folded fake header',
            ],
            'Content-Type override attempt' => [
                'input'          => "Wed, 1 Jan 2020 00:00:00 +0000\r\nContent-Type: text/html",
                'injectedMarker' => 'Content-Type: text/html',
                'label'          => 'Content-Type override attempt',
            ],
            'Unicode line separator U+2028' => [
                'input'          => "Wed, 1 Jan 2020 00:00:00 +0000\xE2\x80\xA8Bcc: $markerAddr",
                'injectedMarker' => $markerAddr,
                'label'          => 'Unicode line separator U+2028',
            ],
            'Unicode paragraph separator U+2029' => [
                'input'          => "Wed, 1 Jan 2020 00:00:00 +0000\xE2\x80\xA9Bcc: $markerAddr",
                'injectedMarker' => $markerAddr,
                'label'          => 'Unicode paragraph separator U+2029',
            ]
        ];
    }

    // -----------------------------------------------------------------------
    // Tests: the Date header is always present and well-formed
    // -----------------------------------------------------------------------

    /**
     * Regardless of what MessageDate contains, createHeader() must always
     * produce exactly one Date: header line.
     *
     * @dataProvider dataAllMessageDateInputs
     *
     * @param mixed  $input Value to assign to $Mail->MessageDate.
     * @param string $label Human-readable description.
     */
    public function testDateHeaderAlwaysPresent($input, $label)
    {
        $this->Mail->MessageDate = $input;
        $headers = $this->Mail->createHeader();
        $count = preg_match_all('/^Date:/m', $headers);
        self::assertSame(1, $count, $label . ': headers must contain exactly one Date: line');
    }

    /**
     * Data provider: a union of all interesting inputs from the other providers.
     *
     * @return array
     * @throws \Exception
     */
    public static function dataAllMessageDateInputs()
    {
        $all = array_merge(
            self::dataValidPastDates(),
            self::dataUnparseableDates()
        );
        // Reduce injection payloads to just their input + label (drop the marker).
        foreach (self::dataInjectionAttempts() as $key => $row) {
            $all[$key] = ['input' => $row['input'], 'label' => $row['label']];
        }
        $all['empty string'] = ['input' => '', 'label' => 'Empty string'];
        return $all;
    }
}
