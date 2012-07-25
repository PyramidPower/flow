<?php

function index_GET(Web $w)
{
	// if ?tab=X  was passed from POST it will be open on index.tpl.php js:
	$w->ctx('tab', $w->request('tab'));
	// otherwise default view according to user settings:
	$uid = $w->auth->user()->id;
	$defaultViewArr = $w->Agenda->getAgs('AgUserSettings',array('user_id'=>$uid, 'title'=>'defaultView'));
	$defaultView = $defaultViewArr[0]->value;
	
	$w->ctx('defaultView', $defaultView);
	
	
	//<label for='2'>Department Schedule</label><input type='checkbox' value='dep' id='2' name="schedules[]" />
	
	// show all user schedules in the list:
	$scheds = $w->Agenda->getUserScheds();
	
	
	if($scheds){
		foreach($scheds as $k=>$s){
			if($s->isUserDefault($uid))
			{
				$checks[] =  
				"<input type='checkbox' value='{$s->id}' id='{$s->id}' name='schedules[]' checked='checked'/><label for=$k>{$s->title}</label>";
			}else{
				$checks[] =  
				"<input type='checkbox' value='{$s->id}' id='{$s->id}' name='schedules[]' /><label for=$k>{$s->title}</label>";
			}
			
		}
		
	$all ="		
			<input type='checkbox' value='all' id='5' name='schedules[]' />
			<label for='5'> ALL</label><br>
	<button id='displaySchedules'>Display</button>";
	
	array_push($checks, $all);
	
		$w->ctx('checks', $checks);
		$w->ctx('scheds', $scheds);
	}
	
	
		
	// calling .lib.php 
	agenda_navigation($w, "Schedules", $checks);
	//agenda_navigation($w, "Schedules");	
	
	// All tabs and js functionality are in .tpl.php
	
}

function schedsList_GET(Web $w)
{
	// calling .lib.php 
	agenda_navigation($w, "Schedules");
	
	$w->ctx('newSchedButton', Html::box($w->localUrl("/agenda-schedule/createSchedule"),"Create New Schedule",true) );
	
	//Schedule objs for the current user:
	$scheds = $w->Agenda->getUserScheds();
	
	if($scheds)
	{
		$schedsTable = array(array('Title','Controls'));
		
		foreach ($scheds as $s){
			$line = null;
			$line[] = $s->title;
			//$schedsArray[$t] = $s->getColor();
			$controls = Html::b($w->localUrl("/agenda-schedule/setDefaultSched/{$s->id}"),"Set as a Default");
			$controls .= Html::b($w->localUrl("/agenda-schedule/setDefaultSched/{$s->id}"),"Delete",'Delete this schedule ?');
			$line[] = $controls;
			$schedsTable[] = $line;
		}
	}
	
	$w->ctx('schedsTable',Html::table( $schedsTable,null,"tablesorter",true));
	
}






function setDefaultSched_GET(Web $w)
{
	extract($w->pathMatch('sid'));
	
	$sched = $w->Agenda->getAgs('AgSchedule', $sid);
	
	
	if($sched){
		$uid = $w->auth->user()->id;	
		$setting = $w->Agenda->getAgs('AgUserSettings',array('user_id'=>$uid,'sched_id'=>$sid,'title'=>'defaultScheds'));

		//var_dump($setting);
		//aDebug($setting);
		if(!is_array($setting)){
			//var_dump($setting);
			
			$setting = new AgUserSettings($w);
			$setting->user_id = $uid;
			$setting->sched_id = $sid;
			$setting->title = 'defaultScheds';
			$setting->value = 1;
			$setting->insert(); 
		}
		
		$w->msg("Schedule set as a default.","/agenda-schedule/schedsList/");
	}else{
		$w->error("Error.","/agenda-schedule/schedsList/");
	}
}





function createSchedule_GET(Web $w)
{
	$w->setLayout(null);
	
	$form = array(
           array("Schedule","section"),
           array("Title","text","title"),
           array("Is a group schedule","checkbox","is_group_sch")
    );

    $form = Html::form($form,$w->localUrl("/agenda-schedule/createSchedule/"),"POST","Save");  
    
    $w->out($form);
}



function createSchedule_POST(Web $w)
{
	$ag = new AgSchedule($w);
    $ag->fill($_REQUEST);
    $ag->owner_user_id = $w->auth->user()->id;
    $ag->insert();
    $w->msg("Schedule added.","/agenda-schedule/schedsList/");
    $m = "Schedule updated";        
    
	$w->msg($m,"/agenda-schedule/schedsList/");
}



function editSched_GET(Web $w)
{
	
}



function editSched_POST(Web $w)
{
//$ag = $w->Agenda->getAgs("AgSchedule", $sid);  

    if ($ag) 
    {
        $ag->fill($_REQUEST);
        $ag->update();
        $m = "Schedule added.";
    }
}




function filesUploading_GET(Web $w)
{
	agenda_navigation($w, "Schedules");
	// .tpl.php
	
}

function filesUploadingRes_POST(Web $w)
{
	//agenda_navigation($w, "Schedules");
	$w->setLayout(null);
	
	aDebug($_FILES);
	
	aDebug($_POST);
	
}

function ajaxCreateEvent_POST(Web $w)
{
	$w->setLayout(null);
	
	$ev = new AgEvent($w);
	$ev->title = $_POST['title'];
	$ev->owner_user_id = $_POST['owner_user_id'];
	$ev->schedule_id = $_POST['schedule_id'];
	$ev->type = $_POST['type'];
	
	$ev->busy = $_POST['busy'];
	
		$parts = explode(' ',$_POST['start']);
		$date = explode('/',$parts[0]);
		$time = explode(':', $parts[1]);
		
	 	$hrs = $time[0];
	 	if($parts[2]=='pm') {
	 		if($hrs!=12){$hrs+=12;}
	 	} 
		$min = $time[1];
		
		$mysqlStartDateTime = mktime($hrs, $min, 0, $date[1], $date[0], $date[2]); //mktime($hrs, $min, 0, $month, $day, $year);
		
	$ev->dt_start = $mysqlStartDateTime;
   
		$parts = explode(' ',$_POST['end']);
		$date = explode('/',$parts[0]);
		$time = explode(':', $parts[1]);
		
	 	$hrs = $time[0];
	 	if($parts[2]=='pm'){
	 		if($hrs!=12){$hrs+=12;}
	 	} 
		$min = $time[1];
		
	$ev->dt_end = mktime($hrs, $min, 0, $date[1], $date[0], $date[2]); 
	
    $ev->insert();
    
    if($ev->id){
    	echo 'ok';
    }else{
    	echo 'error';
    }
    
}

function ajaxEditEvent_POST(Web $w)
{
	$w->setLayout(null);
	
	$eid = $_POST['eid'];
	
	$ev = $w->Agenda->getAgs('AgEvent', $eid);
	
	if($ev)
	{
		$ev->title = $_POST['title'];
		$ev->owner_user_id = $_POST['owner_user_id'];
		$ev->schedule_id = $_POST['schedule_id'];
		$ev->type = $_POST['type'];
		
		
		$ev->busy = $_POST['busy'];
		
			$parts = explode(' ',$_POST['start']);
			$date = explode('/',$parts[0]);
			$time = explode(':', $parts[1]);
			
		 	$hrs = $time[0];
		 	if($parts[2]=='pm'){
		 		if($hrs!=12){$hrs+=12;}
		 	} 
			$min = $time[1];
			
			$mysqlStartDateTime = mktime($hrs, $min, 0, $date[1], $date[0], $date[2]); //mktime($hrs, $min, 0, $month, $day, $year);
			
		$ev->dt_start = $mysqlStartDateTime;
	   
			$parts = explode(' ',$_POST['end']);
			$date = explode('/',$parts[0]);
			$time = explode(':', $parts[1]);
			
		 	$hrs = $time[0];
		 	if($parts[2]=='pm'){
		 		if($hrs!=12){$hrs+=12;}
		 	} 
			$min = $time[1];
			
		$ev->dt_end = mktime($hrs, $min, 0, $date[1], $date[0], $date[2]); 
		
	    $ev->update();

    	echo 'ok';
    	
	}else{
		echo 'error';
	}
}


function ajaxDeleteEvent_POST(Web $w)
{
	$w->setLayout(null);
	
	$eid = $_POST['eid'];
	
	$ev = $w->Agenda->getAgs('AgEvent', $eid);
	
	if($ev){
		$ev->delete();
		echo 'ok';	
	}else{
		echo 'error';
	}

}















