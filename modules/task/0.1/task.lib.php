<?php

// build the Task navigation
function task_navigation(&$w,$title = null,$nav=null) {
	if ($title) {
        $w->ctx("title",$title);
    	}
  
    $nav = $nav ? $nav : array();

    if ($w->auth->loggedIn()) {
    	$w->menuLink("task/index","Task Dashboard",$nav);
    	$w->menuLink("task/tasklist","Task List",$nav);
    	$w->menuLink("task/createtask","Create a Task",$nav);
    	$w->menuLink("task/taskweek","Task Activity",$nav);
    	$w->menuLink("task-group/viewtaskgrouptypes","Groups Administration",$nav);
	    }
    $w->ctx("navigation", $nav);
  }

