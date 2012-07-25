<?php


function auHolidaysList_GET(Web $w)
{
	// calling .lib.php 
	agenda_navigation($w, "Holidays");
	
	// display table
	// calling .lib.php 
	$holidaysTable = array(array('Title','Date','State','Controls'));
	
	$hArray = $w->Agenda->getAgs('AgendaHoliday');

	if(! is_array($hArray)) $w->error('No holidays found', '/agenda-holidays/addHoliday');
	
	foreach($hArray as $h)
	{
		$line = array();
		$line[] = $h->title;
		$line[] = date('d/m/Y', $h->dt_date) ;
		$line[] = $h->state;
		$controls  = Html::box($w->localUrl("/agenda-holidays/editHoliday/$h->id"),"Edit",true) ;
		$controls .= Html::b($w->localUrl("/agenda-holidays/deleteHoliday/$h->id"),"Delete",'Do you want to DELETE this record ?') ;
		$line[] = $controls;
		
		$holidaysTable[] = $line;
	}
	
	$w->ctx('holidaysTable', Html::table( $holidaysTable,null,"tablesorter",true));
}







function deleteHoliday_GET(Web $w)
{
	extract($w->pathMatch('hid'));
	
	$h = $w->Agenda->getAgs('AgendaHoliday', $hid);
	
	if(!$h) $w->error("Object not Found.","/agenda-holidays/auHolidaysList/");

	$h->delete();
	$w->msg("Holiday deleted.","/agenda-holidays/auHolidaysList/");
}







function editHoliday_GET(Web $w)
{
	extract($w->pathMatch('hid'));
	
	$h = $w->Agenda->getAgs('AgendaHoliday', $hid);
	
	if(!$h) $w->error("Object not Found.","/agenda-holidays/auHolidaysList/");
	
	$w->ctx('h', $h);
	
	$w->setLayout(null);
	$form = $w->fetchTemplate('holidays/editHoliday');
	$w->out($form);
}




function editHoliday_POST(Web $w)
{
	extract($w->pathMatch('hid'));
	
	$h = $w->Agenda->getAgs('AgendaHoliday', $hid);
	
	if(!$h) $w->error("Object not Found.","/agenda-holidays/auHolidaysList/");
	
	$h->fill($_REQUEST);
	
	$statesArr = getStateSelectArray();
	foreach($statesArr as $s)
	{
		$n = strtolower($s[0]);
		$h->$n = 0;
	}
	
	
	if($_POST['states']){
		foreach($_POST['states'] as $st){
			$h->$st = 1;
		}
	}
	
	if($_POST['national']){
		$h->national = 1;
	}else{
		$h->national = 0;
	}
	
	$h->update();
	
	$w->msg("Updated","/agenda-holidays/auHolidaysList/");
}




function addHoliday_GET(Web $w)
{
	// calling .lib.php 
	agenda_navigation($w, "Holidays");
	//-------------------------------------------
	//	Access control
	//-------------------------------------------
	$is_allowed = $w->auth->user()->hasAnyRole(array('agenda_manager','agenda_manageholidayscal'));
	if(! $is_allowed) $w->error('access restricted', '/agenda/');
} 





function ajaxAddHoliday_POST(Web $w)
{
	$h = new AgendaHoliday($w);

 	$h->fill($_REQUEST);
  
 	$h->insert();
 	
 	if($h->id)
 	{
 		 $msg = 'ok';
 	}else{
 		 $msg = 'error';
 	}
 	
	$w->setLayout(null);
	echo $msg;
} 


