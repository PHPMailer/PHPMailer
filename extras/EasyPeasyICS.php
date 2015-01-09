<?php
/**
 * EasyPeasyICS Simple ICS/vCal data generator.
 * @author Marcus Bointon <phpmailer@synchromedia.co.uk>
 * @author Manuel Reinhard <manu@sprain.ch>
 *
 * Built with inspiration from
 * http://stackoverflow.com/questions/1463480/how-can-i-use-php-to-dynamically-publish-an-ical-file-to-be-read-by-google-calend/1464355#1464355
 * History:
 * 2010/12/17 - Manuel Reinhard - when it all started
 * 2014 PHPMailer project becomes maintainer
 */

/**
 * Class EasyPeasyICS.
 * Simple ICS data generator
 * @package phpmailer
 * @subpackage easypeasyics
 */
class EasyPeasyICS
{
    /**
     * The name of the calendar
     * @type string
     */
    protected $calendarName;
    /**
     * The array of events to add to this calendar
     * @type array
     */
    protected $events = array();

    /**
     * Constructor
     * @param string $calendarName
     */
    public function __construct($calendarName = "")
    {
        $this->calendarName = $calendarName;
    }

    /**
     * Add an event to the calendar.
     * @param string $start Time string for the start date and time - anything that strtotime() can parse
     * @param string $end Time string for the end date and time - anything that strtotime() can parse
     * @param string $summary A summary of the event
     * @param string $description A description of the event
     * @param string $url A URL for the event
     */
    public function addEvent($start, $end, $summary = "", $description = "", $url = "")
    {
        $this->events[] = array(
            "start" => $start,
            "end" => $end,
            "summary" => $summary,
            "description" => $description,
            "url" => $url
        );
    }

    /**
     * Render a vcal string.
     * @param bool $output Whether to output the calendar data directly (the default) or to return it.
     * @return string
     */
    public function render($output = true)
    {
        $ics = '';

        //Add header
        $ics .= "BEGIN:VCALENDAR
METHOD:PUBLISH
VERSION:2.0
X-WR-CALNAME:" . $this->calendarName . "
PRODID:-//hacksw/handcal//NONSGML v1.0//EN";

        //Add events
        foreach ($this->events as $event) {
            $ics .= "
BEGIN:VEVENT
UID:" . md5(uniqid(mt_rand(), true)) . "@EasyPeasyICS.php
DTSTAMP:" . gmdate('Ymd') . 'T' . gmdate('His') . "Z
DTSTART:" . gmdate('Ymd', $event["start"]) . "T" . gmdate('His', $event["start"]) . "Z
DTEND:" . gmdate('Ymd', $event["end"]) . "T" . gmdate('His', $event["end"]) . "Z
SUMMARY:" . str_replace("\n", "\\n", $event['summary']) . "
DESCRIPTION:" . str_replace("\n", "\\n", $event['description']) . "
URL;VALUE=URI:" . $event['url'] . "
END:VEVENT";
        }

        //Add footer
        $ics .= "
END:VCALENDAR";

        if ($output) {
            //Output
            header('Content-type: text/calendar; charset=utf-8');
            header('Content-Disposition: inline; filename=' . $this->calendarName . '.ics');
            echo $ics;
            return '';
        } else {
            return $ics;
        }
    }
}
