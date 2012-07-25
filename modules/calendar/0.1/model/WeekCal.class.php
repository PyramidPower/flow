<?php
########################################################
#####												####
#####   C L A S S  Week   C A L E N D A R 			####
#####												####
########################################################

class WeekCal
{
	private $date;
	private $action;
	private $navForm;
	private $daysArray;
	private $view_week;
	// to start week with Sunday:
	private $daysShift;
	private $daysTitles=array();
		
	public function getNavigationForm()
	{
		return $this->navForm;
	}
	
	public function getDaysTitles()
	{
		return $this->daysTitles;
	}
	
	public function getDaysArray()
	{
		return $this->daysArray;
	}
	
	
	/**
	 * @param timestamp $dateStamp The calendar will be created for this date's week
	 * @param string 	$action will be set as action for navigation form
	 * @param array  	$hiddenFields = array('hiddenFieldName1'=>hiddenFieldValue, 'hiddenFieldName2'=>hiddenFieldValue)
	 * 
	 * @return null, function just set $this->navForm, $this->daysArray
	 * 
	 *  		
 	 * **/
	public function __construct($dateStamp, $action, $hiddenFields=null, $highlightDate=null, $title=null)
	{
		$this->w = $w;
		$this->date = $dateStamp;
		$this->action = $action;
		//$this->daysShift=1;
		
		
		$this->daysTitles = array('Monday', 'Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday');
		
		
	//---------------------------------------------------------
	//--   Week Navigation
	//--  
	//---------------------------------------------------------
		if(!$dateStamp)
		{
			$dateStamp = time();	
		}
		$displaydate = date('F', $dateStamp);
		
		// current month info
		$mm = date('m', $dateStamp);
		$dd = date('d', $dateStamp);
		$yy = date('Y', $dateStamp);
		
		//week day for the passed dateStamp 
		$wday = date("w", $dateStamp);
		
		// weekday of the 1st date of the month
		// will be used for the first week - for prev month calculation:
		//$weekday = date("w", mktime(0, 0, 0, $mm, 1, $yy));
		$weekday = date("N", mktime(0, 0, 0, $mm, 1, $yy));
		
		//aDebug("Date passed: ".$dd." - ".$mm." ".$weekday);
		

		//prev month must be calculated for the first date of the current month, 
		// OR for current date = 2010-12-31 prevDate will be just 2010-12-01 !  
		//the calendar start with prev Month timeStamp:
		$prevStamp = strtotime("-1 month", mktime(0,0,0, $mm, 1, $yy));
		$pm = date('m',$prevStamp);
		$py = date('y',$prevStamp);
			
		$prevWeekStamp = strtotime("-1 week", $dateStamp);
		
		$nextWeekStamp = strtotime("+1 week", $dateStamp);
			
		$f = "<form id='calNav' action=$this->action method='POST'>";
		$f .= "<input type='hidden' name='prevDate' value='$prevWeekStamp'>";
		$f .= "<input type='hidden' name='nextDate' value='$nextWeekStamp'>";
			
		$f .= "<input type='submit' name='prevSubmit' value=' Prev' style='border:none; background: no-repeat url(".$webroot."/img/calendar/arrowleft.gif');'>";
		$f .= "<b>".$displaydate."</b>";
		$f .= " <input type='submit' name='nextSubmit' value=' Next ' style='border:none; background: 100% 0 no-repeat url(".$webroot."/img/calendar/arrowright.gif');'>";
		
		// all hidden fields:
		if($hiddenFields)
		{
			while($key = key($hiddenFields)) 
			{
			    $name = $key;
			    $val = $hiddenFields[$key];
			    $f .= "<input type='hidden' name='$name' id='$name' value='$val'>";
			    next($hiddenFields);
  			}
		}
		
		$f .= "</form>";
			
		$this->navForm = $f;

		//-------------------------------------------------------
		//
		//			Week Days Array
		//
		//-------------------------------------------------------
		// week to display  
		//$this->view_week = date("W", mktime(0, 0, 0, $mm, ($dd), $yy));
		
		// for timeShift +1 day. As sunday is belong to the next week:
		//$this->view_week = date("W", strtotime("+1 day", $dateStamp));  
		$this->view_week = date("W", $dateStamp);
		
		
		
		

//		$prevD = date("Y-m-d", $prevStamp);
//		aDebug(" -1 month of today stamp = ",$prevD);
//		
//		aDebug("today = ".$displaydate);
//		
//		$nextD = date("Y-m-d", $nextStamp);
//		aDebug(" +1 month of today stamp = ",$nextD);
//		
//		aDebug("week Day of the first month date = ".$weekday);
//		aDebug(array('$prevdate'=>$prevStamp,'$displaydate'=>$displaydate,'$nextdate'=>$nextStamp));

		// number of days of the prev month:
		$numdays_prev = date("t", $prevStamp); 
		
		$cols = 7;
		// current month num of days:
		$numdays = date("t", $dateStamp ); 
		//aDebug("num of days in Current month = ".$numdays);
		$numrows = ceil(($numdays + $weekday) / $cols);
	
		$counter = 1;
		$check_m = $month;
		$check_y = $year;
		 
		$daysleft = $weekday;  // number of the days before the current month to display
	
		//$prev_counter =  $numdays_prev-$weekday+$this->daysShift;  // previous month counter = the first date on the calendar
		$prev_counter =  $numdays_prev-$weekday+2;  // previous month counter = the first date on the calendar
		//aDebug('$prev_counter = '.$prev_counter);
		
		if($prev_counter>0)
		{
			$month = $pm;
			$year = $py;
			$counter = $prev_counter; // start with previous month date
		}
		else
		{
			$counter = 1;
		}
			
		//array for All calendar
		$array_month = array();
		
		$array_result = array();
		
		// start weeks - arrays
		for($i=1;$i<=$numrows;$i++)
		{
			
			// start new array for every week
			$array_week = array();
			
			// title coloumn required:
			// column title will be displayed in template
			if($title)
			{
				array_push($array_week,array('dateDisplay'=>$title,'info'=>'team Title'));
			}
			 
			// days
			for($f=0;$f<=($cols-1);$f++)
			{
				// new array for every day
				$array_day = array();
	
				// format like 12th-Dec:
				$display = date("jS-M", mktime(0, 0, 0, $month, $counter,$year));
				
				// highlighting the date:
				//if($this->date == mktime(0, 0, 0, $month, $counter,$year) ) {
				if($highlightDate == mktime(0, 0, 0, $month, $counter,$year) ) {
					$display = "<strong>" . $display."</strong>";
				}
	
				// keep  ['dateDisplay'] to use in the template:
				$array_day['dateDisplay'] = $display;
				// keep ['dateStamp'] to use in action.php for calculation and navigation:
				$array_day['dateStamp'] = mktime(0, 0, 0, $month, $counter,$year);
				
				array_push($array_week,$array_day);
				
				// get the next day to build the next date
				// keep counting even when the month ends
				// the next month date will be built
				$counter++;
				
			}
			// whole week is created and counter points to the first day
			// of next week 
			
		
			// Check if this week is one that should be displayed:
			
			// date('W', $stamp) returns week number for week Monday-Sunday
			// But in the $array_week the first date is set to Sunday
			// to get the correct week get the second element, which IS Monday:
			$w_dateStamp = $array_week[1+$this->daysShift][dateStamp];
			 
			// the number of the week:
			$w_current_week = date("W", $w_dateStamp);
			
			
			// only 1-view_week will be displayed
			// view_week is set in scheduleToTeam
			if($w_current_week == $this->view_week)
			{
				//the result week is found:
				$array_result = $array_week;
				break;
			}

		} // end rows
	
		$this->daysArray = $array_result;
	}
	
	
	
}


