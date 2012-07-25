<?php

// Overview;
// Define the various Task Groups available to the system
// Define the various Task Types within each group
// Set titles, descriptions, statuses and priorities for each group
// Set titles, descriptions and additional form fields for each task type
// Set flag to allow/disallow closed task to be reopened for each Task Group Type
// This allows <module>.tasks.php file to be created under each module,
// integrating Tasks with Flow modules and leveraging the existing functionality of modules
// Such files are loaded by task.model.php-TaskService->_loadTaskFiles()

////////////////////////////////////////////////////
////		TaskGroupType						////
////////////////////////////////////////////////////

class TaskGroupType_TaskTodo extends TaskGroupType {
	function getTaskGroupTypeDescription() {
		return "This is a TODO list. Use this for assigning any work.";
	}

	function getTaskGroupTypeTitle() {
		return "To Do";
	}

	function getTaskTypeArray() {
		return array("Todo" => "To Do");
	}
	
	function getStatusArray() {
		return array(array("New",false),
			array("Assigned",false),
			array("Wip",false),
			array("Pending",false),
			array("Done",true),
			array("Rejected",true));
	}

	function getTaskPriorityArray() {
		return array("Urgent","Normal","Nice to have");
	}
	
	function getCanTaskGroupReopen() {
		return true;
	}
}

class TaskGroupType_Helpdesk extends TaskGroupType {
	function getTaskGroupTypeDescription() {
		return "This is a Help Desk system.";
	}

	function getTaskGroupTypeTitle() {
		return "Help Desk";
	}

	function getTaskTypeArray() {
		return array(
			"Todo"=>"To Do",
			"SoftwareTicket"=>"Software Ticket",
			"HardwareTicket"=>"Hardware Ticket",
			"FlowTicket"=>"Flow Ticket");
	}

	function getStatusArray() {
		return array(array("New",false),
			array("Assigned",false),
			array("Wip",false),
			array("Pending",false),
			array("Done",true),
			array("Rejected",true));
	}

	function getTaskPriorityArray() {
		return array("Critical","Urgent","Normal","Low");
	}

	function getCanTaskGroupReopen() {
		return true;
	}
}

////////////////////////////////////////////////
////		TaskType						////
////////////////////////////////////////////////

class TaskType_Todo extends TaskType {
	function getTaskTypeTitle() {
		return "Todo Item";
	}
	
	function getTaskTypeDescription() {
		return "Use this to assign any task.";
	}

}


class TaskType_SoftwareTicket extends TaskType {
	function getTaskTypeTitle() {
		return "Software Ticket";
	}
	
	function getTaskTypeDescription() {
		return "Use this to report any issue or feature request for installed software.";
	}
	
	function getFieldFormArray() {
		return array(
			array($this->getTaskTypeTitle(),"section"),
			array("Software Package","select","software",null,lookupForSelect($this->w, "SoftwarePackages")),
			array("Bug or Feature","select","b_or_f",null,lookupForSelect($this->w, "BugOrFeature")),
			array("Identifier","hidden","ident",null),
			);
	}
	function on_after_insert($task) {
		$ident = "SW".sprintf("%05d",$task->id);
		$task->setDataValue("ident",$ident);
		$task->title = $ident." ".$task->title;	
		$task->update();	
	}	
}

class TaskType_HardwareTicket extends TaskType {
	function getTaskTypeTitle() {
		return "Hardware Ticket";
	}
	
	function getTaskTypeDescription() {
		return "Use this to report any issue or request for hardware.";
	}
	
	function getFieldFormArray() {
		return array(
			array($this->getTaskTypeTitle(),"section"),
			array("Hardware","select","hardware",null,lookupForSelect($this->w, "Hardware")),
			array("Identifier","hidden","ident",null),
		);
	}
	
	function on_after_insert($task) {
		$ident = "HW".sprintf("%05d",$task->id);
		$task->setDataValue("ident",$ident);
		$task->title = $ident." ".$task->title;	
		$task->update();	
	}	
}

class TaskGroupType_SoftwareDevelopment extends TaskGroupType {
	function getTaskGroupTypeDescription() {
		return "Use this for tracking software development tasks.";
	}

	function getTaskGroupTypeTitle() {
		return "Software Development";
	}

	function getTaskTypeArray() {
		return array(
			"Todo"=>"To Do",
			"FlowTicket"=>"Flow Ticket");
	}

	function getStatusArray() {
		return array(array("Idea",false),
			array("On Hold",false),
			array("Backlog",false),
			array("Todo",false),
			array("WIP",false),
			array("Testing",false),
			array("Deploy",false),
			array("Live",false),
			array("Rejected",true),
			array("Reviewed",true));
	}

	function getTaskPriorityArray() {
		return array("Critical","Urgent","Normal","Low");
	}
}

class TaskType_FlowTicket extends TaskType {
	
	static $modules = array(
		"Address"=>"ADR",
		"Admin"=>"ADM",
		"Agenda"=>"AGD",
		"Asset"=>"AST",
		"Auth"=>"AUT",
		"Calendar"=>"CAL",
		"Contact"=>"CON",
		"Documents"=>"DOC",
		"File"=>"FIL",
		"Framework"=>"FRM",
		"Help"=>"HLP",
		"Inbox"=>"INB",
		"Integration"=>"INT",
		"Mobile"=>"MOB",
		"News"=>"NWS",
		"Operations"=>"OPS",
		"Pages"=>"PAG",
		"Report"=>"REP",
		"Sales"=>"SAL",
		"Search"=>"SRC",
		"Task"=>"TSK",
		"Vehicle"=>"VEH",
		"Warehouses"=>"WRH",
		"Wiki"=>"WIK",
	);
	
	function getTaskTypeTitle() {
		return "Flow Ticket";
	}
	
	function getTaskTypeDescription() {
		return "Use this to report any issue or feature request for Flow.";
	}
	
	function getFieldFormArray() {
		return array(
			array($this->getTaskTypeTitle(),"section"),
			array("Flow Module","select","flow_module",null,array_keys(self::$modules)),
			array("Bug or Feature","select","b_or_f",null,array("Bug","Feature","Task")),
			array("Identifier","hidden","ident",null),
			);
	}
	
	function on_before_insert($task) {
		// Get REQUEST object instead
		if ($_REQUEST["b_or_f"]=='Bug' || $_REQUEST["b_or_f"]=='Task') {
			$task->status = "Todo";
		}
	}
	
	function on_after_insert($task) {
		$ident = self::$modules[$_REQUEST["flow_module"]].sprintf("%04d",$task->id);
		$task->setDataValue("ident",$ident);
		$task->title = $ident." ".$task->title;	
		$task->update();	
	}
	
}
































