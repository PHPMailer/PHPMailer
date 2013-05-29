<?php

/* ------------------------------------------------------------------------ */
/* EasyPeasyICS
/* ------------------------------------------------------------------------ */
/* Manuel Reinhard, manu@sprain.ch
/* Twitter: @sprain
/* Web: www.sprain.ch
/*
/* Built with inspiration by
/" http://stackoverflow.com/questions/1463480/how-can-i-use-php-to-dynamically-publish-an-ical-file-to-be-read-by-google-calend/1464355#1464355
/* ------------------------------------------------------------------------ */
/* History:
/* 2010/12/17 - Manuel Reinhard - when it all started
/* ------------------------------------------------------------------------ */  

class EasyPeasyICS {

	protected $calendarName;
	protected $events = array();
	

	/**
	 * Constructor
	 * @param string $calendarName
	 */	
	public function __construct($calendarName=""){
		$this->calendarName = $calendarName;
	}//function


	/**
	 * Add event to calendar
	 * @param string $calendarName
	 */	
	public function addEvent($start, $end, $summary="", $description="", $url=""){
		$this->events[] = array(
			"start" => $start,
			"end"   => $end,
			"summary" => $summary,
			"description" => $description,
			"url" => $url
		);
	}//function
	
	
	public function render($output = true){
		
		//start Variable
		$ics = "";
	
		//Add header
		$ics .= "BEGIN:VCALENDAR
METHOD:PUBLISH
VERSION:2.0
X-WR-CALNAME:".$this->calendarName."
PRODID:-//hacksw/handcal//NONSGML v1.0//EN";
		
		//Add events
		foreach($this->events as $event){
			$ics .= "
BEGIN:VEVENT
UID:". md5(uniqid(mt_rand(), true)) ."@EasyPeasyICS.php
DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z
DTSTART:".gmdate('Ymd', $event["start"])."T".gmdate('His', $event["start"])."Z
DTEND:".gmdate('Ymd', $event["end"])."T".gmdate('His', $event["end"])."Z
SUMMARY:".str_replace("\n", "\\n", $event['summary'])."
DESCRIPTION:".str_replace("\n", "\\n", $event['description'])."
URL;VALUE=URI:".$event['url']."
END:VEVENT";
		}//foreach
		
		
		//Footer
		$ics .= "
END:VCALENDAR";


		if ($output) {
			//Output
			header('Content-type: text/calendar; charset=utf-8');
			header('Content-Disposition: inline; filename='.$this->calendarName.'.ics');
			echo $ics;
		} else {
			return $ics;
		}

	}//function

}//class