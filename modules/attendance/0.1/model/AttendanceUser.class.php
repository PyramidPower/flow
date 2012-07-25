<?php
// attendance user object
class AttendanceUser extends DbObject {
	var $user_id;		// user id
	var $manager_id;	// manager id
	var $is_deleted; 	// deleted flag
	var $dt_deleted;	// date/time deleted
	
	// actual table name
	function getDbTableName() {
		return "attendance_user";
	}
}
