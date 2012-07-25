<?php


//////////////////////////////////////////
//			TASK LIST   			    //
//////////////////////////////////////////

function tasklist_ALL(Web $w) {
	task_navigation($w, "");

	// tab: tasks
	// prepare default filter dropdowns
	// get WHO to return relevant tasks:
	//		a selected assignee, a blank assignee = all assignee's, no assignee = tasks assigned to me
	$who = (!array_key_exists("assignee",$_REQUEST)) ? $_SESSION['user_id'] : $_REQUEST['assignee'];

	// for those groups of which i am a member, get list of all members for display in Assignee & Creator dropdowns
	$mygroups = $w->Task->getMemberGroups($_SESSION['user_id']);
	
	if ($mygroups){
		foreach ($mygroups as $mygroup) {
			$mymembers = $w->Task->getMembersInGroup($mygroup->task_group_id);
			foreach ($mymembers as $mymem) {
				$members[$mymem[1]] = array($mymem[0],$mymem[1]);
			}
		}
		sort($members);
	}
	// load the search filters
	$a = Html::select("assignee",$members, $who);
	$w->ctx("assignee",$a);

	$b = Html::select("creator",$members, $_REQUEST['creator']);
	$w->ctx("creator",$b);
	
	$taskgroups = array();
	$c = Html::select("taskgroups",$taskgroups);
	$w->ctx("taskgroups",$c);
	
	$tasktypes = array();
	$d = Html::select("tasktypes",$tasktypes);
	$w->ctx("tasktypes",$d);
	
	$tpriority = array();
	$e = Html::select("tpriority",$tpriority);
	$w->ctx("tpriority",$e);
	
	$status = array();
	$f = Html::select("status",$status);
	$w->ctx("status",$f);

	$g = Html::checkbox("closed", $_REQUEST['closed']);
	$w->ctx("closed",$g);

	// change filter dropdowns to show selectedIndex for current search
	$w->ctx("reqTaskgroups",$_REQUEST['taskgroups']);
	$w->ctx("reqTasktypes",$_REQUEST['tasktypes']);
	$w->ctx("reqPriority",$_REQUEST['tpriority']);
	$w->ctx("reqStatus",$_REQUEST['status']);
	$w->ctx("reqdtFrom",$_REQUEST['dt_from']);
	$w->ctx("reqdtTo",$_REQUEST['dt_to']);

	// prepare WHERE clause as string
	$where = "";
	if ($_REQUEST['taskgroups'] != "")
		$where .= "t.task_group_id = '" . $_REQUEST['taskgroups'] . "' and ";
	if ($_REQUEST['tasktypes'] != "")
		$where .= "t.task_type = '" . $_REQUEST['tasktypes'] . "' and ";
	if ($_REQUEST['tpriority'] != "")
		$where .= "t.priority = '" . $_REQUEST['tpriority'] . "' and ";
	if (($_REQUEST['status'] != ""))
		$where .= "t.status = '" . $_REQUEST['status'] . "' and ";
	if (($_REQUEST['status'] == "") && ($_REQUEST['closed']))
		$where .= "(t.is_closed = 0 or t.is_closed = 1) and ";
	if ((array_key_exists("status",$_REQUEST)) && ($_REQUEST['status'] == "") && (!$_REQUEST['closed']))
		$where .= "t.is_closed = 0 and ";
	if ($_REQUEST['dt_from'] != "")
		$where .= "t.dt_due >= '" . $w->Task->date2db($_REQUEST['dt_from']) . "' and ";
	if ($_REQUEST['dt_to'] != "")
		$where .= "t.dt_due <= '" . $w->Task->date2db($_REQUEST['dt_to']) . "' and ";
	
	$where = rtrim($where, " and ");

	// create task list heading
	$hds = array(array("Title","Assigned To", "Group", "Type", "Priority", "Created By", "Status", "Due", "Time Log"));

	// either use sql join to object_modified, if searching for tasks 'created by' or getObjects for all other searches
	if ($_REQUEST['creator'] != "") {
		$tasks = $w->Task->getCreatorTasks($_REQUEST['creator'],$where);
	}
	else {
		$tasks = $w->Task->getTasks($who,$where);
	}

	// show all tasks found
	if ($tasks) {
		usort($tasks, array("TaskService", "sortTasksbyDue"));
		foreach ($tasks as $task) {
           // if i can edit the task, allow me to edit the status from the Task List
	       if ($task->getCanIEdit()) {
				if ($task->getisTaskClosed() && !$task->getTaskReopen()) {
              		$taskstatus = $task->status;
				}
				else {
	              $taskstatus = Html::select("status_".$task->id,$task->getTaskGroupStatus(), $task->status);
				}
           }
           else {
              $taskstatus = $task->status;
           }
			
			$line[] = array(
			Html::a($webroot."/task/viewtask/".$task->id,$task->title),
			$w->Task->getUserById($task->assignee_id),
			$task->getTaskGroupTypeTitle(),
			$task->getTypeTitle(),
			$task->priority,
			$task->getTaskCreatorName(),
            $taskstatus,
			$task->isTaskLate(),
			Html::a($webroot."/task/starttimelog/".$task->id,"Start Log","Start Log","startTime"),
			);
		}
	}

	// if no tasks found, say as much
	if (!$line)
		$line = array(array("No Tasks found.","","","","","","","",""));

	$line = array_merge($hds, $line);

	// if logged in user is owner of current group, display button to edit the task group
	$btnedit = Html::b("/task-group/viewmembergroup/".$_REQUEST['taskgroups']," Edit Task Group ");
	$grpedit = ($_REQUEST['taskgroups'] != "") && ($w->Task->getIsOwner($_REQUEST['taskgroups'], $_SESSION['user_id'])) ? $btnedit : "";
	$w->ctx("grpedit",$grpedit);

	// display task list
	$w->ctx("mytasks",Html::table($line,null,"tablesorter",true));
	
	// tab: notifications
	// list groups and notification based on my role and permissions
	$line = array(array("Task Group","Your Role","Creator","Assignee","All Others",""));
	
	if ($mygroups){
		usort($mygroups, array("TaskService", "sortbyRole"));
		
		foreach ($mygroups as $mygroup) {
			$taskgroup = $w->Task->getTaskGroup($mygroup->task_group_id);
			$caniview = $taskgroup->getCanIView();
			
			$notify = $w->Task->getTaskGroupUserNotify($_SESSION['user_id'],$mygroup->task_group_id);
			if ($notify) {
				foreach ($notify as $n) {
					$value = ($n->value == "0") ? "No" : "Yes";
					$v[$n->role][$n->type] = $value;
				}
			}
			else {
				$notify = $w->Task->getTaskGroupNotify($mygroup->task_group_id);
				if ($notify) {
					foreach ($notify as $n) {
						$value = ($n->value == "0") ? "No" : "Yes";
						$v[$n->role][$n->type] = $value;
					}
				}
			}

			if ($caniview) {
				$title = $w->Task->getTaskGroupTitleById($mygroup->task_group_id);
				$role = strtolower($mygroup->role);
				
				$line[] = array(
							$title,
							ucfirst($role),
							$v[$role]["creator"],
							$v[$role]["assignee"],
							$v[$role]["other"],
							Html::box($webroot."/task/updateusergroupnotify/".$mygroup->task_group_id," Edit ",true)
							);
			}
		unset($v);
		}
		
	// display list
	$w->ctx("notify",Html::table($line,null,"tablesorter",true));
	}
}

// Create Task: selecting Task Type dynamically loads the related task types, proprity and assignee's
function taskAjaxSelectbyTaskGroup_ALL(Web $w) {
	$tid = $_REQUEST['id'];
	$t = $w->Task->getTaskGroup($tid);
	
	$tasktypes = ($t != "") ? $w->Task->getTaskTypes($t->task_group_type) : array();
	$priority = ($t != "") ? $w->Task->getTaskPriority($t->task_group_type) : array();
	$members = ($t != "") ? $w->Task->getMembersBeAssigned($t->id) : array();
	sort($members);
	$typetitle = ($t != "") ? $t->getTypeTitle() : "";
	$typedesc = ($t != "") ? $t->getTypeDescription() : "";
	
	// if user cannot assign tasks in this group, leave 'first_assignee' blank for owner/member to delegate 
	$members = ($t->getCanIAssign()) ? $members : array(array("Default",""));
	
	// create dropdowns loaded with respective data
	$ttype = Html::select("task_type",$tasktypes,null);
	$prior = Html::select("priority",$priority,null);
	$mem = Html::select("first_assignee_id",$members,null);
	$tasktext = "<table border=0 class=form>" . 
				"<tr><td class=section colspan=2>Task Group Description</td></tr>" . 
				"<tr><td><b>Task Group</td><td>" . $t->title . "</td></tr>" . 
				"<tr><td><b>Task Type</b></td><td>" . $typetitle . "</td></tr>" . 
				"<tr valign=top><td><b>Description</b></td><td>" . $typedesc . "</td></tr>" . 
				"</table><p>";

	// return as array of arrays
	$result = array($ttype, $prior , $mem, $tasktext);
	
	$w->setLayout(null);
	$w->out(json_encode($result));
}

// Search Filter: selecting an Assignee will dynamically load the Group dropdown with available values
function taskAjaxAssigntoGroup_ALL(Web $w) {
	$group = array();
	$assignee = $_REQUEST['id'];
	
	// organise criteria
	$who = ($assignee != "") ? $assignee : null;
	$where = "is_closed = 0";

	// get task group titles from available task list
	$tasks = $w->Task->getTasks($who, $where);
	if ($tasks) {
		foreach ($tasks as $task) {
			if (!array_key_exists($task->task_group_id, $group))
				$group[$task->task_group_id] = array($task->getTaskGroupTypeTitle(),$task->task_group_id);
		}
	}
	if (!$group)
		$group = array(array("No assigned Tasks",""));

	// load Group dropdown and return
	$taskgroups = Html::select("taskgroups",$group,null);
	
	$w->setLayout(null);
	$w->out(json_encode($taskgroups));
	}

// Search filter: selecting a Group will dynamically load the Type dropdown with available values
function taskAjaxGrouptoType_ALL(Web &$w) {
	$types = array();

	// split query string into group and assignee
	list($group, $assignee) = preg_split('/_/',$_REQUEST['id']);

	// organise criteria
	$who = ($assignee != "") ? $assignee : null;
	$where = "";
	if ($group != "")
		$where .= "task_group_id = " . $group . " and ";

	$where .= "is_closed = 0";

	// get task types from available task list
	$tasks = $w->Task->getTasks($who, $where);
	if ($tasks) {
		foreach ($tasks as $task) {
			if (!array_key_exists($task->task_type, $types))
				$types[$task->task_type] = array($task->getTypeTitle(),$task->task_type);
		}
	}
	if (!$types)
		$types = array(array("No assigned Tasks",""));

	// load type dropdown and return
	$tasktypes = Html::select("tasktypes",$types,null);
	
	$w->setLayout(null);
	$w->out(json_encode($tasktypes));
	}

// Search Filter: selecting a Type will dynamically load the Priority dropdown with available values
function taskAjaxTypetoPriority_ALL(Web &$w) {
	$priority = array();

	// split the query string into type, group and assignee
	list($type, $group, $assignee) = preg_split('/_/',$_REQUEST['id']);

	// organise criteria
	$who = ($assignee != "") ? $assignee : null;
	$where = "";
	if ($group != "")
		$where .= "task_group_id = " . $group . " and ";
	if ($type != "")
		$where .= "task_type = '" . $type . "' and ";

	$where .= "is_closed = 0";

	// get priorities from available task list 
	$tasks = $w->Task->getTasks($who, $where);
	if ($tasks) {
		foreach ($tasks as $task) {
			if (!array_key_exists($task->priority, $priority))
				$priority[$task->priority] = array($task->priority,$task->priority);
		}
	}
	if (!$priority)
		$priority = array(array("No assigned Tasks",""));
	
	// load priority dropdown and return
	$priority = Html::select("tpriority",$priority,null);
	
	$w->setLayout(null);
	$w->out(json_encode($priority));
	}
	
// Search Filter: selecting a Priority will dynamically load the Status dropdown with available values
function taskAjaxPrioritytoStatus_ALL(Web &$w) {
	$status = array();

	// split query string into proirity, type, group and assignee
	list($priority, $type, $group, $assignee) = preg_split('/_/',$_REQUEST['id']);
	
	// organise criteria
	$who = ($assignee != "") ? $assignee : null;
	$where = "";
	if ($group != "")
		$where .= "task_group_id = " . $group . " and ";
	if ($type != "")
		$where .= "task_type = '" . $type . "' and ";
	if ($priority != "")
		$where .= "priority = '" . $priority . "' and ";
		
	$where .= "is_closed = 0";

	// get statuses from available tasks
	$tasks = $w->Task->getTasks($who, $where);
	if ($tasks) {
		foreach ($tasks as $task) {
			if (!array_key_exists($task->status, $status))
				$status[$task->status] = array($task->status,$task->status);
		}
	}
	if (!$status)
		$status = array(array("No assigned Tasks",""));
	
	// load status dropdown and return
	$status = Html::select("status",$status,null);
	
	$w->setLayout(null);
	$w->out(json_encode($status));
	}

///////////////////////////////////////////////////////
//					TASK ACTIVITY    				 //
///////////////////////////////////////////////////////

// show task activity for the group and date span specified
function taskweek_ALL(Web &$w) {
	task_navigation($w, "");
	
	// if no group then no group
	$taskgroup = ($_REQUEST['taskgroup'] != "") ? $_REQUEST['taskgroup'] : "";
	// if no group member then no group member
	$assignee = ($_REQUEST['assignee'] != "") ? $_REQUEST['assignee'] : "";
	// if no from date then 7 days ago
    $from = ($_REQUEST['dt_from'] != "") ? $_REQUEST['dt_from'] : $w->Task->getLastWeek();
    // if no to date then today
    $to = ($_REQUEST['dt_to'] != "") ? $_REQUEST['dt_to'] : date("d/m/Y");
    // display	
	$w->ctx("from",$from);
    $w->ctx("to",$to);
	
    // get all tasks in my groups answering criteria
    $tasks = $w->Task->getTaskWeek($taskgroup, $assignee, $from, $to);
	
    // set task activity heading
	$line = array(array("An overview of the activity in Tasks: " . $from . " to " . $to));
    if ($tasks) {
    	// dont wanna keep displaying same date so set a variable for comparison
        $olddate = "";
		foreach ($tasks as $task) {
			$taskgroup = $w->Task->getTaskGroup($task['task_group_id']);
			$caniview = $taskgroup->getCanIView();
			
			if ($caniview) {
				// if current task date = previous task date, dont display
				if (formatDate($task['dt_modified']) != $olddate) {
 					// if this is not the first record, display emtpy row between date lists
					if ($i > 0)
      				   $line[] = array("&nbsp;");
	      			// display fancy date
				   $line[] = array("<b>" . date("l jS F, Y", strtotime($task['dt_modified'])) . "</b>");
				}
				// display comments. if no group selected, display with link to task list with group preselected
	      		$thisgroup = ($taskgroup != "") ? "" : "<a title=\"View Task Group\" href=\"" . $webroot . "/task/tasklist/?taskgroups=" . $task['task_group_id'] . "\">" . $w->Task->getTaskGroupTitleById($task['task_group_id']) . "</a>:&nbsp;&nbsp;";
				$line[] = array("<dd>" . date("g:i a", strtotime($task['dt_modified'])) . " - " . $thisgroup . "<a title=\"View Task Details\" href=\"".$webroot."/task/viewtask/".$task['id']."\"><b>".$task['title']."</b></a>: " . $w->Task->findURL($task['comment']) . " - " . $w->Task->getUserById($task['creator_id']) . "</dd>");
				$olddate = formatDate($task['dt_modified']);
				$i++;
			}
		}
	}
	else {
		// if no tasks found, say as much
		$line[] = array("No Task Activity found for given selections.");
	}
	
	// display
	$lines = Html::table($line,null,"tablesorter",true);
	$w->ctx("taskweek",$lines);

	// get list of groups of which i am a member
	$mygroups = $w->Task->getMemberGroups($_SESSION['user_id']);
	if ($mygroups) {
		foreach ($mygroups as $mygroup) {
			$taskgroup = $w->Task->getTaskGroup($mygroup->task_group_id);
			$caniview = $taskgroup->getCanIView();
						
			if ($caniview) {
				$group[$mygroup->task_group_id] = array($w->Task->getTaskGroupTitleById($mygroup->task_group_id),$mygroup->task_group_id);

				// for those groups of which i am a member, get list of all members for display in Assignee & Creator dropdowns
				$mymembers = $w->Task->getMembersInGroup($mygroup->task_group_id);
				foreach ($mymembers as $mymem) {
					$members[$mymem[1]] = array($mymem[0],$mymem[1]);
				}
			}
		}
		sort($members);
	}
	
	// load the search filters
	$a = Html::select("assignee",$members,$_REQUEST['assignee']);
	$w->ctx("assignee",$a);
	
	$taskgroups = Html::select("taskgroup",$group, $_REQUEST['taskgroup']);
	$w->ctx("taskgroups",$taskgroups);
	
}
	
///////////////////////////////////////////////////////
//					TASKS							 //
///////////////////////////////////////////////////////

function viewtask_GET(Web &$w) {
	$p = $w->pathMatch("id");
	
	// declare delete button
	$btndelete = "";
	
	// get relevant object for viewing a task given input task ID
	$task = $w->Task->getTask($p['id']);
	$taskdata = $w->Task->getTaskData($p['id']);
	$group = $w->Task->getTaskGroup($task->task_group_id);
		
	task_navigation($w, "View Task: " . $task->title);

	// if task is deleted, say as much and return to task list
	if ($task->is_deleted != 0) {
		$w->msg("This Task has been deleted","/task/tasklist/");
	}
	// check if i can view the task: my role in group Vs group can_view value
	elseif ($task->getCanIView()) {
		// tab: Task Details
		
		// if I can assign tasks, provide dropdown of group members else display current assignee.
		// my role in group Vs group can_assign value
		if ($task->getCanIAssign()) {
			$members = ($task) ? $w->Task->getMembersBeAssigned($task->task_group_id) : $w->auth->getUsers();
			sort($members);
			$assign = array("Assigned To","select","assignee_id",$task->assignee_id,$members);
		}
		else {
			$assigned = ($task->assignee_id == "0") ? "Not Assigned" : $w->Task->getUserById($task->assignee_id);
			$assign = array("Assigned To","static","assignee_id",$assigned);
		}	

//		changing type = dymanically loading of relevant form fields ... problem when presenting on single page.
//		array("Task Type","select","task_type",$task->task_type,$task->getTaskGroupTypes()),
		
		// check a due date exists
		$dtdue = (($task->dt_due == "0000-00-00 00:00:00") || ($task->dt_due == "")) ? "" : date('d/m/Y',$task->dt_due);
		
		// Guests can view but not edit
		// See if i am assignee or creator, if yes provide editable form, else provide static display
		if ($task->getCanIEdit()) {
			$btndelete = Html::b($webroot."/task/deletetask/".$task->id," Delete Task ", "Are you should you with to DELETE this task?");

			// if task is closed and Task Group type says cannot be reopened, display static status
			if ($task->getisTaskClosed() && !$task->getTaskReopen()) {
				$status = array("Status","static","status",$task->status);
			}
			// otherwise, task is open, or is closed but can be reopened so allow edit of status
			else {
				$status = array("Status","select","status",$task->status,$task->getTaskGroupStatus());
			}
			
			$f = array(
			array("Task Details","section"),
			array("Title","text", "title", $task->title),
			array("Created By","static", "creator", $task->getTaskCreatorName()),
			array("Task Group","static","tg",$task->getTaskGroupTypeTitle()),
			array("Task Type","static","task_type",$task->getTypeTitle()),
			array("Description","static","tdesc",$task->getTypeDescription()),
			$status,
			array("Priority","select","priority",$task->priority,$task->getTaskGroupPriority()),
			array("Date Due","date","dt_due", $dtdue),
			array("Description","textarea", "description",$task->description,"80","15"),
			$assign,
			);
		}
		else {
			$f = array(
			array("Task Details","section"),
			array("Title","static", "title", $task->title),
			array("Created By","static", "creator", $task->getTaskCreatorName()),
			array("Task Group","static","tg",$task->getTaskGroupTypeTitle()),
			array("Task Type","static","task_type",$task->getTypeTitle()),
			array("Description","static","tdesc",$task->getTypeDescription()),
			array("Status","static","status",$task->status),
			array("Priority","static","priority",$task->priority),
			array("Date Due","static","dt_due", $dtdue),
			array("Description","static", "description",str_replace("\r\n","<br>",$task->description)),
			$assign,
			);
		}

		// got additional form fields for this task type
		$form = $w->Task->getFormFieldsByTask($task->task_type);

		// if there are additional form fields, display them
		if ($form) {
			// string match form fields with task data by key
			// can then push db:value into form field for display
			foreach ($form as $x) {
				if ($x[1] == "section") {
					array_push($f, $x);
				}
		
				if ($taskdata) {
					foreach ($taskdata as $td) {
						$key = $td->key;
						$value = $td->value;

						// Guests can view but not edit
						// See if i am a guest, if yes provide static display, else provide editable form
						if (!$task->getCanIEdit())
							$x[1] = "static";
							
						if ($key == $x[2]) {
							$x[3] = $value;
							array_push($f, $x);
						}
					}
				}
				else {
					if ($x[1] != "section")
						array_push($f, $x);
				}
			}
		}

		// create form
		$form = Html::form($f,$w->localUrl("/task/updatetask/".$task->id),"POST"," Update ");

		// create 'start time log' button
		$btntimelog = "<button class=\"startTime\" href=\"/task/starttimelog/".$task->id."\"> Start Time Log </button>";
		
		// display variables
		$w->ctx("btntimelog",$btntimelog);
		$w->ctx("btndelete",$btndelete);
		$w->ctx("viewtask",$form);
		$w->ctx("extradetails",$task->displayExtraDetails());

		// tab: Task Comments
		// provide button for adding new comments
		$add_c = Html::box($w->localUrl("/task/editComment/".$task->id),"Add a New Comment",true);
		$w->ctx("addComment",$add_c);
	
		// provide current comment count in the tab heading for Comments
		$numComments = $task->countTaskComments();
  		$w->ctx("numComments",$numComments);

  		// get the comments list for this task
  		$comments = $task->getTaskComments();

  		// build the table of comments
  		$line = array(array("Comments for Task: " . $task->title));
  		if ($comments) {
			foreach($comments as $com) {
				$line[] = array("<dt><b>".formatDateTime($com->dt_created)."</b><dd>".str_replace("\n","<br>",$w->Task->findURL($com->comment))." - ".$w->Task->getUserById($com->modifier_id) . "</dd>");
				// edit comments?
    			//Html::box($w->localUrl("/task/editComment/".$task->id."/".$com->id)," Edit ",true)
				}
			}
		else {
			$line[] = array("There are no comments for this task");
		}
		
		// display the table of comments
		$w->ctx("comments",Html::table($line,null,"tablesorter",true));
		
		//tab: Task Documents
		// provide a button for adding new documents
		$line = array();
		$putdocos = Html::box($webroot."/task/attachForm/".$task->id," Upload a Document ",true);  
		$w->ctx("btnAttachment",$putdocos);

		// provide a button for add new pages
		$putpages = Html::box($webroot."/task/addpage/".$task->id," Attach a Page ",true);
		$w->ctx("btnPage",$putpages);
		
		// provide current document + page count in tab heading for Documents
		$numDocos = $task->countTaskDocos();
		$numPages = $task->countTaskPages();
		$num = intval($numDocos) + intval($numPages);
		if ($num == "") { $num = "0"; }	
		
  		$w->ctx("numDocos",$num);
		
  		// get the list of documents
  		$docos = $task->getTaskDocos();
  		// get the list of pages accessible to me
  		$pages = $task->getPages($task->id);
  		
  		// build the table of documents
		$hds = array(array("Document", "Uploaded by", "Date", "Description"));
		
		// if documents, list them
  		if ($docos)	{
			foreach ($docos as $doco) {
				$line[] = array("<a href=\"" . $webroot . "/file/atfile/" . $doco->id . "/" . $doco->filename . "\" target=\"_blank\">" . $doco->filename . "</a>",
								$w->Task->getUserById($doco->modifier_user_id),
								formatDateTime($doco->dt_created),
								$doco->description,
								);
				}
			}
		
		// if pages, list them
		if ($pages) {
			foreach ($pages as $page) {
				// get page details. only pages acceswsible to me are returned
				$pg = $w->Page->getPage($w,$page->object_id);
				
				// if page is returned, then i can view it, so list it
				if ($pg) {
					$line[] = array(
								"<a href=\"" . $webroot . "/pages/index/level/" . $pg->id . "\">" . $pg->subject . "</a>",
								$w->Task->getUserById($pg->creator_id),
								formatDateTime($pg->dt_created),
								$page->key,
								);
				}
			}
		}

		// if no documents or pages, say as much
		if (!$line) {
			$line[] = array("There are no documents attached to this task", "", "", "");
		}
		
		// put column headings onto doco/page list
		$line = array_merge($hds, $line);
				
		// display the table of documents
		$w->ctx("docos",Html::table($line,null,"tablesorter",true));
		
		// tab: time log
		// provide button to add time entry
		$addtime = Html::box($webroot."/task/addtime/".$task->id," Add Time Log entry ",true);
		$w->ctx("addtime",$addtime);
		
		// get time log for task
		$timelog = $task->getTimeLog();
		
		// set total period
		$totseconds = 0;
		
		// set headings
		$line = array(array("Assignee", "Created By", "Start", "End", "Period (hours)", ""));
		// if log exists, display ...
		if ($timelog) {
			// for each entry display, calculate period and display total time on task
			foreach ($timelog as $log) {
				// get time difference, start to end
				$seconds = $log->dt_end - $log->dt_start;
				$period = $w->Task->getFormatPeriod($seconds);

				// if suspect, label button, style period, remove edit button
				if ($log->is_suspect == "1") {
					$label = " Accept ";
					$period = "(" . $period . ")";
					$bedit = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				}
				// if accepted, label button, tally period, include edit button
				if ($log->is_suspect == "0") {
					$label = " Review ";
					$totseconds += $seconds;
					$bedit = Html::box($w->localUrl("/task/addtime/".$task->id."/".$log->id)," Edit ",true);
				}

				// ony Task Group owner gets to reject/accept time log entries
				$bsuspect = ($w->Task->getIsOwner($task->task_group_id, $_SESSION['user_id'])) ? Html::b($w->localUrl("/task/suspecttime/".$task->id."/".$log->id),$label) : "";

				$line[] = array($w->Task->getUserById($log->user_id),
                                $w->Task->getUserById($log->creator_id),
								formatDateTime($log->dt_start),
								formatDateTime($log->dt_end),
								$period,
								$bedit . 
								"&nbsp;" . 
								Html::b($w->localUrl("/task/deletetime/".$task->id."/".$log->id)," Delete ","Are you sure you wish to DELETE this Time Log Entry?") .
								"&nbsp;" . 
								$bsuspect .
								"&nbsp;" . 
								Html::box($w->localUrl("/task/popComment/".$task->id."/".$log->comment_id)," Comment ",true)
								);
			}
			$line[] = array("","","","<b>Total</b>", "<b>".$w->Task->getFormatPeriod($totseconds)."</b>","");
		}
		else {
			$line[] = array("No time log entries have been made","","","","","");
		}		

		// display the task time log
		$w->ctx("timelog",Html::table($line,null,"tablesorter",true));
		
		// tab: notifications
		// if i am assignee, creator or task group owner, i can get notifications for this task
		if ($task->getCanINotify()) {
			// if i can get notifications for this Task, display tbe navigation tab
			$tab = "<a id=\"tab-link-5\" href=\"#\" onclick=\"switchTab(5);\">Task Notifications</a>\n";
			$w->ctx("tasknotifications",$tab);
			
			// get User set notifications for this Task
			$notify = $w->Task->getTaskUserNotify($_SESSION['user_id'],$task->id);
			if ($notify) {
				$task_creation = $notify->task_creation;
				$task_details = $notify->task_details;
				$task_comments = $notify->task_comments;
				$time_log = $notify->time_log;
				$task_documents = $notify->task_documents;
				$task_pages = $notify->task_pages;
			}
			// no user notifications, get user set notifications for the Task Group
			else {
				// need my role in group
				$me = $w->Task->getMemberGroupById($task->task_group_id, $_SESSION['user_id']);
				// get task creator ID
				$creator_id = $task->getTaskCreatorId();
				
				// which am i?
				$assignee = ($task->assignee_id == $_SESSION['user_id']) ? true : false;
				$creator = ($creator_id == $_SESSION['user_id']) ? true : false;
				$owner = $w->Task->getIsOwner($task->task_group_id, $_SESSION['user_id']);
				
				// get single type given this is specific to a single Task
				if ($assignee) {
					$type = "assignee";
				}
				elseif ($creator) {
					$type = "creator";
				}
				elseif ($owner) {
					$type = "other";
				}

				$role = strtolower($me->role);

				if ($type) {
					// for type, check the User defined notification table 
					$notify = $w->Task->getTaskGroupUserNotifyType($_SESSION['user_id'],$task->task_group_id,$role,$type);

					// get list of notification flags
					if ($notify) {
						if ($notify->value == "1") {
							$task_creation = $notify->task_creation;
							$task_details = $notify->task_details;
							$task_comments = $notify->task_comments;
							$time_log = $notify->time_log;
							$task_documents = $notify->task_documents;
							$task_pages = $notify->task_pages;
						}
					}
				}
			}
			
			// create form. if still no 'notify' all boxes are unchecked
			$f = array(array("For which Task Events should you receive Notification?","section"));
			$f[] = array("","hidden","task_creation", "0");
			$f[] = array("Task Details Update","checkbox","task_details", $task_details);
			$f[] = array("Comments Added","checkbox","task_comments", $task_comments);
			$f[] = array("Time Log Entry","checkbox","time_log", $time_log);
			$f[] = array("Task Data Updated","checkbox","task_data", $task_data);
			$f[] = array("Documents Added","checkbox","task_documents", $task_documents);
			$f[] = array("Pages Added","checkbox","task_pages", $task_pages);

			$form = Html::form($f,$w->localUrl("/task/updateusertasknotify/".$task->id),"POST","Save");

			// display form in tab.div
			$tasknotify = "<div id=\"tab-5\" style=\"display: none;\">\n" .
						  "Set your Notifications specific to this Task, otherwise your notifications for this Task Group will be employed.\n" .
			    		  "<p>\n" .
						  $form . 
						  "</div>\n";

			// display
			$w->ctx("tasknotify",$tasknotify);
		}
	}
	else {
		// if i cannot view task details, return to task list with error message
		// for display get my role in the group, the group owners, the group title and the minimum membership required to view a task
		$me = $w->Task->getMemberGroupById($task->task_group_id, $_SESSION['user_id']);
		$myrole = (!$me) ? "Not a Member" : $me->role;
		$owners = $w->Task->getTaskGroupOwners($task->task_group_id);

		// get owners names for display
		foreach ($owners as $owner) {
			$strOwners .= $w->Task->getUserById($owner->user_id) . ", ";
		}
		$strOwners = rtrim($strOwners,", ");

		$form = "You must be at least a <b>" . $group->can_view . "</b> of the Task Group: <b>" . strtoupper($group->title) . "</b>, to view tasks in this group.<p>Your current Membership Level: <b>" . $myrole . "</b><p>Please appeal to the group owner(s): <b>" . $strOwners . "</b> for promotion.";

		$w->error($form,"/task/tasklist");
	}
	
}

// Step I in creating a task. This function displays the default task creation form
function createtask_GET(Web &$w) {
	task_navigation($w, "Create Task");
	
	// set default dropdowns for these task attributes as empty arrays
	// dropdowns are populated dynamically via JSON based upon task group type selected
	$tasktypes = array();
	$priority = array();
	$members = array();
	
	// get list of all task groups
	$taskgroups = $w->Task->getTaskGroups();

	// whittle list of task groups down to only those in which i have role appropriate for creating tasks
	if ($taskgroups){
		foreach ($taskgroups as $tgroup) {
			if ($tgroup->getCanICreate()) {
					$mytaskgroups[] = array($tgroup->title,$tgroup->id);
				}
		}	

		if ($_REQUEST['gid'] != "") {
			$t = $w->Task->getTaskGroup($_REQUEST['gid']);
	
			$tasktypes = ($t != "") ? $w->Task->getTaskTypes($t->task_group_type) : array();
			$priority = ($t != "") ? $w->Task->getTaskPriority($t->task_group_type) : array();
			$members = ($t != "") ? $w->Task->getMembersBeAssigned($t->id) : array();
			sort($members);
			
			$tasktext = "<table border=0 class=form>" . 
				"<tr><td class=section colspan=2>Task Group Description</td></tr>" . 
				"<tr><td><b>Task Group</td><td>" . $t->title . "</td></tr>" . 
				"<tr><td><b>Task Type</b></td><td>" . $t->getTypeTitle() . "</td></tr>" . 
				"<tr><td><b>Description</b></td><td>" . $t->getTypeDescription() . "</td></tr>" . 
				"</table><p>";
		
			$w->ctx("tasktext",$tasktext);
		}
		
		// build form
		$f = Html::form(array(
		array("Create a New Task - Step One","section"),
		array("Task Group","select","task_group_id",$_REQUEST['gid'],$mytaskgroups),
		array("Task Title","text","title"),
		array("Task Type","select","task_type",null,$tasktypes),
		array("Priority","select","priority",null,$priority),
		array("Date Due","date","dt_due"),
		array("Description","textarea","description",null,"80","15"),
		array("Assigned To","select","first_assignee_id",null,$members),
		),$w->localUrl("/task/tasktypeform/"),"POST"," Continue >> ");
	}
	// display form
	$w->ctx("createtask",$f);
}

// Step II in creating a task. This function gets the additional fields by tasktype.
// Serialise REQUEST object from step one and store in hidden form element: 'formone'
function tasktypeform_POST(Web $w) {
	task_navigation($w, "Create Task");

	// get task type, serialise REQUEST object from step 1 of creating a new task
	$tid = $_REQUEST['task_type'];

	// if no due date given, make 1 month from today
	if ($_REQUEST['dt_due'] == "")
		$_REQUEST['dt_due'] = $w->Task->getNextMonth();
	
	$req = serialize($_REQUEST);

	// get the additional form fields based on type type
	$theform = array();
	if ($tid != "") {
		$theform = $w->Task->getFormFieldsByTask($tid);
	}

	if (!$theform) {
		$theform = array(array("Message","static","text","No further information required.<p>Please save your task."));
	}

	// combine input from step one with form fields for step II
	$hiden = array("","hidden","formone",$req);
	array_push($theform, $hiden);

	// display the form
	$f = Html::form($theform, $w->localUrl("/task/createtask/"),"POST"," Submit ");
	$w->ctx("formfields",$f);
}

function createtask_POST(Web &$w) {
	task_navigation($w, "Create Task");

	// unserialise input from step I and store in array: arr_req
	$arr_req = unserialize($_REQUEST['formone']);

	// set relevant dt variables with: Today.
	$arr_req['dt_assigned'] = Date('c');
	$arr_req['dt_first_assigned'] = Date('c');

	// insert Task into database
	$task = new Task($w);
	$task->fill($arr_req);
	$task->insert();
	
	// if insert is successful, store additional fields as task data
	// we do not want to store data from step I, the task_id (as a key=>value pair) nor the FLOW_SID
	if ($task->id) {
		foreach ($_REQUEST as $name => $value) {
			if (($name != "formone") && ($name != "FLOW_SID") && ($name != "task_id")) {
				$tdata = new TaskData($w);
				$arr = array("task_id"=>$task->id,"key"=>$name,"value"=>$value);
				$tdata->fill($arr);
				$tdata->insert();
				unset($arr);
			}
		}

		// return to task dashboard
		$w->msg("Task ".$task->title." added","/task/viewtask/".$task->id);
	}
	else {
		// if task insert was unsuccessful, say as much
		$w->msg("The Task could not be created. Please inform the IT Group","/task/index/");
	}	
}

// update status using dropdowns provided on Task List
function updatestatus_ALL(Web &$w) {
	// check for required REQUEST elements
	if (($_REQUEST['id'] != "") && ($_REQUEST['status'] != "")) {
		// task is to get updated so gather relevant data
		$task = $w->Task->getTask($_REQUEST['id']);

		// if task exists, first gather changes for display in comments
		if ($task) {
			$comments = "status updated to: " . $_REQUEST['status'] . "\n";

			$task->fill($_REQUEST);
		
			// if task has a 'closed' status, set flag so task no longer appear in dashboard count or task list
			if ($task->getisTaskClosed()) {
				$task->is_closed = 1;
				$task->dt_completed = date("d/m/Y");
			}
			else {
				$task->is_closed = 0;
			}
			
			$task->update();

			// we have comments, so add them
    	    $comm = new TaskComment($w);
        	$comm->obj_table = $task->getDbTableName();
	        $comm->obj_id = $task->id;
	        $comm->comment = $comments;
	        $comm->dt_created = Date("c");
	        $comm->is_deleted = 0;
    	    $comm->insert();

		    // add to context for notifications post listener
    	    $w->ctx("TaskComment",$comm);
    		$w->ctx("TaskEvent","task_details");
		}
		// return
		$w->msg("Task: " . $task->title . " updated.","/task/tasklist/?assignee=".$_REQUEST['assignee']."&creator=".$_REQUEST['creator']."&taskgroups=".$_REQUEST['taskgroups']."&tasktypes=".$_REQUEST['tasktypes']."&tpriority=".$_REQUEST['tpriority']."&status=".$_REQUEST['tstatus']."&dt_from=".$_REQUEST['dt_from']."&dt_to=".$_REQUEST['dt_to']);
	}

	// return
	$w->msg("There was a problem.","/task/tasklist/");
	
}

function updatetask_POST(Web &$w) {
	$p = $w->pathMatch("id");
	
	// task is to get updated so gather relevant data
	$task = $w->Task->getTask($p['id']);
	$taskdata = $w->Task->getTaskData($p['id']);

	// if task exists, first gather changes for display in comments
	if ($task) {
		// if no due date, make 1 month from now
		if ($_REQUEST['dt_due'] == "")
			$_REQUEST['dt_due'] = $w->Task->getNextMonth();

		// convert dates to d/m/y for display. if assignee changes, get name of new assignee
		foreach ($_REQUEST as $name => $value) {
			if (startsWith($name,"dt_")) {
				list($d,$m,$y) = preg_split('/\//',$value);
				$value = Date("U",strtotime($y . "-" . $m . "-" . $d));
			}
			if (($name != "FLOW_SID") && ($task->$name) && ($value != $task->$name)) {
				if (startsWith($name,"dt_"))
					$value = Date("d/m/Y",$value);
				if ($name == "assignee_id")
					$value = $w->Task->getUserById($value);

				$comments .= $name . " updated to: " . $value . "\n";
			}
		}

		
		// update the task
		$_REQUEST['dt_assigned'] = Date('c');
		$task->fill($_REQUEST);
		
		// if task has a 'closed' status, set flag so task no longer appear in dashboard count or task list
		if ($task->getisTaskClosed()) {
			$task->is_closed = 1;
			$task->dt_completed = date("d/m/Y");
		}
		else {
			$task->is_closed = 0;
		}
		
		$task->update();

		// if we have comments, add them
		if ($comments) {
	        $comm = new TaskComment($w);
    	    $comm->fill($_REQUEST);
        	$comm->obj_table = $task->getDbTableName();
	        $comm->obj_id = $task->id;
	        $comm->comment = $comments;
    	    $comm->insert();

		    // add to context for notifications post listener
    		$w->ctx("TaskComment",$comm);
    		$w->ctx("TaskEvent","task_details");
		}
	}

	// if there is task data, update it also
	// if there is current no task data, but relevant input in the REQUEST object, create the task data
	if ($taskdata) {
		foreach ($taskdata as $td) {
			$arr = array("value"=>$_REQUEST[$td->key]);
			$td->fill($arr);
			$td->update();
			unset($arr);
			}
		}
	else {
		foreach ($_REQUEST as $name => $value) {
			if ($name != "FLOW_SID") {
				$tdata = new TaskData($w);
				$arr = array("task_id"=>$task->id,"key"=>$name,"value"=>$value);
				$tdata->fill($arr);
				$tdata->insert();
				unset($arr);
			}
		}
	}

	// return
	$w->msg("Task: " . $task->title . " updated.","/task/viewtask/".$task->id."?tab=1");
}

function deletetask_ALL(Web &$w) {
	$p = $w->pathMatch("id");
	
	// task is to get updated so gather relevant data
	$task = $w->Task->getTask($p['id']);

	// if task exists, continue
	if ($task) {
		$arr['is_closed'] = 1;
		$arr['is_deleted'] = 1;
		$task->fill($arr);
		$task->update();
		$w->msg("Task: " . $task->title . " has been deleted.","/task/tasklist/");
	}
	else {
		$w->msg("Task: " . $task->title . " could not be found.","/task/tasklist/");
	}	
}

function editComment_GET(Web &$w) {
	$p = $w->pathMatch("taskid","comm_id");

	// get the relevant comment
	$comm = $w->Task->getComment($p['comm_id']);

	// build the comment for edit
	$form = array(
    		array("Comment","section"),
			array("","textarea","comment",strip_tags($comm->comment),45,25),
			);

	// return the comment for display and edit
	$form = Html::form($form,$w->localUrl("/task/editComment/".$p['taskid']."/".$p['comm_id']),"POST","Save");
	$w->setLayout(null);
    $w->out($form);
} 

function popComment_GET(Web &$w) {
	$p = $w->pathMatch("taskid","comm_id");

	// get the relevant comment
	$comm = $w->Task->getComment($p['comm_id']);

	// build the comment for display
	$form = array(
    		array("Comment","section"),
			array("","textarea","comment",strip_tags($comm->comment),45,25),
			);

	// return the comment for display
	$form = Html::form($form);
	$w->setLayout(null);
    $w->out($form);
} 

function editComment_POST(Web $w) {
  	$p = $w->pathMatch("taskid","comm_id");
  	$task = $w->Task->getTask($p['taskid']);

  	// convert any HTML to entities for display
  	$_REQUEST['comment'] = htmlspecialchars($_REQUEST['comment']);

  	// get the relevant comment
  	$comm = $w->Task->getComment($p['comm_id']);
	
  	// if comment exists, update it. if not, create it.
    if ($comm) {
        $comm->fill($_REQUEST);
        $comm->update();
        $commsg = "Comment updated.";
    }
    else {
        $comm = new TaskComment($w);
        $comm->fill($_REQUEST);
        $comm->obj_table = $task->getDbTableName();
        $comm->obj_id = $p['taskid'];
        $comm->insert();
        $commsg = "Comment created.";
    }
	// add to context for notifications post listener
    $w->ctx("TaskComment",$comm);
   	$w->ctx("TaskEvent","task_comments");
    
    // return
    $w->msg($commsg,"/task/viewtask/".$p['taskid']."?tab=3");

} 

function attachForm_GET(Web $w) {
	$p = $w->pathMatch("id");
	
	// get relevant task
	$task = $w->Task->getTask($p['id']);
	
	// build form to upload document/attachment
	$form = array(
		array("Attach Document","section"),
        array("Document","file","form"),
		array("Description","textarea","description",null,"26","6"),
        );

    // diplay form
    $form = Html::form($form,$w->localUrl("/task/attachForm/".$task->id),"POST"," Upload ", null, null, null, 'multipart/form-data'); 
    
    $w->setLayout(null);
    $w->out($form);
}


function attachForm_POST(Web $w) {
	$p = $w->pathMatch("id");
	
	// get relevant task
	$task = $w->Task->getTask($p['id']); 
    
	// if task exists get REQUEST and FILE object for insert into attachment database against this task
    if ($task) {
        $description = $w->request('description');

        if ($_FILES['form']['size'] > 0) {
	    	$filename = strtolower($_FILES['form']['name']); 
 			$parts = explode(".", $filename) ;
 			$n = count($parts)-1; 
 			$ext = $parts[$n]; 
	    	
	    	$attach = $w->File->uploadAttachment("form",$task,null,$description);
	    	if (!$attach) {
	    		$message = "There was an error. The document could not be saved.";
	    	}
	    	else {
	    		$message = "The Document has been uploaded.";
	    	}
        }

        // create comment
        $comm = new TaskComment($w);
        $comm->obj_table = $task->getDbTableName();
	    $comm->obj_id = $task->id;
	    $comm->comment = "File Uploaded: " . $filename;
    	$comm->insert();

	    // add to context for notifications post listener
    	$w->ctx("TaskComment",$comm);
    	$w->ctx("TaskEvent","task_documents");
    }
    
    // return
    $w->msg($message,"/task/viewtask/".$task->id."?tab=4");
}

function addpage_GET(Web &$w) {
	$p = $w->pathMatch("id");

	// get list of pages accessible to me
	$pages = $w->Page->getUserPageTitles();

	// create form
	$f = array(
			array("Select a Page","section"),
			array("Page","autocomplete","page",null,$pages)
			);

	$form = Html::form($f,$w->localUrl("/task/addpage/".$p['id']),"POST","Save");
	
	// return and display form
	$w->setLayout(null);
    $w->out($form);
}

function addpage_POST(Web &$w) {
	$p = $w->pathMatch("id");
	
	if ($_POST['page'] == "0") {
    	// 'blank' selected so return
    	$w->msg("Please select a PAGE","/task/viewtask/".$p['id']."?tab=4");
	}
	else {
		// get relevant task
		$task = $w->Task->getTask($p['id']);
		// get page
		$page = $w->Page->getPage($w, $_REQUEST['page']);
		// get first 100 characters, minus HTML tags, for display as 'description'
		$content = substr(strip_tags($page->body),0,100);

		// create Task Object
		$obj = new TaskObject($w);
		$obj->task_id = $p['id'];
		$obj->key = $content;
		$obj->table_name = "page";
		$obj->object_id = $_REQUEST['page'];
		$obj->insert();
    
    	// create comment
	    $comm = new TaskComment($w);
    	$comm->obj_table = $task->getDbTableName();
		$comm->obj_id = $task->id;
		$comm->comment = "Page Attached to Task: " . $page->subject;
	    $comm->insert();

		// add to context for notifications post listener
    	$w->ctx("TaskComment",$comm);
	    $w->ctx("TaskEvent","task_pages");
	
    	// return
    	$w->msg("Page Added to Task","/task/viewtask/".$p['id']."?tab=4");
	}
}

//////////////////////////////////
//			TIME LOG			//
//////////////////////////////////

function addtime_GET(Web &$w) {
	$p = $w->pathMatch("taskid","log_id");

	$hours = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24");
	$mins = array("00","05","10","15","20","25","30","35","40","45","50","55");
	
	// get the relevant comment
	$log = $w->Task->getTimeLogEntry($p['log_id']);
	$task = $w->Task->getTask($p['taskid']);
	
	// if log entry exists, populate form with values
	if ($log) {
		$who = $log->user_id;
		$s = date("d/m/Y g:i a",$log->dt_start);
		$e = date("d/m/Y g:i a",$log->dt_end);
		
  		$comm = $w->Task->getComment($log->comment_id);
  		$comment = $comm->comment;
	}
	// if new entry, set current date and time
	else {
		$who = $_SESSION["user_id"];
		$s = $e = date("d/m/Y g:i a");
	}
	
	$f = array(
			array("Add Time Log Entry","section"),
			array("Assignee","select","user_id",$who, $w->Task->getMembersBeAssigned($task->task_group_id)),
			array("Select the Start Date & Time","section"),
			array("Date/Time","datetime","dt_start", $s),
			array("Select the End Date & Time, or Period worked","section"),
			array("Date/Time","datetime","dt_end",$e),
			array("Or Period:","static","OR","<b>Below select the period worked since the Start Date/Time</b>"),
			array("Hours","select","per_hour",null,array_slice($hours, 0, 11)),
			array("Min","select","per_minute",null,$mins),
			array("Comments","section"),
			array("Comments","textarea","comments",$comment,"40","10"),
			);
			
	$form = Html::form($f,$w->localUrl("/task/edittime/".$p['taskid']."/".$p['log_id']),"POST","Save");
	
	$w->setLayout(null);
    $w->out($form);
		
}

function edittime_POST(Web $w) {
  	$p = $w->pathMatch("taskid","log_id");

  	// lets set some defaults if no selections are made
  	$_REQUEST["dt_start"] = ($_REQUEST["dt_start"] != "") ? $_REQUEST["dt_start"] : date("d/m/Y G:i"); 
  	$_REQUEST["dt_end"] = ($_REQUEST["dt_end"] != "") ? $_REQUEST["dt_end"] : date("d/m/Y G:i"); 
  	
	// get the relevant log entry
	$log = $w->Task->getTimeLogEntry($p['log_id']);
  	
	// set time log values
	$arr["task_id"] = $p["taskid"];
	$arr["creator_id"] = $_SESSION["user_id"];
	$arr["dt_created"] = date("d/m/Y");
	$arr["user_id"] = $_REQUEST["user_id"];
	
	list ($date,$time,$ampm) = preg_split("/\s/",$_REQUEST['dt_start']);
	$start = $w->Task->date2db($date) . " " . $time . " " . $ampm;
	$arr["dt_start"] = date("Y-m-d G:i",strtotime($start));
	
	if (($_REQUEST['per_hour'] != "") || ($_REQUEST['per_minute'] != "")) {
		$s = strtotime($arr["dt_start"]);
		$phour = ($_REQUEST["per_hour"] != "") ? $_REQUEST["per_hour"] : 0;
		$pmin = ($_REQUEST["per_minute"] != "") ? $_REQUEST["per_minute"] : 0;
		$arr["dt_end"] = date("Y-m-d G:i", mktime(date("G",$s)+$phour, date("i",$s)+$pmin, 0, date("m",$s) , date("d",$s), date("Y",$s)));
	}
	else {
		list ($date,$time,$ampm) = preg_split("/\s/",$_REQUEST['dt_end']);
		$end = $w->Task->date2db($date) . " " . $time . " " . $ampm;
		$arr["dt_end"] = date("Y-m-d G:i",strtotime($end));
	}
	
	// check that end time is later than start time
	if (strtotime($arr["dt_start"]) > strtotime($arr["dt_end"])) {
		$logmsg = "Start is greater than End. Please enter again.";		
	}
	else {
		$logmsg = ($log) ? "Time Log Entry updated." : "Time Log Entry created.";
		
	    // add comment
	    $comm = new TaskComment($w);
	    $comm->obj_table = "Task";
		$comm->obj_id = $arr["task_id"];
		$comm->comment = $logmsg . $w->Task->getUserById($arr["user_id"]) . " - " . formatDateTime($arr["dt_start"]) . " to " . formatDateTime($arr["dt_end"]) . " - Comments: " . $_REQUEST['comments'];
	    $comm->insert();
	    
	    // add to context for notifications post listener
	    $w->ctx("TaskComment",$comm);
    	$w->ctx("TaskEvent","time_log");
    	
	    $arr["comment_id"] = $comm->id;
	    
	    // if log entry exists, update it. if not, create it.
    	if ($log) {
	        $log->fill($arr);
    	    $log->update();
	    }
	    else {
	    	$log = new TaskTime($w);
        	$log->fill($arr);
	        $log->insert();
	    }
	}
	
    // return
    $w->msg($logmsg,"/task/viewtask/".$p['taskid']."?tab=2");

} 

function suspecttime_ALL(Web &$w) {
  	$p = $w->pathMatch("taskid","log_id");
		
	// get the relevant log entry
	$log = $w->Task->getTimeLogEntry($p['log_id']);
	
	// toggle database field based on current setting
	if ($log->is_suspect == "0") {
		$log->is_suspect = "1";
		$logmsg = "Time Log entry marked for review";
	}
	else {
		$log->is_suspect = "0";
		$logmsg = "Time Log entry accepted";
		}
	$log->update();
	
	// add comment
	$comm = new TaskComment($w);
	$comm->obj_table = "Task";
	$comm->obj_id = $log->task_id;
	$comm->comment = $logmsg . " - " . formatDateTime($log->dt_start) . " to " . formatDateTime($log->dt_end);
	$comm->insert();
	
	// add to context for notifications post listener
	$w->ctx("TaskComment",$comm);
   	$w->ctx("TaskEvent","time_log");
   	
    $w->msg($logmsg,"/task/viewtask/".$p['taskid']."?tab=2");
}

function deletetime_ALL(Web &$w) {
  	$p = $w->pathMatch("taskid","log_id");
		
	// get the relevant log entry
	$log = $w->Task->getTimeLogEntry($p['log_id']);
  	
	// if log entry exists, continue
	if ($log) {
		$arr['is_deleted'] = 1;
		$log->fill($arr);
		$log->update();

	    // add comment
	    $comm = new TaskComment($w);
	    $comm->obj_table = "Task";
		$comm->obj_id = $log->task_id;
		$comm->comment = "Time Log Entry deleted: " . $w->Task->getUserById($log->user_id) . " - " . formatDateTime($log->dt_start) . " to " . formatDateTime($log->dt_end);
	    $comm->insert();
	    
	    // add to context for notifications post listener
	    $w->ctx("TaskComment",$comm);
    	$w->ctx("TaskEvent","time_log");
    	
	    $w->msg("Time Log entry has been deleted.","/task/viewtask/".$p['taskid']."?tab=2");
	}
	else {
		$w->msg("Time Log entry could not be found.","/task/viewtask/".$p['taskid']."?tab=2");
	}	
}


// popup task time logger
function starttimelog_ALL(Web &$w) {
	$p = $w->pathMatch("id");

	if ($_POST['started'] == "yes") {
		// get time log
		$log = $w->Task->getTimeLogEntry($_POST['logid']);
	
		// update time log entry
    	$log->dt_end = date("Y-m-d G:i");
		$log->update();
		
		// set page variables
		$start = date("Y-m-d G:i", $log->dt_start);
		$end = $log->dt_end;
		$taskid = $_POST['taskid'];
		$tasktitle = $_POST['tasktitle'];
		$logid = $_POST['logid'];
	}
	else {
		// get the task
		$task = $w->Task->getTask($p['id']);
	
		// set time log values
		$arr["task_id"] = $task->id;
		$arr["creator_id"] = $_SESSION["user_id"];
		$arr["dt_created"] = date("d/m/Y");
		$arr["user_id"] = $_SESSION["user_id"];
	
		// format start and end times for database
		$start = $arr["dt_start"] = date("Y-m-d G:i");
		$end = $arr["dt_end"] = date("Y-m-d G:i");

		// add time log entry
		$log = new TaskTime($w);
	    $log->fill($arr);
		$log->insert();
		
		// set page variables
		$taskid = $task->id;
		$tasktitle = $task->title;
		$logid = $log->id;
	}

	// create page
	$html = "<html><head><title>Task Time Log - " . $task->title . "</title>" .
			"<style type=\"text/css\">" .
			"body { background-color: #8ad228; }" .
			"td { background-color: #ffffff; color: #000000; font-family: verdana, arial; font-weight: bold; font-size: .8em; }" .
			"td.startend { background-color: #d2efab; color: #000000; font-family: verdana, arial; font-weight: bold; font-size: .9em; }" .
			"td.timelog { background-color: #75ba4d; color: #000000; font-family: verdana, arial; font-weight: bold; font-size: .9em; }" .
			"td.tasktitle { background-color: #9fea72; color: #000000; font-family: verdana, arial; font-weight: bold; font-size: .8em; }" .
			"a { text-decoration: none; } " .
			"a:hover { color: #ffffff; } " .
			"</style>" . 
			"<script language=\"javascript\">" .
			"var thedate = new Date();" .
			"thedate.setDate(thedate.getDate()+1);" .
			"document.cookie = \"thiswin=true;expires=\" + thedate.toGMTString() + \";path=/\";" .
			"function doUnLoading() {" .
			"	var thedate = new Date();" .
			"	thedate.setDate(thedate.getDate()-1);" .
			"	document.cookie = \"thiswin=true;expires=\" + thedate.toGMTString() + \";path=/\";" .
			"	document.theForm.action = \"/task/endtimelog\";" .
			"	document.theForm.submit();" .
			"}" .
			"function beforeUnLoading() {" .
			"	document.theForm.restart.value = \"yes\";" .
			"	doUnLoading();" .
			"}" .
			"function goTask() {" .
			"	window.opener.location.href = \"/task/viewtask/" . $taskid . "\";" .
			"}" .
			"</script></head><body leftmargin=0 topmargin=0 marginwidth=0 marginheight=0 onbeforeunload=\"javascript: doUnLoading();\">" .
			"<form name=theForm action=\"/task/starttimelog\" method=POST>" .
			"<table cellpadding=2 cellspacing=2 border=0 width=100%>" .
			"<tr align=center><td colspan=2 class=timelog>Task Time Log</td></tr>" .
			"<tr align=center><td colspan=2 class=tasktitle><a title=\"View Task\" href=\"javascript: goTask();\">" . $tasktitle . "</a></td></tr>" .
			"<tr align=center><td width=50% class=startend>Start</td><td width=50% class=startend>Stop</td></tr>" .
			"<tr align=center><td>" . date("g:i a", strtotime($start)) . "</td><td>" . date("g:i a", strtotime($end)) . "</td></tr>" .
			"<tr align=center><td colspan=2 class=timelog>&nbsp;</td></tr>" .
			"<tr><td colspan=2 class=startend>Comments</td></tr>" .
			"<tr><td colspan=2 align=center><textarea name=comments rows=4 cols=40>" . $_POST['comments'] . "</textarea></td></tr>" . 
			"<tr align=center>" .
			"<td class=timelog align=right><button id=end onClick=\"javascript: beforeUnLoading();\">Save Comments</button></td>" .
			"<td class=timelog align=left><button id=end onClick=\"javascript: doUnLoading();\">Stop Time Now</button></td>" . 
			"</tr>" .
			"</table>" .
			"<input type=hidden name=started value=\"yes\">" .
			"<input type=hidden name=restart value=\"no\">" .
			"<input type=hidden name=taskid value=\"".$taskid."\">" .
			"<input type=hidden name=tasktitle value=\"".$tasktitle."\">" .
			"<input type=hidden name=logid value=\"".$logid."\">" .
			"</form>" .
			"<script language=javascript>" .
			"document.theForm.comments.focus();" .
			"var r = setTimeout('theForm.submit()',1000*60*5);"  . 
			"</script>" .
			"</body></html>";
	
	// output page
	$w->setLayout(null);
	$w->out($html);
	}

function endtimelog_ALL(Web &$w) {
	// get time log
	$log = $w->Task->getTimeLogEntry($_REQUEST['logid']);
	// get the task
	$task = $w->Task->getTask($_REQUEST["taskid"]);
	$tasktitle = $task->title;
	
	if ($log) {
		// set log end. used in comment
    	$log->dt_end = date("Y-m-d G:i");
		
    	// set comment
		$comment = "Time Log Entry: " . $w->Task->getUserById($log->user_id) . " - " . formatDateTime($log->dt_start) . " to " . formatDateTime($log->dt_end);
		if ($_REQUEST['comments'] != "")
			$comment .= " - Comments: " . htmlspecialchars($_REQUEST['comments']);
		
		// add comment
		$comm = new TaskComment($w);
		$comm->obj_table = $task->getDbTableName();
		$comm->obj_id = $_REQUEST["taskid"];
		$comm->comment = $comment;
		$comm->insert();

	    // add to context for notifications post listener
		$w->ctx("TaskComment",$comm);
    	$w->ctx("TaskEvent","time_log");
		
    	// update time log entry
    	$log->dt_end = date("Y-m-d G:i");
    	$log->comment_id = $comm->id;
		$log->update();
	}
	
	// if 'Save Comment' display current entry and restart time log
	if ($_REQUEST['restart'] == "yes") {
		// create page
		$html = "<html><head><title>Task Time Log - " . $task->title . "</title>" .
				"<style type=\"text/css\">" .
				"body { background-color: #8ad228; }" .
				"td { background-color: #ffffff; color: #000000; font-family: verdana, arial; font-weight: bold; font-size: .8em; }" .
				"td.startend { background-color: #d2efab; color: #000000; font-family: verdana, arial; font-weight: bold; font-size: .9em; }" .
				"td.timelog { background-color: #75ba4d; color: #000000; font-family: verdana, arial; font-weight: bold; font-size: .9em; }" .
				"td.tasktitle { background-color: #9fea72; color: #000000; font-family: verdana, arial; font-weight: bold; font-size: .8em; }" .
				"a { text-decoration: none; } " .
				"a:hover { color: #ffffff; } " .
				"</style>" . 
				"<script language=\"javascript\">" .
				"function reStart() {" .
				"	location.href = \"/task/starttimelog/" . $_REQUEST["taskid"] . "\";" .
				"}" .
				"var c = setTimeout('reStart()',2000);" .
				"</script></head><body leftmargin=0 topmargin=0 marginwidth=0 marginheight=0>" .
				"<table cellpadding=2 cellspacing=2 border=0 width=100%>" .
				"<tr align=center><td colspan=2 class=timelog>Task Time Log</td></tr>" .
				"<tr align=center><td colspan=2 class=tasktitle><a title=\"View Task\" href=\"javascript: goTask();\">" . $tasktitle . "</a></td></tr>" .
				"<tr align=center><td width=50% class=startend>Start</td><td width=50% class=startend>Stop</td></tr>" .
				"<tr align=center><td>" . date("g:i a", $log->dt_start) . "</td><td>" . date("g:i a", strtotime($log->dt_end)) . "</td></tr>" .
				"<tr align=center><td colspan=2 class=timelog>&nbsp;</td></tr>" .
				"<tr><td colspan=2 class=startend>Comments</td></tr>" .
				"<tr><td colspan=2>" . str_replace("\n","<br>",$_POST['comments']) . "</td></tr>" . 
				"</table>" .
				"</body></html>";

	}
	else {
		$html = "<html><head>" .
				"<script language=\"javascript\">" .
				"self.close();" .
				"</script></head></html>";
	}
	
	// output page
	$w->setLayout(null);
	$w->out($html);
}

//////////////////////////////////////
//		TASK NOTIFICATIONS			//
//////////////////////////////////////

function updateusergroupnotify_GET(Web &$w) {
	$p = $w->pathMatch("id");
	
	// get task title
	$title = $w->Task->getTaskGroupTitleById($p['id']);
	
	// get member
	$member = $w->Task->getMemberGroupById($p['id'],$_SESSION['user_id']);
	
	// get user notify settings for Task Group
	$notify = $w->Task->getTaskGroupUserNotify($_SESSION['user_id'],$p['id']);
	if ($notify) {
		foreach ($notify as $n) {
			$v[$n->role][$n->type] = $n->value;
			$task_creation = $n->task_creation;
			$task_details = $n->task_details;
			$task_comments = $n->task_comments;
			$time_log = $n->time_log;
			$task_documents = $n->task_documents;
			$task_pages = $n->task_pages;
		}
	}
	// no user notify? get default group settings. set all task events on
	else {
		$notify = $w->Task->getTaskGroupNotify($p['id']);
		if ($notify) {
			foreach ($notify as $n) {
				$v[$n->role][$n->type] = $n->value;
				$task_creation = 1;
				$task_details = 1;
				$task_comments = 1;
				$time_log = 1;
				$task_documents = 1;
				$task_pages = 1;
			}
		}
	}
	
	// if no user notifications and no group defaults
	// set blank form - all task events on - so user can create their user notifications
	if (!$v) {
		$v['guest']['creator'] = 0;
		$v['member']['creator'] = 0;
		$v['member']['assignee'] = 0;
		$v['owner']['creator'] = 0;
		$v['owner']['assignee'] = 0;
		$v['owner']['other'] = 0;
		$task_creation = 1;
		$task_details = 1;
		$task_comments = 1;
		$time_log = 1;
		$task_documents = 1;
		$task_pages = 1;
	}

	$f = array(array($title . " - Notifications","section"));

	// so foreach role/type lets get the values and create  checkboxes
	foreach ($v as $role => $types) {
		if ($role == strtolower($member->role)) {
			foreach ($types as $type => $value) {
				$f[] = array(ucfirst($type),"checkbox",$role."_".$type,$value);
			}
		}
	}
	
	// add Task Events to form
	$f[] = array("For which events should you receive Notification?","section");
	$f[] = array("Task Creation","checkbox","task_creation",$task_creation);
	$f[] = array("Task Details Update","checkbox","task_details",$task_details);
	$f[] = array("Comments Added","checkbox","task_comments",$task_comments);
	$f[] = array("Time Log Entry","checkbox","time_log",$time_log);
	$f[] = array("Documents Added","checkbox","task_documents",$task_documents);
	$f[] = array("Pages Added","checkbox","task_pages",$task_pages);
	
	$f = Html::form($f,$w->localUrl("/task/updateusergroupnotify/".$p['id']),"POST","Save");
	
	$w->setLayout(null);
    $w->out($f);
}

function updateusergroupnotify_POST(Web &$w) {
	$p = $w->pathMatch("id");

	// lets set some values knowing that only checked checkboxes return a value
	$arr['guest']['creator'] = $_REQUEST['guest_creator'] ? $_REQUEST['guest_creator'] : "0"; 
	$arr['member']['creator'] = $_REQUEST['member_creator'] ? $_REQUEST['member_creator'] : "0"; 
	$arr['member']['assignee'] = $_REQUEST['member_assignee'] ? $_REQUEST['member_assignee'] : "0"; 
	$arr['owner']['creator'] = $_REQUEST['owner_creator'] ? $_REQUEST['owner_creator'] : "0"; 
	$arr['owner']['assignee'] = $_REQUEST['owner_assignee'] ? $_REQUEST['owner_assignee'] : "0"; 
	$arr['owner']['other'] = $_REQUEST['owner_other'] ? $_REQUEST['owner_other'] : "0"; 

	// set task event notify values
	$task_creation = $_REQUEST['task_creation'] ? $_REQUEST['task_creation'] : "0"; 
	$task_details = $_REQUEST['task_details'] ? $_REQUEST['task_details'] : "0"; 
	$task_comments = $_REQUEST['task_comments'] ? $_REQUEST['task_comments'] : "0"; 
	$time_log = $_REQUEST['time_log'] ? $_REQUEST['time_log'] : "0"; 
	$task_documents = $_REQUEST['task_documents'] ? $_REQUEST['task_documents'] : "0"; 
	$task_pages = $_REQUEST['task_pages'] ? $_REQUEST['task_pages'] : "0"; 

	// so foreach role/type lets put the values in the database
	foreach ($arr as $role => $types) {
		foreach ($types as $type => $value) {
			// is there a record for this user > taskgroup > role > type?
			$notify = $w->Task->getTaskGroupUserNotifyType($_SESSION['user_id'],$p['id'],$role,$type);
			
			// if yes, update, if no, insert
			if ($notify) {
				$notify->value = $value;
				$notify->task_creation = $task_creation;
				$notify->task_details = $task_details;
				$notify->task_comments = $task_comments;
				$notify->time_log = $time_log;
				$notify->task_documents = $task_documents;
				$notify->task_pages = $task_pages;
				$notify->update();
				}
			else {
				$notify = new TaskGroupUserNotify($w);
				$notify->task_group_id = $p['id'];
				$notify->user_id = $_SESSION['user_id'];
				$notify->role = $role;
				$notify->type = $type;
				$notify->value = $value;
				$notify->task_creation = $task_creation;
				$notify->task_details = $task_details;
				$notify->task_comments = $task_comments;
				$notify->time_log = $time_log;
				$notify->task_documents = $task_documents;
				$notify->task_pages = $task_pages;
				$notify->insert();
			}
		}
	}
	
	// return
	$w->msg("Notifications Updated","/task/tasklist/?taskgroups=".$p['id']."&tab=2");
}

function updateusertasknotify_POST(Web &$w) {
	$p = $w->pathMatch("id");

	// set task event notify values
	$task_creation = $_REQUEST['task_creation'] ? $_REQUEST['task_creation'] : "0"; 
	$task_details = $_REQUEST['task_details'] ? $_REQUEST['task_details'] : "0"; 
	$task_comments = $_REQUEST['task_comments'] ? $_REQUEST['task_comments'] : "0"; 
	$time_log = $_REQUEST['time_log'] ? $_REQUEST['time_log'] : "0"; 
	$task_documents = $_REQUEST['task_documents'] ? $_REQUEST['task_documents'] : "0"; 
	$task_pages = $_REQUEST['task_pages'] ? $_REQUEST['task_pages'] : "0"; 
			
	// is there a record for this user > task?
	$notify = $w->Task->getTaskUserNotify($_SESSION['user_id'],$p['id']);
			
	// if yes, update, if no, insert
	if ($notify) {
		$notify->task_creation = $task_creation;
		$notify->task_details = $task_details;
		$notify->task_comments = $task_comments;
		$notify->time_log = $time_log;
		$notify->task_documents = $task_documents;
		$notify->task_pages = $task_pages;
		$notify->update();
		}
	else {
		$notify = new TaskUserNotify($w);
		$notify->task_id = $p['id'];
		$notify->user_id = $_SESSION['user_id'];
		$notify->task_creation = $task_creation;
		$notify->task_details = $task_details;
		$notify->task_comments = $task_comments;
		$notify->time_log = $time_log;
		$notify->task_documents = $task_documents;
		$notify->task_pages = $task_pages;
		$notify->insert();
		}
	
	// return
	$w->msg("Notifications Updated","/task/viewtask/".$p['id']."/?tab=5");
}
?>
