<?php
class AttendanceService extends DbService {
	// function to sort lists by date created, earliest to latest
	static function sortByStarted($a, $b) {
    	if ($a->dt_start == $b->dt_start) {
			return 0;
		}
		return ($a->dt_start > $b->dt_start) ? +1 : -1;
	}

	// convert dd/mm/yyyy date to yyy-mm-dd for SQL statements
	function & date2db($date) {
		if ($date) {
			list($d,$m,$y) = preg_split("/\/|-|\./", $date);
			return $y."-".$m."-".$d;
		}
	}
	
	// is this a date?
	function is_date($thedate) {
		if (!is_numeric($thedate))
			return false;

		$month = date("m",$thedate);
		$day   = date("d",$thedate);
		$year  = date("Y",$thedate);
 
		if (checkdate($month, $day, $year)) {
			return true;
		}
		return false;
	}

	// nicely format a number of seconds as H:m
	function & getFormatPeriod($seconds) {
		if (is_numeric($seconds)) {
			$hours = intval($seconds/3600);
			$mins = intval(($seconds/60) % 60);
			$mins = str_pad($mins,2,"0",STR_PAD_LEFT);
			$sec = intval($seconds % 60);
			$sec = str_pad($sec,2,"0",STR_PAD_LEFT);
			return $hours.":".$mins.":".$sec;
		}
	}
	
	// toggle start/stop attendance log
	function getNextAttendanceType($atype=null) {
		$arr = array("Started"=>"Stop","Stopped"=>"Start");
		
		if ($atype == "") {
			return $arr["Stopped"];
		}
		else {
			return $arr[$atype];
		}
	}
	
	// return 1st and last day of given week, year (Monday = 1st day)
	function getWeek($year,$week) {
	    $mon = date("Y-m-d", strtotime("{$year}-W{$week}-1"));
		$sun = date("Y-m-d", strtotime("{$year}-W{$week}-7"));
		
		return array("mon"=>$mon,"sun"=>$sun);
		
	} 
	
    // return a users full name given their user ID
	function & getUserById($id) {
		$u = $this->w->auth->getUser($id);
		return $u ? $u->getFullName() : "";
	}
	
	// return a user object
	function & getAttendanceUser($id) {
		return $this->getObject("AttendanceUser", array("user_id"=>$id));
	}

	// return users given their Mamager ID
	function & getAttendanceUsers($id) {
		return $this->getObjects("AttendanceUser", array("manager_id"=>$id,"is_deleted"=>"0"));
	}
	
	// return a user object
	function & getAttendanceLogRecord($id) {
		return $this->getObject("AttendanceLog", array("id"=>$id,"is_deleted"=>"0"));
	}
	
	// return a user log object. default to current week
	function & getAttendanceLog($id, $year, $week) {
		$where = "attendance_user_id = " . $id . " and is_deleted = 0";

		$arr = $this->getWeek($year,$week);
		if ($arr) {
			$where .= " and dt_start >= '" . $arr['mon'] . "' and dt_start <= '" . $arr['sun'] . "'";
		}
		
		return $this->getObjects("AttendanceLog", $where);
	}

	// return a user time sheet approval record
	function & getAttendanceApproval($id, $year, $week) {
		return $this->getObject("AttendanceApproval", array("attendance_user_id"=>$id,"ts_week"=>$week,"ts_year"=>$year));
	}
}
