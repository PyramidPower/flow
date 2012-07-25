<?php

//////////////////////////////////////////////////
//			REPORT DASHBOARD					//
//////////////////////////////////////////////////

function index_ALL(Web &$w) {
	report_navigation($w, "Reports");

	// report approval flag: display appropriate image
	$app[0] = "<img alt=\"No\" src=\"/img/report/no.gif\" style=\"display: block; margin-left: auto; margin-right: auto;\">";
	$app[1] = "<img alt=\"Yes\" src=\"/img/report/yes.gif\" style=\"display: block; margin-left: auto; margin-right: auto;\">";
	
	// organise criteria
	$who = $_SESSION['user_id'];
	$where = ($_REQUEST['flow_module'] != "") ? " and r.flow_module = '" . $_REQUEST['flow_module'] . "'" : "";
	$where .= ($_REQUEST['category'] != "") ? " and r.category = '" . $_REQUEST['category'] . "'" : "";
	$where .= ($_REQUEST['type'] != "") ? " and r.sqltype like '%" . $_REQUEST['type'] . "%'" : "";
	
	// get report categories from available report list
	$reports = $w->Report->getReportsbyUserWhere($who, $where);

	// set headings based on role: 'user' sees only approved reports and no approval status
	$line = ($w->auth->user()->hasRole("report_editor")  || $w->auth->user()->hasRole("report_admin")) ? array(array("Approved", "Module", "Category", "Title", "Type", "Description", "")) : array(array("Module", "Category", "Title", "Description",""));
	
	// if i am a member of a list of reports, lets display them
	if ($reports) {
		foreach ($reports as $rep) {
			$member = $w->Report->getReportMember($rep->id,$who);
			
			// editor & admin get EDIT button
//			if (($w->auth->user()->hasRole("report_editor")) || ($w->auth->user()->hasRole("report_admin"))) {
			if (($member->role == "EDITOR") || ($w->auth->user()->hasRole("report_admin"))) {
				$btnedit = Html::b($webroot."/report/viewreport/".$rep->id," Edit ");
			}
			else {
				$btnedit = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			}
			
			// admin also gets DELETE button
			if ($w->auth->user()->hasRole("report_admin")) {
				$btndelete = Html::b($webroot."/report/deletereport/".$rep->id," Delete ", "Are you sure you want to delete this Report?");
			}
			else {
				$btndelete = "";
			}
			
			// if 'report user' only list approved reports with no approval status flag
			if (($w->auth->user()->hasRole("report_user")) && (!$w->auth->user()->hasRole("report_editor")) && (!$w->auth->user()->hasRole("report_admin"))) {
				if ($rep->is_approved == "1") {
					$line[] = array(
								ucfirst($rep->flow_module),
								$rep->getCategoryTitle(),
								$rep->title,
								$rep->description,
								$btnedit . 
								"&nbsp;&nbsp;&nbsp;" . 
								Html::b($webroot."/report/runreport/".$rep->id," Execute ")
								);
				}
			}
			else {
				// if editor or admin, list all active reports of which i have membership and show approval status and buttons
				$line[] = array(
							$app[$rep->is_approved],
							ucfirst($rep->flow_module),
							$rep->getCategoryTitle(),
							$rep->title,
							ucfirst($rep->sqltype),
							$rep->description,
							$btnedit . 
							"&nbsp;&nbsp;&nbsp;" . 
							Html::b($webroot."/report/runreport/".$rep->id," Execute ") .
							"&nbsp;&nbsp;&nbsp;" .
							$btndelete,
							);
			}
		}
	}
	else {
		// i am not a member of any reports
		$line[] = array("You have no available reports","","","","","","");
	}
	// populate search dropdowns
	$flow_modules = array();
	$w->ctx("flowmodules",Html::select("flow_module",$flow_modules));
	$category = array();
	$w->ctx("category",Html::select("category",$category));
	$type = array();
	$w->ctx("type",Html::select("type",$type));
	
	// ser filter dropdown defaults
	$w->ctx("reqFlowModule",$_REQUEST['flow_module']);
	$w->ctx("reqCategory",$_REQUEST['category']);
	$w->ctx("reqType",$_REQUEST['type']);
	
	// display list of reports, if any
	$w->ctx("viewreports",Html::table($line,null,"tablesorter",true));
}

// Search Filter: load relevnt Flow Module dropdown available values
function reportAjaxListModules_ALL(Web $w) {
	$flow_modules = array();
	
	// organise criteria
	$who = $_SESSION['user_id'];
	$where = "";

	// get report categories from available report list
	$reports = $w->Report->getReportsbyUserWhere($who, $where);
	if ($reports) {
		foreach ($reports as $report) {
			if (!array_key_exists($report->flow_module, $flow_modules))
				$flow_modules[$report->flow_module] = array(ucfirst($report->flow_module),$report->flow_module);
		}
	}
	if (!$flow_modules)
		$flow_modules = array(array("No Reports",""));

	// load Flow Module dropdown and return
	$flow_modules = Html::select("flow_module",$flow_modules);
	
	$w->setLayout(null);
	$w->out(json_encode($flow_modules));
	}

	// Search Filter: selecting an Flow Module will dynamically load the Category dropdown with available values
function reportAjaxModuletoCategory_ALL(Web $w) {
	$category = array();
	$flowmodule = $_REQUEST['id'];
	
	// organise criteria
	$who = $_SESSION['user_id'];
	$where = ($_REQUEST['id'] != "") ? " and r.flow_module = '" . $_REQUEST['id'] . "'" : "";

	// get report categories from available report list
	$reports = $w->Report->getReportsbyUserWhere($who, $where);
	if ($reports) {
		foreach ($reports as $report) {
			if (!array_key_exists($report->category, $category))
				$category[$report->category] = array($report->getCategoryTitle(),$report->category);
		}
	}
	if (!$category)
		$category = array(array("No Reports",""));

	// load Category dropdown and return
	$category = Html::select("category",$category);
	
	$w->setLayout(null);
	$w->out(json_encode($category));
	}

// Search Filter: selecting an Category will dynamically load the Type dropdown with available values
function reportAjaxCategorytoType_ALL(Web $w) {
	$type = array();

	list($category, $flowmodule) = preg_split('/_/',$_REQUEST['id']);
	
	// organise criteria
	$who = $_SESSION['user_id'];
	$where = ($flowmodule != "") ? " and r.flow_module = '" . $flowmodule . "'" : "";
	$where .= ($category != "") ? " and r.category = '" . $category . "'" : "";
	
	// get report categories from available report list
	$reports = $w->Report->getReportsbyUserWhere($who, $where);
	if ($reports) {
		foreach ($reports as $report) {
			$arrtype = preg_split("/,/", $report->sqltype);
			foreach ($arrtype as $rtype) {
				$rtype = trim($rtype);
				if (!array_key_exists(strtolower($rtype), $type))
					$type[strtolower($rtype)] = array(strtolower($rtype),strtolower($rtype));
			}
		}
	}
	if (!$type)
		$type = array(array("No Reports",""));

	// load Type dropdown and return
	$type = Html::select("type",$type);
	
	$w->setLayout(null);
	$w->out(json_encode($type));
	}
	
//////////////////////////////////////////////////
//			CREATE REPORT						//
//////////////////////////////////////////////////

function createreport_ALL(Web &$w) {
	report_navigation($w, "Create a Report");
	
	// get list of flow modules
	$flow_modules = $w->Report->getFlowModules();
	
	// build form to create a report. display to users by role is controlled by the template
	// using lookup with type ReportCategory for category listing
	$f = Html::form(array(
	array("Create a New Report","section"),
	array("Title","text","title", $_REQUEST['title']),
	array("Flow Module","select","flow_module", $_REQUEST['flow_module'], $flow_modules),
	array("Category","select","category", $_REQUEST['category'], lookupForSelect($w, "ReportCategory")),
	array("Description","textarea","description",$_REQUEST['description'],"100","2"),
	array("Code","textarea","report_code",$_REQUEST['report_code'],"100","22"),
	),$w->localUrl("/report/savereport/"),"POST"," Save Report ");
	
	$t = Html::form(array(
	array("Special Parameters","section"),
	array("User","static","user","{{current_user_id}}"),
	array("Roles","static","roles","{{roles}}"),
	array("Site URL","static","webroot","{{webroot}}"),
	array("View Database","section"),
	array("Tables","select","dbtables",null,$w->Report->getAllDBTables()),
	array(" ","static","dbfields","<span id=dbfields></span>")
	));		
	$w->ctx("dbform",$t);

	// display form
	$w->ctx("createreport",$f);
}

// save newly created form
function savereport_POST(Web &$w) {
	report_navigation($w, "Create Report");

	// get type of statement: select/insert/update/delete
	$_POST['sqltype'] = $w->Report->getSQLStatementType($_POST['report_code']);
	
	// insert report into database
	$report = new Report($w);
	$report->fill($_POST);
	$report->insert();
	
	// if insert successful, make creator a MEMBER of this report
	if ($report->id) {
		$arr['report_id'] = $report->id;
		$arr['user_id'] = $_SESSION['user_id']; 

		$mem = new ReportMember($w);
		$mem->fill($arr);
		$mem->insert();
	}
	$w->msg("Report created","/report/index/");
}

// Create Report: show fields in selected table to assist in Report creation
function taskAjaxSelectbyTable_ALL(Web $w) {
	$tbl = $_REQUEST['id'];
	
	// create dropdowns loaded with respective data
	$dbfields = $w->Report->getFieldsinTable($tbl);

	$w->setLayout(null);
	$w->out(json_encode($dbfields));
}

//////////////////////////////////////////////////
//			VIEW REPORT							//
//////////////////////////////////////////////////

function viewreport_GET(Web &$w) {
	report_navigation($w, "Edit Report");
	$p = $w->pathMatch("id");

	// tab: view report
	// if there is a report ID in the URL ...
	if ($p['id']) {
		// get member
		$member = $w->Report->getReportMember($p['id'],$_SESSION['user_id']);
		
		// get the relevant report
		$rep = $w->Report->getReportInfo($p['id']);

		// if the report exists check status & role before displaying
		if ($rep) {
			// if ($w->auth->user()->hasRole("report_user") && (!$w->auth->user()->hasRole("report_editor")) && (!$w->auth->user()->hasRole("report_admin"))) {
			if (($member->role != "EDITOR") && (!$w->auth->user()->hasRole("report_admin"))) {
				// redirect the unauthorised
				$w->msg("Sorry, you are not authorised to edit this Report","/report/index/");
			}
			elseif ($w->auth->user()->hasRole("report_admin")) {
				// build the report for edit WITH an Approval checkbox
				// using lookup with type ReportCategory for category listing
				
				// get list of flow modules
				$flow_modules = $w->Report->getFlowModules();
								
				$f = Html::form(array(
					array("Approve a New Report","section"),
					array("Title","text","title", $rep->title),
					array("Flow Module","select","flow_module", $rep->flow_module, $flow_modules),
					array("Category","select","category", $rep->category, lookupForSelect($w, "ReportCategory")),
					array("Description","textarea","description",$rep->description,"100","2"),
					array("Code","textarea","report_code",$rep->report_code,"100","22"),
					array("Approved","checkbox","is_approved", $rep->is_approved),
					),$w->localUrl("/report/editreport/".$rep->id),"POST"," Update Report ");
				
					// provide a button by which the report may be tested, ie. executed
					$btntestreport = Html::b($webroot."/report/runreport/".$rep->id," Test the Report ");
					$w->ctx("btntestreport",$btntestreport);
					
					// create form providing view of tables and fields
					$t = Html::form(array(
					array("Special Parameters","section"),
					array("User","static","user","{{current_user_id}}"),
					array("Roles","static","roles","{{roles}}"),
					array("Site URL","static","webroot","{{webroot}}"),
					array("View Database","section"),
					array("Tables","select","dbtables",null,$w->Report->getAllDBTables()),
					array("Fields","static","dbfields","<span id=dbfields></span>")
					));
					$w->ctx("dbform",$t);
				}
//			elseif ($w->auth->user()->hasRole("report_editor")) {
			elseif ($member->role == "EDITOR") {
				// build the report for edit. edited forms again require approval
				// using lookup with type ReportCategory for category listing

				// get list of flow modules
				$flow_modules = $w->Report->getFlowModules();
				
				$f = Html::form(array(
					array("Create a New Report","section"),
					array("Title","text","title", $rep->title),
					array("Flow Module","select","flow_module", $rep->flow_module, $flow_modules),
					array("Category","select","category", $rep->category, lookupForSelect($w, "ReportCategory")),
					array("Description","textarea","description",$rep->description,"100","2"),
					array("Code","textarea","report_code",$rep->report_code,"100","22"),
					array("","hidden","is_approved", "0"),
					),$w->localUrl("/report/editreport/".$rep->id),"POST"," Update Report ");

					// create form providing view of tables and fields
					$t = Html::form(array(
					array("Special Parameters","section"),
					array("Logged in User","static","user","{{current_user_id}}"),
					array("User Roles","static","roles","{{roles}}"),
					array("Site URL","static","webroot","{{webroot}}"),
					array("View Database","section"),
					array("Tables","select","dbtables",null,$w->Report->getAllDBTables()),
					array("Fields","static","dbfields","<span id=dbfields></span>")
					));
					$w->ctx("dbform",$t);
			}
			else {
				// redirect on all other occassions
				$w->msg($rep->title . ": Report has yet to be approved","/report/index/");
			}
		}
		else {
			$f = "Report does not exist";
		}
	}
	else {
		$f = "Report does not exist";
	}
	// return the form for display and edit
	$w->ctx("viewreport",$f);
    
	$btnrun = Html::b($webroot."/report/runreport/".$rep->id, " Execute Report ");
    $w->ctx("btnrun",$btnrun);
	

	// tab: view members
	// see report.lib.php
	report_viewMemberstab($w, $p['id']);
} 

//////////////////////////////////////////////////
//			EDIT REPORT							//
//////////////////////////////////////////////////

function editreport_POST(Web $w) {
  	$p = $w->pathMatch("id");
  	
  	if (!array_key_exists("is_approved",$_REQUEST))
  		$_REQUEST['is_approved'] = 0;
  		
  	// if there is a report ID in the URL ...
  	if ($p['id']) {
  		// get report details
	  	$rep = $w->Report->getReportInfo($p['id']);

	  	// if report exists, update it
	  	if ($rep) {
			$_POST['sqltype'] = $w->Report->getSQLStatementType($_POST['report_code']);
			
	  		$rep->fill($_POST);
	        $rep->update();
	        $repmsg = "Report updated.";

	        // check if there is a feed associated with this report
	        $feed = $w->Report->getFeedInfobyReportId($rep->id);
			if ($feed) {
				// if feed exists, need to reevaluate the URL in case of changes in the report parameters
				$elements = $rep->getReportCriteria();

				if ($elements) {
					foreach ($elements as $element) {
						if (($element[0] != "Description") && ($element[2] != ""))
						$query .= $element[2] . "=&lt;value&gt;&";
					}
				}
		
				$query = rtrim($query,"&");

				// use existing key to reevaluate feed URL
				$feedurl = $w->localUrl("/report/feed/?key=" . $feed->key . "&" . $query);

				// update feed URL
				$feed->url = $feedurl;
				$feed->update();
			}
	  	}
	    else {
	    	$repmsg = "Report does not exist";
	    }
	}
	else {
		$repmsg = "Report does not exist";
	}
	
    // return
    $w->msg($repmsg,"/report/viewreport/".$rep->id);
} 

//////////////////////////////////////////////////
//			DELETE REPORT						//
//////////////////////////////////////////////////

function deletereport_ALL(Web &$w) {
	$p = $w->pathMatch("id");

	// if there is  report ID in the URL ...
	if ($p['id']) {
		// get report details
		$rep = $w->Report->getReportInfo($p['id']);
		
		// if report exists, delete
		if ($rep) {
			$rep->is_deleted = 1;
			$rep->update();

			// need to check if there is a feed associated with this report
			$feed = $w->Report->getFeedInfobyReportId($rep->id);
			
			// if feed exists, set is_deleted flag. ie. delete feed as well as report
			if ($feed) {
				$feed->is_deleted = 1;
				$feed->update();
			}
			// return
			$w->msg("Report deleted","/report/index/");
		}
		// if no report, say as much
		else {
			$w->msg("Report no longer exists?","/report/index/");
		}
	}
}

//////////////////////////////////////////////////
//			EXECUTE REPORT						//
//////////////////////////////////////////////////

// display the form allowing users to set report parameters
function runreport_ALL(Web &$w) {
	report_navigation($w, "Generate Report");
	$p = $w->pathMatch("id");
	
	// if there is a report ID in the URL ...
	if ($p['id']) {
		// get member
		$member = $w->Report->getReportMember($p['id'],$_SESSION['user_id']);
		
		// get the relevant report
		$rep = $w->Report->getReportInfo($p['id']);

		// if report exists, first check status and user role before displaying
		if ($rep) {
			if (($rep->is_approved == "0") && ($member->role != "EDITOR") && (!$w->auth->user()->hasRole("report_admin"))) {
				$w->msg($rep->title . ": Report is yet to be approved","/report/index/");
			}
			else {
				// display form
				report_navigation($w, $rep->title);
				
				if (($member->role == "EDITOR") || ($w->auth->user()->hasRole("report_admin"))) {
					$btnedit = Html::b($webroot."/report/viewreport/".$rep->id," Edit Report ");
				}
				else {
					$btnedit = "";
				}

				// get the form array
				$form = $rep->getReportCriteria();

				// if there is a form display it, otherwise say as much
				if ($form) {
					$theform = Html::form($form,$w->localUrl("/report/exereport/".$rep->id),"POST"," Display Report ");
				}
				else {
					$theform = "No search criteria?";
				}

				// display
				$w->ctx("btnedit",$btnedit);
				$w->ctx("report",$theform);
			}
		}
	}
}

// criteria/parameter form is submited and report is executed
function exereport_ALL(Web &$w) {
	report_navigation($w, "Generate Report");
	$p = $w->pathMatch("id");

	$arrreq = array();
	// prepare export buttons for display if format = html
	foreach ($_POST as $name => $value) {
		$arrreq[] = $name ."=" . urlencode($value);
	}
	
	$viewurl = $webroot . "/report/viewreport/" . $p['id'];
	$runurl = $webroot . "/report/runreport/" . $p['id'] . "/?" . implode("&",$arrreq);
	$repurl = $webroot . "/report/exereport/" . $p['id'] . "?";
	$strREQ = $arrreq ? implode("&",$arrreq) : "";
	$urlcsv = $repurl . $strREQ . "&format=csv";
	$urlpdf = $repurl . $strREQ . "&format=pdf";
	$urlxml = $repurl . $strREQ . "&format=xml";
	$btncsv = Html::b($urlcsv," Export as CSV ");
	$btnpdf = Html::b($urlpdf," Export as PDF ");
	$btnxml = Html::b($urlxml," Export as XML ");
	$btnrun = Html::b($runurl," Edit Report Parameters ");
	$btnview = Html::b($viewurl," Edit Report ");
	
	// if there is a report ID in the URL ...
	if ($p['id']) {
		// get member
		$member = $w->Report->getReportMember($p['id'],$_SESSION['user_id']);
		
		// get the relevant report
		$rep = $w->Report->getReportInfo($p['id']);

		// if report exists, execute it
		if ($rep) {
			report_navigation($w, $rep->title);
			
			// prepare and execute the report
			$tbl = $rep->getReportData();

			// if we have an empty return, say as much
			if (!$tbl) {
				$w->error("No Data found for selections. Please try again....","/report/runreport/".$rep->id);
			}
			// if an ERROR is returned, say as much
			elseif ($tbl[0][0] == "ERROR") {
				$w->error($tbl[1][0],"/report/runreport/".$rep->id);
			}
			// if we have records, present them in the requested format
			else {
				// as a cvs file for download
				if ($_REQUEST['format'] == "csv") {
					$w->setLayout(null);
					$w->Report->exportcsv($tbl, $rep->title);
				}
				// as a PDF file for download
				elseif ($_REQUEST['format'] == "pdf") {
					$w->setLayout(null);
					$w->Report->exportpdf($tbl, $rep->title);
				}
				// as XML document for download
				elseif ($_REQUEST['format'] == "xml") {
					$w->setLayout(null);
					$w->Report->exportxml($tbl, $rep->title);
				}
				// default to a web page
				else {
					// allowing multiple SQL statements, each returns a recordset as a seperate array element, ie. iterate
					// array: report parameters > report title > data columns > recordset
					foreach ($tbl as $t) {
						$crumbs = array_shift($t);
						$title = array_shift($t);
						
						if ($crumbs) {
							foreach ($crumbs as $crumb) {
								$strcrumb .= $crumb . ", ";
							}
						$strcrumb = "<b>Form Parameters</b>: " . rtrim($strcrumb, ", ");
						}

						// first row is our column headings
						$hds[] = array_shift($t);
						// first row has column names as associative. change keys to numeric to match recordset
						$tvalues = array_values($hds[0]);

						// find key of any links
						foreach ($tvalues as $h) {
							if (stripos($h,"_link")) {
								list($fld,$lnk) = preg_split("/_/", $h);
								$f = array_search($fld."_link",$tvalues);
								$ukey[$f] = $fld;
								unset($hds[0][$h]);
							}
						}

						if ($ukey) {
							// now need to find key of fields to link
							foreach ($tvalues as $m => $h) {
								foreach ($ukey as $n => $u) {
									if ($u == $h)
										$fkey[$n] = array_search($u,$tvalues);
								}
							}

							// iterate row to create link and dump URL related fields
							foreach ($t as $v) {
								// keys points to fields so need to maintain array and create all URLS
								// before we start dumping fields and splicing links
								foreach ($fkey as $n => $u) {
									$a[$n] = "<a href=\"" . $v[$n] . "\">" . $v[$u] . "</a>";
									$dump[] = $n;
									$dump[] = $u;
								}

								
								// dump url related fields
								foreach ($dump as $num) {
									unset($v[$num]);
								}
								
								// add completed URL(s)
								foreach ($a as $num => $url) {
									$v[$num] = $url;
								}

								// we now have gaps from our unsetting and inserting of links
								// eg. $v[3], $v[4], $v[6], $v[0]
								// get array_keys into new array: 
								$sortv = array_keys($v);
								// sort so keys are now in order dispite the gaps
								sort($sortv);
								// create new - ordered - array setting our original array values
								foreach ($sortv as $num => $val) {
									$sorted[] = $v[$val];
								}
								
								$arr[] = $sorted;								
								unset($a);
								unset($dump);
								unset($sorted);
							}
							// recreate $t
							$t = $arr;
							unset($ukey);
							unset($fkey);
						}
						// put headings back into array
						$t = array_merge($hds,$t);
						// build results table
						$results .= "<b>" . $title . "</b><p>" . $strcrumb . "<p>" . Html::table($t,null,"tablesorter",true) . "<p>";
						// reset parameters string
						$strcrumb = "";
						unset($hds);
						unset($arr);
					}

					// display export and function buttons
					$w->ctx("exportxml",$btnxml);
					$w->ctx("exportcsv",$btncsv);
					$w->ctx("exportpdf",$btnpdf);
					$w->ctx("btnrun",$btnrun);
					$w->ctx("showreport",$results);

					// allow editor/admin to edit the report
					if (($member->role == "EDITOR") || ($w->auth->user()->hasRole("report_admin"))) {
						$w->ctx("btnview",$btnview);
					}
				}
			}
		}
		else {
			// report does not exist?
			$w->ctx("showreport","No such report?");
		}
	}	
}


//////////////////////////////////////////////////
//			MANAGE REPORT MEMBERS				//
//////////////////////////////////////////////////

// provide form by which to add members to a report
function addmembers_GET(Web &$w) {
	$p = $w->pathMatch("id");

	// get the list of report editors and admins
	$members1 = $w->Auth->getUsersForRole("report_editor");
	$members2 = $w->Auth->getUsersForRole("report_user");
	// merge into single array
	$members12 = array_merge($members1, $members2);

	// strip the dumplicates. dealing with an object so no quick solution
	$members = array();
	foreach ($members12 as $member) {
	    if (!in_array($member, $members)) {
        	$members[] = $member;
		    }
		}

	// build form
	$addUserForm['Add Members'] = array(
	array(array("","hidden", "report_id",$p['id'])),
	array(array("As Role","select","role","",$w->Report->getReportPermissions())),
	array(array("Add Members","multiSelect","member",null,$members)));

	$w->setLayout(null);
	$w->ctx("addmembers",Html::multiColForm($addUserForm,$w->localUrl("/report/updatemembers/"),"POST"," Submit "));
}

// edit a member
function editmember_GET(Web &$w) {
	$p = $w->pathMatch("repid","userid");
	// get member details for edit
	$member = $w->Report->getReportMember($p['repid'], $p['userid']);

	// build editable form for a member allowing change of membership type
	$f = Html::form(array(
	array("Member Details","section"),
	array("","hidden", "report_id",$p['repid']),
	array("Name","static", "name", $w->Report->getUserById($member->user_id)),
	array("Role","select","role",$member->role,$w->Report->getReportPermissions())
	),$w->localUrl("/report/editmember/".$p['userid']),"POST"," Update ");

	// display form
    $w->setLayout(null);
	$w->ctx("editmember",$f);
	}
	
function editmember_POST(Web &$w) {
	$p = $w->pathMatch("id");
	$member = $w->Report->getReportMember($_POST['report_id'], $p['id']);
	
	$member->fill($_REQUEST);
	$member->update();

	$w->msg("Member updated","/report/viewreport/".$_POST['report_id']."?tab=2");
	}
	
// add members to a report
function updatemembers_POST(Web &$w) {
	$arrdb = array();
	$arrdb['report_id'] = $_REQUEST['report_id'];
	$arrdb['role'] = $_REQUEST['role'];
	$arrdb['is_deleted'] = 0;

	// for each selected member, complete population of input array
	foreach ($_REQUEST['member'] as $member) {
		$arrdb['user_id'] = $member;
		// find member against report ID
		$mem = $w->Report->getReportMember($arrdb['report_id'], $arrdb['user_id']);
		
		// if no membership, create it, otherwise update and continue
		if (!$mem) {
			$mem = new ReportMember($w);
			$mem->fill($arrdb);
			$mem->insert();
			}
		else {
			$mem->fill($arrdb);
			$mem->update();				
			}

		// prepare input array for next selected member to insert
		unset($arrdb['user_id']);
	}
	// return
	$w->msg("Member Group updated","/report/viewreport/".$arrdb['report_id']."?tab=2");
}

function deletemember_GET(Web &$w) {
	$p = $w->pathMatch("report_id","user_id");
	
	// get details of member to be deleted
	$member = $w->Report->getReportMember($p['report_id'],$p['user_id']);

	if ($member) {
		// build a static form displaying members details for confirmation of delete
		$f = Html::form(array(
		array("Confirm Delete Member","section"),
		array("","hidden", "is_deleted","1"),
		array("Name","static", "name", $w->Report->getUserById($member->user_id)),
		),$w->localUrl("/report/deletemember/".$member->report_id."/".$member->user_id),"POST"," Delete ");
	}
	else {
		$f = "No such member?";
	}
	// display form
    $w->setLayout(null);
	$w->ctx("deletemember",$f);
	}

function deletemember_POST(Web &$w) {
	$p = $w->pathMatch("report_id","user_id");
	// get the details of the person to delete 
	$member = $w->Report->getReportMember($p['report_id'],$p['user_id']);
	$_POST['id'] = $member->id;

	// if member exists, delete them
	if ($member) {
		$member->fill($_POST);
		$member->update();

		$w->msg("Member deleted","/report/viewreport/".$p['report_id']."?tab=2");
	}
	else {
		// if member somehow no longer exists, say as much
		$w->msg("Member no longer exists?","/report/viewreport/".$p['report_id']."?tab=2");
	}
}

//////////////////////////////////////////////////
//			FEEDS								//
//////////////////////////////////////////////////

// receive, process and deliver feeds
function feed_ALL(Web &$w) {
	// check for feed key in request
	if (array_key_exists("key",$_REQUEST)) {
		// get feed
		$feed = $w->Report->getFeedInfobyKey($_REQUEST["key"]);
		
		// if feed, then get respective report details
		if ($feed) {
			$rep = $w->Report->getReportInfo($feed->report_id);
			
			// if report exists, execute it
			if ($rep) {
				report_navigation($w, $rep->title);

				// prepare and execute the report
				$tbl = $rep->getReportData();
				
				// if we have an empty return, say as much
				if (!$tbl) {
					// return error status?
				}
				// if an ERROR is returned, say as much
				elseif ($tbl[0][0] == "ERROR") {
					// return error status?
					$w->ctx("showreport","error");
				}
				// if a SUCCESSFUL insert/update/delete is returned, say as much
				elseif ($tbl[0][0] == "SUCCESS") {
					// return error status?
					$w->ctx("showreport","success");
				}
				// if we have records, present them in the requested format
				else {
					// as a cvs file for download
					if ($_REQUEST['format'] == "csv") {
						$w->Report->exportcsv($tbl, $rep->title);
					}
					// as a PDF file for download
					elseif ($_REQUEST['format'] == "pdf") {
						$w->Report->exportpdf($tbl, $rep->title);
					}
					// as XML document for download
					elseif ($_REQUEST['format'] == "xml") {
						$w->Report->exportxml($tbl, $rep->title);
					}
					// if confused, display a web page in the usual manner
					else {
						foreach ($tbl as $t) {
							$crumbs = array_shift($t);
							$title = array_shift($t);
							$results .= "<b>" . $title . "</b><p>" . Html::table($t,null,"tablesorter",true);
						}
						$w->ctx("showreport",$results);
					}
				}
			}
		}
	}
}

// list available feeds in the feed dashboard
function listfeed_ALL(Web &$w) {
	report_navigation($w, "Feeds");

	// get all feeds
	$feeds = $w->Report->getFeeds();

	// prepare table headings
	$line = array(array("Feed","Report","Description","Created",""));
	
	// if feeds exists and i am suitably authorised, list them
	if (($feeds) && ($w->auth->user()->hasRole("report_editor") || $w->auth->user()->hasRole("report_admin"))) {
		foreach ($feeds as $feed) {
			// get report data
			$rep = $w->Report->getReportInfo($feed->report_id);

			// display the details
			if ($rep) {
				$line[] = array(
					$feed->title,
					$rep->title,
					$feed->description,
					formatDateTime($feed->dt_created),
					Html::b($webroot."/report/editfeed/".$feed->id, " View ") .
					"&nbsp;&nbsp;&nbsp;" .
					Html::b($webroot."/report/deletefeed/".$feed->id, " Delete ", "Are you sure you wish to DELETE this feed?")
					);
			}
		}
	}
	else {
		// no feeds and/or no access
		$line[] = array("No feeds to list","","","","");
	}
	
	// display results
	$w->ctx("feedlist",Html::table($line,null,"tablesorter",true));
}

function createfeed_GET(Web &$w) {
	report_navigation($w, "Create a Feed");
	
	// get list of reports for logged in user. sort to list unapproved reports first
	$reports = $w->Report->getReportsbyUserId($_SESSION['user_id']);

	// if i am a member of a list of reports, lets display them
	if (($reports) && ($w->auth->user()->hasRole("report_editor")  || $w->auth->user()->hasRole("report_admin"))) {
		foreach ($reports as $report) {
			// get report data
			$rep = $w->Report->getReportInfo($report->report_id);
			$myrep[] = array($rep->title, $rep->id);
		}
	}
	
	$f = Html::form(array(
	array("Create a Feed from a Report","section"),
	array("Select Report","select","rid",null,$myrep),
	array("Feed Title","text","title"),
	array("Description","textarea","description",null,"40","6"),
	),$w->localUrl("/report/createfeed/"),"POST"," Save ");
	
	$w->ctx("createfeed",$f);
}

function createfeed_POST(Web &$w) {
	report_navigation($w, "Create a Feed");
	
	// create a new feed
	$feed = new ReportFeed($w);

	$arr["report_id"] = $_REQUEST["rid"];
	$arr["title"] = $_REQUEST["title"];
	$arr["description"] = $_REQUEST["description"];
	$arr["dt_created"] = date("d/m/Y");
	$arr["is_deleted"] = 0;
	
	$feed->fill($arr);
	$feed->insert();

	$rep = $w->Report->getReportInfo($feed->report_id);

	// if report exists
	if ($rep) {
		// get the form array
		$elements = $rep->getReportCriteria();

		if ($elements) {
			foreach ($elements as $element) {
				if (($element[0] != "Description") && ($element[2] != ""))
				$query .= $element[2] . "=&lt;value&gt;&";
			}
		}
		
		$query = rtrim($query,"&");
		
		$feedurl = $w->localUrl("/report/feed/?key=" . $feed->key . "&" . $query);

		$feed->url = $feedurl;
		$feed->update();
		
		$feedurl = "<b>Your Feed has been created</b><p>Use the URL below, with actual query parameter values, to access this report feed.<p>" . $feedurl;
		$w->ctx("feedurl", $feedurl);
	}
}

function editfeed_GET(Web &$w) {
	report_navigation($w, "Edit Feed");
	
	$p = $w->pathMatch("id");
	
	$feed = $w->Report->getFeedInfobyId($p["id"]);
	
	// get list of reports for logged in user. sort to list unapproved reports first
	$reports = $w->Report->getReportsbyUserId($_SESSION['user_id']);

	// if i am a member of a list of reports, lets display them
	if (($reports) && ($w->auth->user()->hasRole("report_editor")  || $w->auth->user()->hasRole("report_admin"))) {
		foreach ($reports as $report) {
			// get report data
			$rep = $w->Report->getReportInfo($report->report_id);
			$myrep[] = array($rep->title, $rep->id);
		}
	}
	
	$note = "Available Formats: html, csv, pdf, xml<br>";
	$note .= "Date Formats must be <b>d/m/Y</b> to mimic date picker";
	
	$f = Html::form(array(
	array("Create a Feed from a Report","section"),
	array("Select Report","select","rid",$feed->report_id,$myrep),
	array("Feed Title","text","title",$feed->title),
	array("Description","textarea","description",$feed->description,"40","6"),
	array("Feed URL","static","url", $feed->url),
	array("Note","static","url", $note),
	),$w->localUrl("/report/editfeed/".$feed->id),"POST"," Update ");
	
	$w->ctx("editfeed",$f);
}

function editfeed_POST(Web &$w) {
	report_navigation($w, "Create a Feed");
	
	$p = $w->pathMatch("id");
	
	$feed = $w->Report->getFeedInfobyId($p["id"]);

	$arr["report_id"] = $_REQUEST["rid"];
	$arr["title"] = $_REQUEST["title"];
	$arr["description"] = $_REQUEST["description"];
	
	$feed->fill($arr);
	$feed->update();

	$w->msg("Feed " . $feed->title . " has been updated","/report/listfeed/");
}

function deletefeed_ALL(Web &$w) {
	$p = $w->pathMatch("id");
	
	$feed = $w->Report->getFeedInfobyId($p["id"]);

	$arr["is_deleted"] = 1;
	
	$feed->fill($arr);
	$feed->update();

	$w->msg("Feed " . $feed->title . " has been deleted","/report/listfeed/");
}

// when creating a feed, display the details of a report when it is selected as the feed input
function feedAjaxGetReportText_ALL(Web $w) {
	// get the relevant report
	$rep = $w->Report->getReportInfo($_REQUEST["id"]);
	
	if ($rep) {
       $feedtext = "<table border=0 class=form>" . 
		           "<tr><td class=section colspan=2>Report</td></tr>" . 
				   "<tr><td><b>Title</td><td>" . $rep->title . "</td></tr>" . 
				   "<tr><td><b>Description</b></td><td>" . $rep->description . "</td></tr>" . 
				   "</table><p>";
	}
	else {
		$feedtext = "";
	}

	$w->setLayout(null);
	$w->out(json_encode($feedtext));
}

//////////////////////////////////////////////////
//			MAIL MERGE							//
//////////////////////////////////////////////////

// list available feeds in the feed dashboard
function listmailmerge_ALL(Web &$w) {
	report_navigation($w, "Mail Merge");

	// get all feeds
	$mmlist = $w->Report->getMailMerges();

	// prepare table headings
	$line = array(array("Mail Merge","Report","Description","Created",""));
	
	// if feeds exists and i am suitably authorised, list them
	if (($mmlist) && ($w->auth->user()->hasRole("report_editor") || $w->auth->user()->hasRole("report_admin"))) {
		foreach ($mmlist as $mm) {
			// get report data
			$rep = $w->Report->getReportInfo($mm->report_id);

			// display the details
			if ($rep) {
				$line[] = array(
					$mm->title,
					$rep->title,
					$mm->description,
					formatDateTime($mm->dt_created),
					Html::b($webroot."/report/editmailmerge/".$mm->id, " View ") .
					"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
					Html::b($webroot."/report/viewrun/".$mm->id, " View Runs ") .
					"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
					Html::b($webroot."/report/createrun/".$mm->id, " Create Run ") . 
					"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" .
					Html::b($webroot."/report/deletemailmerge/".$mm->id, " Delete ", "Are you sure you wish to DELETE this mail merge?")
					);
			}
		}
	}
	else {
		// no feeds and/or no access
		$line[] = array("No mail merge to list","","","","");
	}
	
	// display results
	$w->ctx("mmlist",Html::table($line,null,"tablesorter",true));
}

function createrun_GET(Web &$w) {
	report_navigation($w, "Create a Mail Merge Run");
	
	$p = $w->pathMatch("id");

	// get mail merge
	$mm = $w->Report->getMailMergeInfobyId($p["id"]);
	
	// get report
	$rep = $w->Report->getReportInfo($mm->report_id);
	
	$hours = array("0","1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20","21","22","23","24");
	$mins = array("00","15","30","45");
	
	$s = date("d/m/Y");
	$sh = date("G");
	$sm = ceil(date("i") * (1/15)) / (1/15);
	if ($sm == 60) {
		$hm = date("d/m/Y:G:i", mktime(date("G")+1, 0, 0, date("m") , date("d"), date("Y")));
		list($d,$h,$m) = preg_split("/:/",$hm);
		$s = $d;
		$sh = $h;
		$sm = $m;
	}
			
	$form["Mail Merge - Check details below"] = array(
			array(array("Mail Merge - Check details below","section")),
			array(array("Report","static","report_id",$rep->title)),
			array(array("Title","static","title", $mm->title)),
			array(array("Description","static","description",str_replace("\n","<br>",$mm->description))),
			);
	$form["Mail Template"] = array(
			array(array("To","static","to",$mm->to)),
			array(array("From Name","static","fromname",$mm->fromname)),
			array(array("From Email","static","fromemail",$mm->fromemail)),
			array(array("CC","static","cc",$mm->cc)),
			array(array("bcc","static","bcc",$mm->bcc)),
			array(array("Subject","static","subject",$mm->subject)),
			array(array("Mail Template","static","mmbody",$mm->mmbody)),
			);
	$form["Scheduled Run Time"] = array(
			array(array("Date","date","dt_start", $s),array("Hour","select","start_hour",$sh,$hours),array("Min","select","start_minute",$sm,$mins)),
			);
			
	// return form
	$form = Html::multiColForm($form,$w->localUrl("/report/createrun/".$mm->id,"POST"," Create Run "));
	$w->ctx("runmm",$form);
}

function createrun_POST(Web &$w) {
	report_navigation($w, "Create a Mail Merge Run");
	
	$p = $w->pathMatch("id");

	// get mail merge
	$mm = $w->Report->getMailMergeInfobyId($p["id"]);

	$rep = $w->Report->getReportInfo($mm->report_id);

	// prepare and execute the report
	$tbl = $rep->getReportData();

	// if we have an empty return, say as much
	if (!$tbl) {
		$w->error("No Data found for report selections .....","/report/createrun/".$mm->id);
	}
	// if an ERROR is returned, say as much
	elseif ($tbl[0][0] == "ERROR") {
		$w->error($tbl[1][0],"/report/createrun/".$mm->id);
	}
	// if we have records ....
	else {
		// make schedule date.time
		$_REQUEST["dt_start"] = ($_REQUEST["dt_start"] != "") ? $_REQUEST["dt_start"] : date("d/m/Y"); 
	  	$_REQUEST["start_hour"] = ($_REQUEST["start_hour"] != "") ? $_REQUEST["start_hour"] : date("H"); 
  		$_REQUEST["start_minute"] = ($_REQUEST["start_minute"] != "") ? $_REQUEST["start_minute"] : (ceil(date("i") * (1/15)) / (1/15)); 
		$schedule = $w->Task->date2db($_REQUEST["dt_start"]) . " " . $_REQUEST["start_hour"] . ":" . $_REQUEST["start_minute"];
  		
		$mmrun = new ReportMailMergeRun($w);
		$mmrun->mailmerge_id = $mm->id;
		$mmrun->to = $mm->to;
		$mmrun->fromname = $mm->fromname;
		$mmrun->fromemail = $mm->fromemail;
		$mmrun->cc = $mm->cc;
		$mmrun->bcc = $mm->bcc;
		$mmrun->subject = $mm->subject;
		$mmrun->dt_created = date("Y-m-d G:i");
		$mmrun->dt_schedule = $schedule;
		$mmrun->insert();

		if ($mmrun->id) {
			foreach ($tbl as $t) {
				// throw away the stuff we dont need
				// we only need the recordset
				$crumbs = array_shift($t);
				$title = array_shift($t);
				$hds = array_shift($t);
				$hds = array_values($hds);
			
				$mmbody = $mm->mmbody;

				foreach ($t as $row) {
					if (startsWith($mm->to,'{{')){
						$emailfield = str_replace(array("{","}"), "", $mm->to);
					} else {
						$toemail =$mm->to;
					}


					foreach ($hds as $n => $h) {
						$mmbody = str_replace("{{".$h."}}",$row[$n], $mmbody);
						if ($h == $emailfield) {
							$toemail = $row[$n];
						}
					}
					
					$mmrec = new ReportMailMergeRecipient($w);
					$mmrec->mailmergerun_id = $mmrun->id;

					$mmrec->recipient_email = $toemail;
					$mmrec->mmbody = $mmbody;
					$mmrec->insert();
				}
			}
		}
	}
	$w->msg("Run has been created","/report/viewrun/".$mm->id);
	
}

function viewrun_ALL(Web &$w) {
	$p = $w->pathMatch("id");

	// get mail merge
	$mm = $w->Report->getMailMergeInfobyId($p["id"]);
	
	report_navigation($w, "Mail Merge Runs: " . $mm->title);
	
	$runs = $w->Report->getMailMergeRuns($mm->id);
	usort($runs,array("ReportService","sortbySchedule"));
	
	$line = array(array("Recipients","Sent","From","Subject","Schedule",""));
	
	if ($runs) {
		foreach ($runs as $run) {
			$recipients = $w->Report->getRunRecipientCount($run->id);
			$where = " and is_sent <> ''";
			$sent = $w->Report->getRunRecipientCount($run->id, $where);

			$line[] = array(
					$recipients[0][cnt],
					$sent[0][cnt],
					$run->fromname . " &lt;" . $run->fromemail . "&gt;",
					$run->subject,
					formatDateTime($run->dt_schedule),
					Html::b($webroot."/report/viewrundetails/".$mm->id."/".$run->id," View Details ")."&nbsp;".
					Html::b($webroot."/report/runmailmerge/".$mm->id."/".$run->id," Run Now! ")
					);
		}
	}
	else {
		// no runs
		$line[] = array("No mail merge runs to list","","","","","");
	}
	
	// display results
	$w->ctx("runlist",Html::table($line,null,"tablesorter",true));
	
}

function viewrundetails_ALL(Web &$w) {
	$p = $w->pathMatch("mmid","runid");

	// get mail merge
	$mm = $w->Report->getMailMergeInfobyId($p["mmid"]);
	
	report_navigation($w, "Mail Merge Runs: " . $mm->title);

	// get run info
	$run = $w->Report->getMailMergeRunInfo($p['runid']);
	
	// get list of recipients
	$recipients = $w->Report->getMailMergeRecipients($p['runid']);
	
	$line = array(array("Email","Sent"));
	
	if ($recipients) {
		foreach ($recipients as $rec) {
			$status = ($rec->is_sent != "") ? $rec->is_sent : "- no status -";
			$line[] = array(
					
					$rec->recipient_email,
					$status,
					);
		}
	}
	else {
		// no runs
		$line[] = array("No recipients to list","","","","","");
	}
	
	// display results
	$w->ctx("mmid",$p['mmid']);
	$w->ctx("recipientlist",Html::table($line,null,"tablesorter",true));
}

function runmailmerge_GET(Web &$w) {
	report_navigation($w, "Execute a Mail Merge");
	
	$p = $w->pathMatch("mmid","runid");

	// get mail merge
	$mm = $w->Report->getMailMergeInfobyId($p["mmid"]);
	
	// get list of recipients
	$recipients = $w->Report->getMailMergeRecipients($p['runid']);
	
	if ($recipients) {
		require("PHPMailer/class.phpmailer.php");
		
		$mail = new PHPMailer(true);
		$mail->IsMail();
		/*
		$mail->IsSMTP();
		$mail->Mailer = "smtp";
		$mail->Host = "smtp.gmail.com";
		$mail->Port = 587;
		$mail->SMTPAuth = true;
		$mail->SMTPSecure = "tls";
 		$mail->Username = "ian.edwards@pyramidpower.com.au";
		$mail->Password = "Pyramid02";
//		$mail->SMTPDebug = 2;
		*/
		foreach ($recipients as $rec) {
			try {
				$mail->ClearAddresses();
				$mail->AddAddress($rec->recipient_email, $rec->recipient_name);
				$mail->SetFrom($mm->fromemail, $mm->fromname);
				$mail->AddReplyTo($mm->fromemail, $mm->fromname);
				$mail->Subject = $mm->subject;
				$mail->MsgHTML($rec->mmbody);
				$mail->Send();

				$rec->is_sent = date("Y-m-d G:i:s");
				$msg = "sent";
				}
			catch (phpmailerException $e) {
				$msg = $e->errorMessage();
				$rec->is_sent = $msg;
			}
			catch (Exception $e) {
				$rec->is_sent = $e->getMessage();
			}
		$rec->update();
		}
	}
	$w->ctx("msg",$msg);
	
}

function createmailmerge_GET(Web &$w) {
	report_navigation($w, "Create a Mail Merge");
	
	// get list of reports for logged in user. sort to list unapproved reports first
	$reports = $w->Report->getReportsbyUserId($_SESSION['user_id']);

	// if i am a member of a list of reports, lets display them
	if (($reports) && ($w->auth->user()->hasRole("report_editor")  || $w->auth->user()->hasRole("report_admin"))) {
		foreach ($reports as $report) {
			// get report data
			$rep = $w->Report->getReportInfo($report->report_id);
			$myrep[] = array($rep->title, $rep->id);
		}
	}
	
	$f = Html::form(array(
	array("Create a Mail Merge from a Report","section"),
	array("Select Report","select","report_id",null,$myrep),
	array("Title","text","title"),
	array("Description","textarea","description",null,"50","6"),
	array("Mail Template","section"),
	array("To","text","to"),
	array("From Name","text","fromname"),
	array("From Email","text","fromemail"),
	array("CC","text","cc"),
	array("bcc","text","bcc"),
	array("Subject","text","subject"),
	array("Mail Template","textarea","mmbody",null,"50","30"),
	),$w->localUrl("/report/createmailmerge/"),"POST"," Save ");
	
	$w->ctx("createmm",$f);
}

function createmailmerge_POST(Web &$w) {
	report_navigation($w, "Create a Mail Merge");
	
	// create a new feed
	$mm = new ReportMailMerge($w);

	$_POST["dt_created"] = date("d/m/Y");
	$_POST["is_deleted"] = 0;
	
	$mm->fill($_POST);
	$mm->insert();

	$w->msg("Mail Merge: " . $mm->title . " has been created","/report/listmailmerge/");
}

function editmailmerge_GET(Web &$w) {
	report_navigation($w, "Edit Mail Merge");
	
	$p = $w->pathMatch("id");

	// get mail merge
	$mm = $w->Report->getMailMergeInfobyId($p["id"]);
	
	// get list of reports for logged in user. sort to list unapproved reports first
	$reports = $w->Report->getReportsbyUserId($_SESSION['user_id']);

	// if i am a member of a list of reports, lets display them
	if (($reports) && ($w->auth->user()->hasRole("report_editor")  || $w->auth->user()->hasRole("report_admin"))) {
		foreach ($reports as $report) {
			// get report data
			$rep = $w->Report->getReportInfo($report->report_id);
			$myrep[] = array($rep->title, $rep->id);
		}
	}
	
	$f = Html::form(array(
	array("Create a Mail Merge from a Report","section"),
	array("Select Report","select","report_id",$mm->report_id,$myrep),
	array("Title","text","title", $mm->title),
	array("Description","textarea","description",$mm->description,"50","6"),
	array("Mail Template","section"),
	array("To","text","to",$mm->to),
	array("From Name","text","fromname",$mm->fromname),
	array("From Email","text","fromemail",$mm->fromemail),
	array("CC","text","cc",$mm->cc),
	array("bcc","text","bcc",$mm->bcc),
	array("Subject","text","subject",$mm->subject),
	array("Mail Template","textarea","mmbody",$mm->mmbody,"50","30"),
	),$w->localUrl("/report/editmailmerge/".$mm->id),"POST"," Save ");
	
	$w->ctx("editmm",$f);
}

function editmailmerge_POST(Web &$w) {
	report_navigation($w, "Edit a Mail Merge");
	
	$p = $w->pathMatch("id");
	
	$mm = $w->Report->getMailMergeInfobyId($p["id"]);

	$mm->fill($_POST);
	$mm->update();

	$w->msg("Mail Merge " . $mm->title . " has been updated","/report/listmailmerge/");
}

function deletemailmerge_ALL(Web &$w) {
	$p = $w->pathMatch("id");
	
	$mm = $w->Report->getMailMergeInfobyId($p["id"]);

	$arr["is_deleted"] = 1;
	
	$mm->fill($arr);
	$mm->update();

	$w->msg("Mail Merge " . $mm->title . " has been deleted","/report/listmailmerge/");
}

