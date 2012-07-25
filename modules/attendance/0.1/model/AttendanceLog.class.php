<?php
// attendance log object
class AttendanceLog extends DbObject {
	var $attendance_user_id;	// user id
	var $dt_start;				// log entry start date/time
	var $dt_stop;				// log entry stop date/time
	var $is_approved;			// log entry approved?
	var $is_deleted;			// log entry deleted?
	var $approver_id;			// approver id
	var $dt_approved;			// date approved
	var $comment_id;			// comment id
	
	// actual table name
	function getDbTableName() {
		return "attendance_log";
	}
}
