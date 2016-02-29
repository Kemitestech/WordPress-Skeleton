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
	
	
	public function render(){
                        
		
	
		//Add header
		$ics = "BEGIN:VCALENDAR
VERSION:2.0
METHOD:PUBLISH
X-WR-CALNAME:".$this->calendarName."
PRODID:-//hacksw/handcal//NONSGML v1.0//EN";
		
		//Add events
                //removed Z from 65 - 67
		foreach($this->events as $event){
			$ics .= "
BEGIN:VEVENT
UID:". md5(uniqid(mt_rand(), true)) ."@eplicalgen.php
DTSTAMP:" . gmdate('Ymd').'T'. gmdate('His') . "Z
DTSTART:".date_i18n('Ymd', $event["start"])."T".date_i18n('His', $event["start"])."
DTEND:".date_i18n('Ymd', $event["end"])."T".date_i18n('His', $event["end"])."
SUMMARY:".str_replace(array(',','"',"'"), array('\,','\"',"'"), $event['summary'])."
URL;VALUE=URI:".$event['url']."
END:VEVENT";
		}//foreach
		
//DESCRIPTION:".str_replace("\n", "\r\n", $event['description'])."		
		//Footer
		$ics .= "
END:VCALENDAR";

$ics = str_replace("\n","\r\n", $ics);
		//Output
		header('Content-type: text/calendar; charset=utf-8');
		header('Content-Disposition: inline; filename='.$this->calendarName.'.ics');
		echo $ics;

	}//function

}//class