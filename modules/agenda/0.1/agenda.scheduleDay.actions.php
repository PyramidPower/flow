<?php

/**
 * Content of this file supposed to be shown in 'agewnda-schedule/' 'Day' tab.
 * **/

function scheduleDayView_GET(Web $w)
{
	$w->setLayout(null);

	// the rest is in the scheduleDay.tpl.php file
}


function  ajaxCurrDay_ALL(Web $w)
{
	// to have layout null
	// scheduleDay renamed to scheduleDayView because it called scheduleDay.tpl.php
	$w->setLayout(null);
	
	extract($w->pathMatch('dateStamp'));
	
	
	$res['updatedDateStamp'] = $dateStamp;
	$res['date'] = date('d/m/Y', $dateStamp);
	
	// All schedules to be displayed:
	if(isset($_POST['schedules'])){
		$sss = json_decode( $_POST['schedules'] );
	}
	
	// Get Day infor for selected scheds, default scheds will be shown if noll passed: 
	//$res['htmlDay'] =  ajaxDayHelper($dateStamp, $sss, $w);
	$res['htmlDay'] =  ajaxDayHelper($dateStamp, null, $w);
	
	//json_encode('sss'); //aDebug($sss)
	/*
	if(is_array($_POST['schedules'])){
		foreach($_POST['schedules'] as $pp)
		{
			$check .= ",  ".$pp;
		}
	}
	*/
	
	//$res['htmlDay'] = $check;
	
	echo json_encode($res);
	
}


/*
 * returns updated date = prevDate - to be assigned to var unix on .tpl.php 
 * */
function  ajaxPrevDay_POST(Web $w)
{
	// to have layout null
	// scheduleDay renamed to scheduleDayView because it called scheduleDay.tpl.php
	$w->setLayout(null);
	
	extract($w->pathMatch('dateStamp'));
	
	$prevDateStamp = strtotime('-1 day', $dateStamp);
	
	$res['updatedDateStamp'] = $prevDateStamp;
	$res['date'] = date('d/m/Y', $prevDateStamp);
	
	// All schedules to be displayed:
	if(isset($_POST['schedules'])){
		$sss = json_decode( $_POST['schedules'] );	
	}
	
	
	
	//$res['htmlDay'] = ajaxDayHelper($prevDateStamp, $sss, $w);
	$res['htmlDay'] = ajaxDayHelper($prevDateStamp, null, $w);
	
	
	echo json_encode($res);
	
	/*
		try {
			//$res['htmlDay'] = ajaxDayHelper($prevDateStamp, $sss, $w);
			echo json_encode($res);
		}
		catch (Exception $e)
		{
		    echo json_encode($e->getMessage());
		}
	
	*/
	
}


function  ajaxNextDay_POST(Web $w)
{
	// to have layout null
	// scheduleDay renamed to scheduleDayView because it called scheduleDay.tpl.php
	$w->setLayout(null);
	
	extract($w->pathMatch('dateStamp'));
	
	$nextDateStamp = strtotime('+1 day', $dateStamp);
	
	$res['updatedDateStamp'] = $nextDateStamp;
	$res['date'] = date('d/m/Y', $nextDateStamp);
	
	// All schedules to be displayed:
	if(isset($_POST['schedules'])){
		$sss = json_decode( $_POST['schedules'] );	
	}
	
	
	//$res['htmlDay'] = ajaxDayHelper($nextDateStamp, $sss, $w);
	$res['htmlDay'] = ajaxDayHelper($nextDateStamp, null, $w);
	
	
	echo json_encode($res);
	
}





function ajaxDayHelper($dateStamp, $schedsIDs,  Web $w)
{
	
	if(!$dateStamp) return 'no date';
	
	$date = Date('Y-m-d', $dateStamp);
	
	// get all schedules currently checked on the Schedules view:
	$scheds = $w->Agenda->getSelectedScheds($schedsIDs);
	
	$events = array();
	 
	if($scheds && is_array($scheds)){
		
		foreach ($scheds as $sched){
			
			$evs = $sched->getDayEvents($dateStamp);
			//$color = $ev->getColor();
			if(is_array($evs)){
				$events = array_merge($events, $evs);
			}
			//$events[] = $ev;
			
		}
		
		if( count($events)>0)
		{
			//sort events by start DateTime:
			if( usort($events, "cmpAgEventsByStart"))
			{
				$w->ctx('events',$events);
				
			}
		}else{
			$w->ctx('events',null);
		} 
	
		
   		
		
	}else{
		$w->ctx('noScheds','No schedules found');
	}
	
	$w->ctx('dateStamp',$dateStamp);
	$html = $w->fetchTemplate('scheduleDay/ajaxDayHelper'); //"<br>actionHelperDate: $date<br>html: ".
	
	return $html;
	
}









