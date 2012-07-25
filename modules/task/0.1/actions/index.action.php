<?php
//////////////////////////////////////////
//			TASK DASHBOARD   			//
//////////////////////////////////////////

function index_ALL(Web $w) {
	task_navigation($w, "");

	//tab: tasks
	// get list of groups of which i am a member
	$mygroups = $w->Task->getMemberGroups($_SESSION['user_id']);
	if ($mygroups) {
		foreach ($mygroups as $mygroup) {
			$group[$mygroup->task_group_id] = $w->Task->getTaskGroupTypeById($mygroup->task_group_id);
		}
	}

	// start creating accordion user interface
	$strOut = "<div id=\"accordion\">";

	// build accordion based on all tasks allocated to groups of which i am a member
	// drilling down through group > task type > status
	// if group permissions mean 'i can view' then show count of my tasks / group tasks
	if ($group) {
		foreach ($group as $grpid => $grptype) {
			// i as arbitory value. really looking at array count
			$i = 0;
	
			// get current group title for display
			$taskgroup = $w->Task->getTaskGroup($grpid);
			$grouptitle = $taskgroup->title;
			
			// if i can create tasks in this group, provide link with group stats
			$newtasklink = "";
			if ($taskgroup->getCanICreate())
				$newtasklink = "&nbsp;&nbsp;&nbsp;<a href=\"/task/createtask/?gid=" . $grpid . "\">Create a Task</a>";
						
			$taskweek = "&nbsp;&nbsp;&nbsp;<a href=\"/task/taskweek/?taskgroup=" . $grpid . "\">Task Activity</a>";
			
			// can i view tasks in this group? if not, do not display
			$caniview = $taskgroup->getCanIView();
			
			if ($caniview) {
				// get group tasks
				$tasks = $w->Task->getTasksbyGroupId($grpid);
				// get group status list
				$statuses = $w->Task->getTaskTypeStatus($grptype);
	
				// for each task : is task open or closed? is tasks assigned to me? what is status of task?
				// build arrays accordingly to give relevant counts of respective status for each group > task type
				// create appropriate HTML to build accordion with task count tables and any required URLs
				if ($tasks) {
					foreach ($tasks as $task) {
						if (!$task->getisTaskClosed())
							$opentasks[] = $i;
						if ($task->assignee_id == $_SESSION['user_id']) {
							$mytasks[$task->task_type][$task->status][] = $i;
							if (!$task->getisTaskClosed())
								$mygrptasks[] = $i;
						}
						$alltypes[$task->task_type] = $task->getTypeTitle();
						$alltasks[$task->task_type][$task->status][] = $i;
					}
	
					// hds: our list of available statuses for current task group
					// iterate through status list looking for task of each status. count tasks.
					// foreach task type in current group, list task count per status
					$hds = array("Type");
					foreach ($alltypes as $type => $typetitle) {
						$t[] = $typetitle;
						foreach ($statuses as $stat) {
							// build URLS with query string to feed task list filter
							$msurl = "<a title=\"View your " . $stat[0] . " " . $typetitle . " Tasks\" href=\"" . $webroot . "/task/tasklist/?assignee=" . $_SESSION['user_id'] . "&taskgroups=" . $grpid . "&tasktypes=" . $type . "&status=" . $stat[0] . "\">";
							$mysurl = (count($mytasks[$type][$stat[0]]) > 0) ? $msurl : "";
							$nsurl = "<a title=\"View " . $stat[0] . " " . $typetitle . " Tasks\" href=\"" . $webroot . "/task/tasklist/?assignee=&taskgroups=" . $grpid . "&tasktypes=" . $type . "&status=" . $stat[0] . "\">";
							$nosurl = (count($alltasks[$type][$stat[0]]) > 0) ? $nsurl : "";
							$eurl = "</a>";
							$myeurl = (count($mytasks[$type][$stat[0]]) > 0) ? $eurl : "";
							$noeurl = (count($alltasks[$type][$stat[0]]) > 0) ? $eurl : "";
							$hds[$stat[0]] = $stat[0];
						
							$t[] = $mysurl.count($mytasks[$type][$stat[0]]).$myeurl . " / " . $nosurl.count($alltasks[$type][$stat[0]]).$noeurl;
						}
						$line[] = $t;
						unset($t);
						}

					// merge status heading with task count for display
					$hds = array($hds);
					$grouptasks = array_merge($hds, $line);
					$showtasks = Html::table($grouptasks,null,"tablesorter",true);
	
					// continue building accordion with stats gather for current group > task types > statuses
					$strOut .= "<div>";
					$strOut .=	"<h3><a href=\"#\">" . $grouptitle . " - " . count($mygrptasks) . "/" . count($opentasks) . " open tasks</a></h3>";
					$strOut .=	"<div>";
					$strOut .= "<span style=\"padding-left:20px;\">View the <a title=\"View all open " . $grouptitle . " Tasks\" href=\"" . $webroot . "/task/tasklist/?assignee=&taskgroups=" . $grpid . "\"><b>Group Tasks List</b></a> or click the underlined figures below to view specific tasks of specific status." . $newtasklink . $taskweek . "</span>";
					$strOut .= $showtasks;
					$strOut .=	"</div></div>";
	
					// reset all array used in counts and continue to next group or end
					unset($hds);
					unset($alltypes);
					unset($alltasks);
					unset($line);
					unset($grouptasks);
					unset($mytasks);
					unset($mygrptasks);
					unset($opentasks);
				}
			} 
		}
	}
	
	// close accordian and display
	$strOut .=	"</div>";
	$w->ctx("grouptasks",$strOut);
}
