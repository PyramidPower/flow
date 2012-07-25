<?php
########################################################
#####												####
#####  		M O N T H   C A L E N D A R 			####
#####												####
########################################################

/**
 *@author Sergey Gagarin <serg@pyramidpower.com.au>
 *@copyright (c) 2010 Pyramid Power, Australia {@link http://www.pyramidpower.com.au PyramidPower}
 *@link http://www.pyramidpower.com.au Pyramidpower site
 *@versin 0.1
 *
 * Class MonthCal provides table-like calendar with navigation bar.
 * To be used with /css/calendar/calendar.css   
 *$uses:
 * <code> 	 set the navigation action 'prev/next month':
 * 	  			$action = $w->localUrl("/test-calendar/monthCalendar");
 *	  		 create calendar
 *	 			$cal = new MonthCal($date, $action,  array('aid'=>21, 'tid'=>A11));
 *	 		 get navigation form
 *				$navForm = $cal->getNavigationForm();
 *			 push out navigation 
 *				$w->ctx('calNav',$navForm);
 *			 get days 
 *				$array_month = $cal->getDaysArray();
 *			 populate day's info
 *			 push out $array_month 
 *</code>
 *
 *@see scheduleJobToAgency_ALL()
 *
 *@example  C:\Users\serg\workplaceGIT\FLowGit\modules\operations\0.1\operations.schedule.actions.php scheduleJobToAgency_ALL() 
 * **/
class MonthCal
{
	/**
	 * @access private
	 * @var timestamp 
	 */
	private $date;
	
	/**
	 * @access private
	 * @var string contains action URL for navigation form.
	 */
	private $action;
	
	/**
	 * @var string contains html representation of navigation form.
	 */
	var $navForm;
	
	/**
	 * @var array contains array of days arrays. 
	 */
	var $daysArray;

	
	/**
	 * @return string contains html representation of navigation form.
	 */
	public function getNavigationForm()
	{
		return $this->navForm;
	}
	
	
	
	/**
	 *$uses
	 *	<code>	
	 *		array(
	 *				weekArray(
	 *							week1Array(
	 *										 weekDay1Array('dateDisplay'=>28th-Nov,'dateStamp'=>1290862800))
	 *										 weekDay2Array('dateDisplay'=>29th-Nov,'dateStamp'=>1290949200))
	 *										 ...
	 *										)
	 *							week2Array(
	 *										 weekDay1Array('dateDisplay'=>NNth-Nov,'dateStamp'=>dddddddddd))
	 *										 ...
	 *										)
	 *							...
	 *				)
	 *	</code>
	 *
 	 * **/
	public function getDaysArray()
	{
		return $this->daysArray;
	}
	
	
	/**
	 * @param timestamp $dateStamp calendar will be created for this date's month
	 * @param string 	$action will be set as action for navigation form
	 * @param array  	$hiddenFields  array('hiddenFieldName1'=>hiddenFieldValue, 'hiddenFieldName2'=>hiddenFieldValue)
	 * 
	 * @return set $navForm, $daysArray
	 * 
	 * @see $navForm, $daysArray
	 * 
	 * 
	 *  		
 	 * **/
	public function __construct($dateStamp, $action, $hiddenFields=null)
	{
		$this->date = $dateStamp;
		$this->action = $action;

	//---------------------------------------------------------
	//--   Calendar Navigation
	//--  
	//---------------------------------------------------------
			
		if(!$dateStamp)
		{
			// today's time stamp
			$dateStamp = time();	
		}
			
		$displaydate = date('F', $dateStamp);
		
		// current month info
		$mm = date('m', $dateStamp);
		$yy = date('Y', $dateStamp);
		$weekday = date("N", mktime(0, 0, 0, $mm, 1, $yy));
		
		
		
		//prev month must be calculated for the first date of the current month, 
		// OR for current date = 2010-12-31 prevDate will be just 2010-12-01 !  
		//prev Month timeStamp:
		$prevStamp = strtotime("-1 month", mktime(0,0,0, $mm, 1, $yy));
		$pm = date('m',$prevStamp);
		$py = date('y',$prevStamp);
			
			
		//next month must be calculated for the first date of the current month
		// OR for current date = 2011-02-31 nextDate will be 2011-03-03  !
		// next month timeStamp:
		$nextStamp = strtotime("+1 month", mktime(0,0,0, $mm, 1, $yy));
	
		// Calendar navigation line is a form:
		$f = "<form id='calNav' action=$action method='POST'>";
		$f .= "<input type='hidden' name='prevDate' value='$prevStamp'>";
		$f .= "<input type='hidden' name='nextDate' value='$nextStamp'>";
			
		$f .= "<input type='submit' name='prevSubmit' value=' Prev' style='border:none; background: no-repeat url(".$webroot."/img/calendar/arrowleft.gif');'>";
		$f .= "<b>".$displaydate."</b>";
		$f .= " <input type='submit' name='nextSubmit' value=' Next ' style='border:none; background: 100% 0 no-repeat url(".$webroot."/img/calendar/arrowright.gif');'>";
		
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

	//------------------------------------------------------------
	//
	//    Month days Array
	//------------------------------------------------------------	
	// DEBUGGING 
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
		//aDebug("num of days in Prev month = ".$numdays_prev);
	 

		$cols = 7;
		// current month num of days:
		$numdays = date("t", $dateStamp ); 
		//aDebug("num of days in Current month = ".$numdays);
		//$numrows = ceil(($numdays + $weekday) / $cols);
		$numrows = ceil(($numdays + $weekday) / $cols);
	
		$check_m = $month;
		$check_y = $year;
		 
		$daysleft = $weekday;  // number of the days before the current month to display
		
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
		
		//aDebug(array('$month '=>$month ,'$year'=>$year,'$counter'=>$counter));
		
	
		//array for All calendar
		$array_month = array();
		
		// start weeks
		for($i=1;$i<=$numrows;$i++)
		{
			// start new array for every week
			$array_week = array();
			 
			// days
			for($f=0;$f<=($cols-1);$f++)
			{
	
				// new array for every day
				$array_day = array();
	
				// format like 12th-Dec:
				$display = date("jS-M", mktime(0, 0, 0, $month, $counter,$year));
				
				// highlighting today date:
				if(mktime(0,0,0) == mktime(0, 0, 0, $month, $counter,$year) ) {
					$display = "<strong>" . $display."</strong>";
				}
	
				// keep  ['dateDisplay'] to use in the template:
				$array_day['dateDisplay'] = $display;
				// keep ['dateStamp'] to use in action.php for calculation and navigation to the week view:
				$array_day['dateStamp'] = mktime(0, 0, 0, $month, $counter,$year);
				
				array_push($array_week,$array_day);
	
				// get the next day to build the next date
				// keep counting even when the month ends
				// the next month date will be built
				$counter++;
			}
			array_push($array_month, $array_week);
	
		} // end rows
	
		$this->daysArray = $array_month;
	}
	
}

