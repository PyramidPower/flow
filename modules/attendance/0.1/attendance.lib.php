<?php

// build the Attendance navigation
function attendance_navigation(&$w,$title = null,$nav=null) {
	if ($title) {
        $w->ctx("title",$title);
   	}
  
    $nav = $nav ? $nav : array();

    if ($w->auth->loggedIn()) {
    	$w->menuLink("attendance/index","Attendance Dashboard",$nav);
    	$w->menuLink("attendance-manager/index","Managers Administration",$nav);
    }

    $w->ctx("navigation", $nav);
  }

