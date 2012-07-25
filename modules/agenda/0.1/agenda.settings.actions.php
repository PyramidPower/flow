<?php


function userSettings_GET(Web $w)
{
	$w->setLayout(null);

	  // check what is hours limits settings:
	$uid = $w->auth->user()->id;
	$hStartSetArr = $w->Agenda->getAgs('AgUserSettings',array('user_id'=>$uid, 'title'=>'userHrsStart'));
	$hStartSet = $hStartSetArr[0]->value;
	if($hStartSet){
		if($hStartSet < 12){
			$hStartSet .= ":00 am";
		}else{
			$hStartSet = ($hStartSet-12).":00 pm";
		}
	}else{
		$hStartSet = "12:00 am";
	}
	
	
	
	$hEndSetArr = $w->Agenda->getAgs('AgUserSettings',array('user_id'=>$uid, 'title'=>'userHrsEnd'));
	$hEndSet = $hEndSetArr[0]->value;
	if($hEndSet){
		if($hEndSet < 12){
			$hEndSet .= ":00 am";
		}else{
			$hEndSet = ($hEndSet-12).":00 pm";
		}
	}else{
		$hEndSet = "11:00 pm";
	}
	
	
	$timeTable = array(array('Day start time','Day end time','Controls'));
	
	$line[] = $hStartSet;
	$line[] = $hEndSet;
	
	$line[] = Html::box($w->localUrl("/agenda-settings/setDisplayTime/$hStartSet/$hEndSet"),"Change",true);
	
	$timeTable[] = $line;

	$w->ctx('timeTable', Html::table( $timeTable,null,"tablesorter",true));
	
	$line = null;
	
	
	
	$sTable = array(array('Current Settings', 'Changes'));
	
	// ------------------------------
	//      Time Limits settings
	//-------------------------------
	$line[] = 'Day and week(s) views shows: '.$hStartSet.' - '.$hEndSet;
	//Html::timePicker($name,$value,$size);
	$inpStart = Html::timePicker("userHrsStart",$hStartSet,'9');
	$inpEnd = Html::timePicker("userHrsEnd",$hEndSet,'9');
	
	$form = "<form method='POST' action='{$w->localUrl("/agenda-settings/setDisplayTime/")}' name='timeChange'>";
	$form.= "New settings: ".$inpStart." - ".$inpEnd;
	$form.= "<input type='submit' value='Change'>";
	$form.= "</form>";
	$line[] = $form;  
	
	$sTable[] = $line;
	$line = null;
	
	//--------------------------------
	//      Default view settings
	//--------------------------------
	$defaultViewArr = $w->Agenda->getAgs('AgUserSettings',array('user_id'=>$uid, 'title'=>'defaultView'));
	$defaultView = $defaultViewArr[0]->value;
	//$defaultViewAll = array('Day'=>0,'Week'=>1, '6 Weeks'=>2, 'Year'=>3, 'Agenda'=>4);
	$defaultViewAll = array('Day','Week', '6 Weeks', 'Year', 'Agenda');
	$index = $defaultViewArr[0]->value;
	$v = $index ? $defaultViewAll[$index] : ' is not set';
	if($index == 0) {$v = 'Day';}
	
	$line[] = "Deafault view: ".$v;
	
	$form = "<form method='POST' action='{$w->localUrl("/agenda-settings/setDefaultView/")}' name='viewChange'>";
	
	$form .= "<select name='defaultView'>";
	
	foreach($defaultViewAll as $k=>$view){
		
		if($k == $index){
			$form .= "<option value='{$k}' selected='selected'>";
		}else{
		$form .= "<option value='{$k}'>";
		}
		
		$form .= $view."</option>";
	}
	
	$form .= "</select>";
	//$form.= Html::select('defaultView', $defaultViewAll, $defaultView, null, null, null); 
	// select($name, $items, $value=null, $class=null, $style=null, $allmsg = "-- Select --")
	$form.= "<input type='submit' value='Change'>";
	$form.= "</form>";
	$line[] = $form; 
	
	$sTable[] = $line;
	$line = null;
	
	
	/*
	 * <input type="radio" name="group2" value="Water"> Water<br>
<input type="radio" name="group2" value="Beer"> Beer<br>
<input type="radio" name="group2" value="Wine" checked> Wine<br>
	 * */
	
	
	$w->ctx('sTable', Html::table( $sTable,null,"tablesorter",true));
	
	

}





function setDefaultView_POST(Web $w)
{
	$uid = $w->auth->user()->id;
	
	$setArr = $w->Agenda->getAgs('AgUserSettings',array('user_id'=>$uid, 'title'=>'defaultView'));
	$set1 = $setArr[0];
	
	if($set1){
		$set1->value = $w->request('defaultView');
		$set1->update();
	}else{
		$set1 = new AgUserSettings($w);
		$set1->user_id = $uid;
		$set1->sched_id = 0;
		$set1->title = 'defaultView';
		$set1->value = $w->request('defaultView');
		$set1->insert();
	}
	
	$w->msg('view settings updated',"/agenda-schedule/?tab=5");
}

/*
function setDisplayTime_GET(Web $w)
{
	$w->setLayout(null);
	
	extract($w->pathMatch('start','end'));
	
	$start=substr_replace($start, ' ', '-2', 0);
	$end=substr_replace($end, ' ', '-2', 0);
	
	$form = array(
           array("Display Settings","section"),
           array("Day start time","time","userHrsStart",$start),
           array("Day end time","time","userHrsEnd",$end)
    );

    $form = Html::form($form,$w->localUrl("/agenda-settings/setDisplayTime/"),"POST","Save");  
    
    $w->out($form);
	
}
*/



function setDisplayTime_POST(Web $w)
{
	$uid = $w->auth->user()->id;
	
	$setArr = $w->Agenda->getAgs('AgUserSettings',array('user_id'=>$uid, 'title'=>'userHrsStart'));
	$set1 = $setArr[0];
	
	if($set1){
		$doit1 = 'update';
	}else{
		$set1 = new AgUserSettings($w);
	}
	
	$setArr = $w->Agenda->getAgs('AgUserSettings',array('user_id'=>$uid, 'title'=>'userHrsEnd'));
	$set2 = $setArr[0];
	if($set2){
		$doit2 = 'update';
	}else{
		$set2 = new AgUserSettings($w);
	}
	
	$set1->user_id = $uid;
	$set2->user_id = $uid;
	
	$set1->sched_id = 0;
	$set2->sched_id = 0;
	
	$set1->title = 'userHrsStart';
	
	$parts = explode(' ', $w->request('userHrsStart'));
	$timeStart = explode(':', $parts[0]);
	 
	$hrs = (int)$timeStart[0];
	if($parts[1]=='pm'){
		if($hrs!=12){$hrs+=12;}
	}
	if($parts[1]=='am'){
		if($hrs!=12){$hrs = 0;}
	}
	$set1->value = $hrs;
	
	
	
	
	$set2->title = 'userHrsEnd';
	//$timeEnd = explode(':', $w->request('userHrsEnd'));
	$parts = explode(' ', $w->request('userHrsEnd'));
	$timeEnd = explode(':', $parts[0]);
	 
	$hrs = (int)$timeEnd[0];
	if($parts[1]=='pm'){
		if($hrs!=12){$hrs+=12;}
	}
	
	$set2->value = $hrs;
	
	
	
	
    if($doit1 == 'update')
    {
    	$set1->update();
    }else{
    	$set1->insert();
    }      
    
    if($doit2=='update'){
    	$set2->update();
    }else{
    	$set2->insert();
    }
    
	$w->msg('time settings updated',"/agenda-schedule/?tab=5");
}























