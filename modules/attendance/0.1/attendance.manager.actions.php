<?php

//////////////////////////////////////////////////
//			ATTENDANCE DASHBOARD				//
//////////////////////////////////////////////////

function index_ALL(Web &$w) {
	attendance_navigation($w, "Attendance");
	
	// tab: managers team
	// get user details
	$attuser = $w->Attendance->getAttendanceUsers($_SESSION['user_id']);

	$line = array(array("Employee",""));
	
	if ($attuser) {
		foreach ($attuser as $user) {
			$line[] = array(
						$w->Attendance->getUserById($user->user_id),
						Html::b($w->localUrl("/attendance-manager/viewtimesheet/".$user->user_id)," View Time Sheet ") .
						"&nbsp;&nbsp;&nbsp;" .
						Html::b($w->localUrl("/attendance-manager/deleteemployee/".$user->user_id)," Remove Employee ", "Are you sure you want to REMOVE this EMPLOYEE?")
					);	
		}
	}
	else {
		$line[] = array("No Employees available","","");
	}
	
	// display the user details
	$w->ctx("users",Html::table($line,null,"tablesorter",true));
}

function viewtimesheet_ALL(Web &$w) {
	$p = $w->pathMatch("id");

	$user = $w->Attendance->getUserById($p['id']);
	
	attendance_navigation($w, $user);
	
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

	$approval = $w->Attendance->getAttendanceApproval($p['id'], $year, $week);
	$appflag = ($approval) ? $approval->approval : "1";
	$alabel = ($appflag == "0") ? "<p>Time Sheet Approved: " . $w->Attendance->getUserById($approval->approver_id) . ". " . formatDate($approval->dt_approved) : "";
	
	$attlog = $w->Attendance->getAttendanceLog($p['id'], $year, $week);
	
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
			}
			// if accepted, label button, tally period, include edit button
			if ($log->is_approved == "0") {
				$label = " Review ";
				$tottime += $seconds;
			}
			
			if ($appflag != "0") {
				$buttons = Html::box($w->localUrl("/attendance-manager/edittime/".$log->id."/".$p['id']."/".$week)," Edit ",true) . 
						"&nbsp;&nbsp;&nbsp;" .
						Html::b($w->localUrl("/attendance-manager/deletetime/".$log->id."/".$p['id']."/".$week)," Delete ","Are you sure you wish to delete this ATTENDANCE TIME LOG ENTRY?") .
						"&nbsp;&nbsp;&nbsp;" .
						Html::b($w->localUrl("/attendance-manager/suspecttime/".$log->id."/".$p['id']."/".$week),$label);

				$appbutton = Html::b($w->localUrl("/attendance-manager/acceptsheet/".$p['id']."/".$week."/".$year)," Approve Time Sheet");
			}
			else {
				$buttons = "";
				$appbutton = Html::b($w->localUrl("/attendance-manager/acceptsheet/".$p['id']."/".$week."/".$year)," Review Time Sheet");
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
		$line[] = array("","","<b>Week Total</b>","<b>".$w->Attendance->getFormatPeriod($tot)."</b>",$appbutton);
	}		
	else {
		$line[] = array("No Time Sheet available","","","","");
	}

	$weeknav = "<a href=\"/attendance-manager/viewtimesheet/".$p['id']."/?y=" . $year . "&w=" . ($week-1) . "\">Previous Week</a>&nbsp;&nbsp;&nbsp;" . 
			   "<a href=\"/attendance-manager/viewtimesheet/".$p['id']."/?y=" . $year . "&w=" . date("W") . "\">Current Week</a>&nbsp;&nbsp;&nbsp;" . 
     		   "<a href=\"/attendance-manager/viewtimesheet/".$p['id']."/?y=" . $year . "&w=" . ($week+1) . "\">Next Week</a>";

	$w->ctx("weeknav",$weeknav);
	$w->ctx("strweek",$strweek . $alabel);
		
	// display the user details
	$w->ctx("timesheet",Html::table($line,null,"tablesorter",true));
}

function edittime_GET(Web &$w) {
	$p = $w->pathMatch("logid","usr","week");

	$log = $w->Attendance->getAttendanceLogRecord($p['logid']);

	$f = array(
			array("Edit Attendance Record","section"),
			array("","hidden","thedate",date("Y-m-d",$log->dt_start)),
			array("Start","time","dt_start",date("H:i a", $log->dt_start)),
			array("End","time","dt_stop",date("H:i a",$log->dt_stop)),
			);
	
    // diplay form
    $editime = Html::form($f,$w->localUrl("/attendance-manager/editime/".$p["logid"]."/".$p['usr']."/".$p['week']),"POST"," Save "); 
    
    $w->setLayout(null);
    $w->out($editime);
}

function editime_POST(Web &$w) {
	$p = $w->pathMatch("logid","usr","week");
		
	$log = $w->Attendance->getAttendanceLogRecord($p['logid']);
	
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
	
	$w->msg("Attendance Log Started","/attendance-manager/viewtimesheet/".$p['usr']."/?w=".$p['week']);
}

function deletetime_ALL(Web &$w) {
	$p = $w->pathMatch("logid","usr","week");
		
	// ge4t log entry
	$log = $w->Attendance->getAttendanceLogRecord($p['logid']);
	
	// if log exists, mark as deleted
	if ($log) {
		$log->is_deleted = "1";
		$log->update();
	}

	// return
	$w->msg("Attendance Log Entry deleted","/attendance-manager/viewtimesheet/".$p['usr']."/?w=".$p['week']);
}

function suspecttime_ALL(Web &$w) {
	$p = $w->pathMatch("logid","usr","week");
				
	// get the relevant log entry
	$log = $w->Attendance->getAttendanceLogRecord($p['logid']);
		
	// toggle database field based on current setting
	if ($log->is_approved == "0") {
		$log->is_approved = "1";
		$log->approver_id = $_SESSION['user_id'];
		$log->dt_approved = date("Y-m-d");
		$logmsg = "Attendance Time Log entry not accepted";
	}
	else {
		$log->is_approved = "0";
		$log->approver_id = $_SESSION['user_id'];
		$log->dt_approved = date("Y-m-d");
		$logmsg = "Attendance Time Log entry accepted";
		}
	$log->update();
	
	// return
    $w->msg($logmsg,"/attendance-manager/viewtimesheet/".$p['usr']."/?w=".$p['week']);
}

function acceptsheet_ALL(Web &$w) {
	$p = $w->pathMatch("usr","week","year");
	
	$attlog = $w->Attendance->getAttendanceLog($p['usr'], $p['year'], $p['week']);
	
	if ($attlog) {
		foreach ($attlog as $log) {
			// get the relevant log entry
			$thislog = $w->Attendance->getAttendanceLogRecord($log->id);

			if (($thislog) && ($thislog->is_approved == "1")) {
				$myflag = true;
				break;
			}
		}
	}
	
	if (!$myflag) {
		$approval = $w->Attendance->getAttendanceApproval($p['usr'], $p['year'], $p['week']);
	
		if ($approval) {
			$appflag = ($approval->approval == "0") ? "1" : "0";
		
			$approval->approver_id = $_SESSION['user_id'];
			$approval->approval = $appflag;
			$approval->dt_approved = date("Y-m-d");
			$approval->update();
		}
		else {
			$appflag = "0";
			
			$approval = new AttendanceApproval($w);
			$approval->attendance_user_id = $p['usr'];
			$approval->ts_week = $p['week'];
			$approval->ts_year = $p['year'];
			$approval->approver_id = $_SESSION['user_id'];
			$approval->approval = $appflag;
			$approval->dt_approved = date("Y-m-d");
			$approval->insert();
		}
	
		if ($attlog) {
			if ($appflag == "0") {
				foreach ($attlog as $log) {
					// get the relevant log entry
					$thislog = $w->Attendance->getAttendanceLogRecord($log->id);

					if ($thislog) {
						$thislog->is_approved = $appflag;
						$thislog->approver_id = $_SESSION['user_id'];
						$thislog->dt_approved = date("Y-m-d");
						$thislog->update();
					}
				}
			}
			$logmsg = "Time sheet Accepted";
		}
	}
	else {
		$logmsg = "Time Sheet is still under Review";
	}
	
	// return
    $w->msg($logmsg,"/attendance-manager/viewtimesheet/".$p['usr']."/?w=".$p['week']."&y=".$p['year']);
}

function addemployees_GET(Web &$w) {
	$p = $w->pathMatch("task_group_id");

	// get all users
	$users = $w->auth->getUsers();
	
	// not interested in users who are really groups
	foreach ($users as $user) {
		if ($user->is_group == "0")
			$usr[] = $user;
	}
	
	// build 'add members' form given task group ID, the list of group roles and the list of users.
	// if current members are added as if new, their membership will be updated, not recreated, with the selected role
	$addUserForm['Add Employees'] = array(
	array(array("Employees","multiSelect","member",null,$usr)));

	$w->setLayout(null);
	$w->ctx("addemployees",Html::multiColForm($addUserForm,$w->localUrl("/attendance-manager/updateemployees/"),"POST"," Submit "));
}

function updateemployees_POST(Web &$w) {
	$arrdb = array();
	$arrdb['is_deleted'] = "0";
	$arrdb['manager_id'] = $_SESSION['user_id'];
	
	// for each selected member, complete population of input array
	foreach ($_REQUEST['member'] as $member) {
		$arrdb['user_id'] = $member;
		
		// check to see if member already exists in this group
		$mem = $w->Attendance->getAttendanceUser($arrdb['user_id']);
		
		// if no membership, create it
		if (!$mem) {
			$mem = new AttendanceUser($w);
			$mem->fill($arrdb);
			$mem->insert();
			}
		else {
			// if membership does exists, update the record
			$mem->fill($arrdb);
			$mem->update();
		}
		// prepare input array for next selected member to insert/update
		unset($arrdb['user_id']);
	}
	// return
	$w->msg("Employees Added","/attendance-manager/index/");
}

function deleteemployee_ALL(Web &$w) {
	$p = $w->pathMatch("user_id");
	
	// check to see if member already exists
	$mem = $w->Attendance->getAttendanceUser($p['user_id']);
	
	if ($mem) {
		$mem->is_deleted = "1";
		$mem->dt_deleted = date("Y-m-d");
		$mem->update();
		
		$msg = "Employee removed";
	}
	else {
		$msg = "Employee not found";
	}

	$w->msg($msg,"/attendance-manager/index/");
}
?>
