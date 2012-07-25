<?php

//////////////////////////////////////////////////
//			ATTENDANCE DASHBOARD				//
//////////////////////////////////////////////////

function index_ALL(Web &$w) {
	attendance_navigation($w, "Attendance");
	
	// tab: user dashboard
	// get user details
	$attuser = $w->Attendance->getAttendanceUser($_SESSION['user_id']);
	
	$catttype = (isset($_SESSION["FlowAttendanceType"])) ? $_SESSION["FlowAttendanceType"] : "";
	$natttype = $w->Attendance->getNextAttendanceType($catttype);
	$label = ucfirst($natttype) . " Log";
	
	$line = array(array("Employee","Manager",""));
	
	if ($attuser) {
		$line[] = array(
					$w->Attendance->getUserById($attuser->user_id),
					$w->Attendance->getUserById($attuser->manager_id),
					Html::b($w->localUrl("/attendance/createlogentry/".$attuser->user_id),$label)
				);	
	}
	else {
		$line[] = array("No Attendance User record available","","");
	}
	
	// display the user details
	$w->ctx("user",Html::table($line,null,"tablesorter",true));
	
	// tab: time sheet - default, current week
	$year = ($_GET["y"] != "") ? $_GET["y"] : date("Y");
	$week = ($_GET["w"] != "") ? $_GET["w"] : date("W");
	
	if ($week == "0") {
		$year = $year - 1;
		$week = "52";
	}
	if ($week == "53") {
		$year = $year + 1;
		$week = "1";
	}
	
	$week = str_pad($week,2,"0",STR_PAD_LEFT);
	
	$darr = $w->Attendance->getWeek($year,$week);
	if ($darr) {
		$strweek = "Time Sheet: " . date("j/m/Y", strtotime($darr["mon"])) . " to " . date("j/m/Y", strtotime($darr["sun"]));
	}

	$approval = $w->Attendance->getAttendanceApproval($_SESSION['user_id'], $year, $week);
	$appflag = ($approval) ? $approval->approval : "1";
	$alabel = ($appflag == "0") ? "<p>Time Sheet Approved: " . $w->Attendance->getUserById($approval->approver_id) . ". " . formatDate($approval->dt_approved) : "";
	
	$attlog = $w->Attendance->getAttendanceLog($_SESSION['user_id'], $year, $week);
	
	if ($attlog) {
		usort($attlog, array("AttendanceService","sortByStarted"));
	}

	$line = array(array("Date","Start","Stop","Period",""));

	if ($attlog) {
		$tot = 0;
		$tottime = 0;
		$olddate = "";
		
		foreach ($attlog as $log) {
			$date = date("d/m/Y",$log->dt_start);
			
			if (($olddate != "") && ($date != $olddate)) {
				$line[] = array("","","<b>Total</b>","<b>".$w->Attendance->getFormatPeriod($tottime)."</b>","");
				$line[] = array("&nbsp;","&nbsp;","&nbsp;","&nbsp;","&nbsp;");
				$tot += $tottime;
				$tottime = 0;
				$ndate = "<b>".$date."</b>";
			}
			elseif ($olddate == "") {
				$ndate = "<b>".$date."</b>";
			}
			else {
				$ndate = "";
			}
			
			if (($w->Attendance->is_date($log->dt_start)) && ($w->Attendance->is_date($log->dt_stop))) {
				$seconds = $log->dt_stop - $log->dt_start;
				$period = $w->Attendance->getFormatPeriod($seconds);
				$dt_start = formatDateTime($log->dt_start);
				$dt_stop = formatDateTime($log->dt_stop);
			}
			else {
				$dt_start = $w->Attendance->is_date($log->dt_start) ? formatDateTime($log->dt_start) : "No Start entry";
				$dt_stop = $w->Attendance->is_date($log->dt_stop) ? formatDateTime($log->dt_stop) : "No Stop entry";
				$seconds = 0;
				$period = "0:00:00";
			}
			
			// if no accepted, label button, style period, remove edit button
			if ($log->is_approved == "1") {
				$label = " Accept ";
				$period = "(" . $period . ")";
				$btnedit = "&nbsp;";
			}
			// if accepted, label button, tally period, include edit button
			if ($log->is_approved == "0") {
				$label = " Review ";
				$tottime += $seconds;
				$btnedit = Html::box($w->localUrl("/attendance/edittime/".$log->id."/".$week)," Edit ",true);
			}
			
			if ($appflag != "0") {
				$buttons = $btnedit .
  							"&nbsp;&nbsp;&nbsp;" .
							Html::b($w->localUrl("/attendance/deletetime/".$log->id."/".$week)," Delete ","Are you sure you wish to delete this ATTENDANCE TIME LOG ENTRY?");
			}
			else {
				$buttons = "";
			}
			
			$line[] = array(
						$ndate,
						$dt_start,
						$dt_stop,
						$period,
						$buttons
 						);
				
			$olddate = $date;
		}
		$tot += $tottime;
		$line[] = array("","","<b>Total</b>","<b>".$w->Attendance->getFormatPeriod($tottime)."</b>","");
		$line[] = array("&nbsp;","&nbsp;","&nbsp;","&nbsp;","&nbsp;");
		$line[] = array("","","<b>Week Total</b>","<b>".$w->Attendance->getFormatPeriod($tot)."</b>","");
	}		
	else {
		$line[] = array("No Time Sheet available","","","","");
	}

	$weeknav = "<a href=\"/attendance/index/?tab=2&y=" . $year . "&w=" . ($week-1) . "\">Previous Week</a>&nbsp;&nbsp;&nbsp;" . 
			"<a href=\"/attendance/index/?tab=2&y=" . $year . "&w=" . date("W") . "\">Current Week</a>&nbsp;&nbsp;&nbsp;" . 
			"<a href=\"/attendance/index/?tab=2&y=" . $year . "&w=" . ($week+1) . "\">Next Week</a>";

	$w->ctx("weeknav",$weeknav);
	$w->ctx("strweek",$strweek . ". " . $alabel);
		
	// display the user details
	$w->ctx("timesheet",Html::table($line,null,"tablesorter",true));

}

function createlogentry_ALL(Web &$w) {
	$p = $w->pathMatch("id");

	$logid = (isset($_SESSION["FlowAttendance"])) ? $_SESSION["FlowAttendance"] : "";

	if ($logid) {
		$type = "Stopped";
		$log = $w->Attendance->getAttendanceLogRecord(intval($logid));
	
		$log->dt_stop = date("Y-m-d H:i:s");
		$log->update();
		
		unset($_SESSION["FlowAttendance"]);
		unset($_SESSION["FlowAttendanceType"]);
	}
	else {
		$type = "Started";
		
		$log = new AttendanceLog($w);
		$log->attendance_user_id = $p['id'];
		$log->dt_start = date("Y-m-d H:i:s");
		$log->insert();

		$_SESSION["FlowAttendance"] = $log->id;
		$_SESSION["FlowAttendanceType"] = $type;
	}
	
	$w->msg("Attendance Log ".$type,"/attendance/index");
}

function edittime_GET(Web &$w) {
	$p = $w->pathMatch("id","week");

	$log = $w->Attendance->getAttendanceLogRecord($p['id']);
	
	$start = ($log->dt_start) ? date("H:i a", $log->dt_start) : "";
	$stop = ($log->dt_stop) ? date("H:i a", $log->dt_stop) : "";
	
	$f = array(
			array("Edit Attendance Record","section"),
			array("","hidden","thedate",date("Y-m-d",$log->dt_start)),
			array("Start","time","dt_start",$start),
			array("End","time","dt_stop",$stop),
			);
	
    // diplay form
    $editime = Html::form($f,$w->localUrl("/attendance/editime/".$p["id"]."/".$p['week']),"POST"," Save "); 
    
    $w->setLayout(null);
    $w->out($editime);
}

function editime_POST(Web &$w) {
	$p = $w->pathMatch("id","week");
	
	$log = $w->Attendance->getAttendanceLogRecord($p['id']);
	
	$_POST['dt_start'] = $_POST['thedate'] . " " . $_POST['dt_start'];
	$_POST['dt_stop'] = $_POST['thedate'] . " " . $_POST['dt_stop'];

	if ($log) {
		$log->fill($_POST);
		$log->update();
	}
	else {
		$log = new AttendanceLog($w);
		$log->attendance_user_id = $_SESSION['user_id'];
		$log->dt_start = $_POST['start'];
		$log->dt_stop = $_POST['stop'];
		$log->insert();
	}
	
	$w->msg("Attendance Log Started","/attendance/index/?tab=2&w=".$p['week']);
}

function deletetime_ALL(Web &$w) {
	$p = $w->pathMatch("id","week");
	
	// ge4t log entry
	$log = $w->Attendance->getAttendanceLogRecord($p['id']);
	
	// if log exists, mark as deleted
	if ($log) {
		$log->is_deleted = "1";
		$log->update();
	}

	// return
	$w->msg("Attendance Log Entry deleted","/attendance/index/?tab=2&w=".$p['week']);
}
?>