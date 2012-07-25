<?php

//------------------ NAVIGATION----------------

function agenda_navigation(&$w,$title = null,$checks=null) {
	if ($title) {
		$w->ctx("title",$title);
	}

	//$nav = $nav ? $nav : array();
	$nav = array();

	if ($w->auth->loggedIn()) {
		
		$schedule = array();
		$w->menuLink("agenda-schedule/","Calendar",$schedule);
		//$boxes["Calendars"]=Html::ul($schedule,null,"navlinks");
		
		$w->menuLink("agenda-schedule/filesUploading/","UploadingTest",$schedule);
		//$boxes["Calendars"]=Html::ul($schedule,null,"navlinks");
		
		$w->menuLink("agenda-schedule/schedsList","Schedules",$schedule);
		
		
		
		if($checks)
		{
			$hr = array("<hr style='width:90%; position:relative; left:-5px;' />");
			$schedule = array_merge($schedule, $hr);
			$schedule = array_merge($schedule, $checks);
			
		}
		
		$boxes["Calendars"]=Html::ul($schedule,null,"navlinks");
		
		//$w->menuLink("agenda/index","Dashboard",$nav); //

		$is_allowed = $w->auth->user()->hasAnyRole(array('agenda_manager','agenda_manageholidayscal'));
		if($is_allowed)
		{
			$holidays = array();
			$w->menuLink("agenda-holidays/auHolidaysList","Holidays",$holidays);
			
			$w->menuLink("agenda-holidays/addHoliday","Add Holiday",$holidays);
			$boxes["Holidays"]=Html::ul($holidays,null,"navlinks");
		}
		
		$w->ctx("boxes",$boxes);
		
	}
	$w->ctx("navigation", $nav);

} 




function cmpAgEventsByStart($a, $b)
    {
        if ($a['dt_start'] == $b['dt_start']) {
        	return 0;
        }
        return ($a['dt_start'] > $b['dt_start']) ? +1 : -1;
    }











