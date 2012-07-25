<?php
// weekly time sheet approval register
class AttendanceApproval extends DbObject {
	var $attendance_user_id;	// user id
	var $ts_week;				// time sheet week number
	var $ts_year;				// time sheet year
	var $approver_id;			// approver ID
	var $approval;				// approval flag
	var $dt_approved;			// date approved
	
	// actual table name
	function getDbTableName() {
		return "attendance_approval";
	}
}
