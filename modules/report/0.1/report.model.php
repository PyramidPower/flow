<?php

class ReportService extends DbService {

	// function to sort lists by date schedule
	static function sortBySchedule($a, $b) {
    	if ($a->dt_schedule == $b->dt_schedule) {
			return 0;
		}
		return ($a->dt_schedule < $b->dt_schedule) ? +1 : -1;
	}

	// get list of flow modules for Html::select
	function & getFlowModules() {
		$fmodules = $this->w->handlers();
		if ($fmodules) {
			foreach ($fmodules as $f) {
				$flow_modules[] = array(ucfirst($f),$f);
			}
			return $flow_modules;
		}
	}
	
	// static list of group permissions
	function & getReportPermissions() {
    	return array("USER","EDITOR");
    }
	
    // return a mail merge run its report id
	function & getMailMergeRunInfo($id) {
		return $this->getObject("ReportMailMergeRun",array("id"=>$id,"is_deleted"=>0));
	}
	
	
	// return list of mail merge runs for given mail merge
	function & getMailMergeRuns() {
		return $this->getObjects("ReportMailMergeRun",array("is_deleted"=>0));
	}
	
	// return list of mail merge recipients for given mail merge run
	function & getMailMergeRecipients($id) {
		return $this->getObjects("ReportMailMergeRecipient",array("mailmergerun_id",$id));
	}
	
	// get recipient count for a given run
	function & getRunRecipientCount($id, $where=null) {
		if ($id) {
			$where = "where id = " . $id . $where;
			$rows = $this->_db->sql("SELECT count(recipient_email) as cnt from ".ReportMailMergeRecipient::getDbTableName(). " " . $where)->fetch_all();
			return $rows;
		}
	}
	
	// return list of mail merges
	function & getMailMerges() {
		return $this->getObjects("ReportMailMerge",array("is_deleted"=>0));
	}
	
	// return a mail merge given its id
	function & getMailMergeInfobyId($id) {
		return $this->getObject("ReportMailMerge",array("id"=>$id,"is_deleted"=>0));
	}
	
	// return a mail merge given its report id
	function & getMailMergeInfobyReportId($id) {
		return $this->getObject("ReportMailMerge",array("report_id"=>$id,"is_deleted"=>0));
	}
	
	// return a report given its ID
	function & getReportInfo($id) {
		return $this->getObject("Report",array("id"=>$id));
	}
	
	// return list of feeds
	function & getFeeds() {
		return $this->getObjects("ReportFeed",array("is_deleted"=>0));
	}
	
	// return a feed given its id
	function & getFeedInfobyId($id) {
		return $this->getObject("ReportFeed",array("id"=>$id,"is_deleted"=>0));
	}
	
	// return a feed given its report id
	function & getFeedInfobyReportId($id) {
		return $this->getObject("ReportFeed",array("report_id"=>$id,"is_deleted"=>0));
	}
	
	// return a feed given its key
	function & getFeedInfobyKey($key) {
		return $this->getObject("ReportFeed",array("key"=>$key,"is_deleted"=>0));
	}
	
	// return list of APPROVED and NOT DELETED report IDs for a given a user ID and a where clause
	function & getReportsbyUserWhere($id,$where) {
		// need to get reports for me and my groups
		// me
		$myid[] = $id;

		// need to check all groups given group member could be a group
		$groups = $this->w->Auth->getGroups();
		
		if ($groups) {
			foreach ($groups as $group) {
				$flg = $this->w->Auth->user()->inGroup($group);
				if ($flg)
					$myid[$group->id] = $group->id;
			}
		}
		// list of IDs to check for report membership, my ID and my group IDs
		$id = implode(",",$myid);

		$where .= " and r.is_deleted = 0 and m.is_deleted = 0";

		$rows = $this->_db->sql("SELECT distinct r.* from ".ReportMember::getDbTableName()." as m inner join ".Report::getDbTableName()." as r on m.report_id = r.id where m.user_id in (" . $id . ") " . $where . " order by r.is_approved desc,r.title")->fetch_all();
		$rows = $this->fillObjects("Report",$rows);
		return $rows;
	}
	
	// return list of APPROVED and NOT DELETED report IDs for a given a user ID as member
	function & getReportsbyUserId($id) {
		// need to get reports for me and my groups
		// me
		$myid[] = $id;

		// need to check all groups given group member could be a group
		$groups = $this->w->Auth->getGroups();
		
		if ($groups) {
			foreach ($groups as $group) {
				$flg = $this->w->Auth->user()->inGroup($group);
				if ($flg)
					$myid[$group->id] = $group->id;
			}
		}
		// list of IDs to check for report membership, my ID and my group IDs
		$id = implode(",",$myid);
		
		$rows = $this->_db->sql("SELECT distinct m.report_id from ".ReportMember::getDbTableName()." as m inner join ".Report::getDbTableName()." as r on m.report_id = r.id where m.user_id in (" . $id . ") and r.is_deleted = 0 and m.is_deleted = 0 order by r.is_approved desc,r.title")->fetch_all();
		$rows = $this->fillObjects("ReportMember",$rows);
		return $rows;
	}

	// return list of APPROVED and NOT DELETED report IDs for a given a user ID and Flow Module
	function & getReportsbyModulenId() {
		// need to get reports for me and my groups
		// me
		$myid[] = $_SESSION['user_id'];

		// need to check all groups given group member could be a group
		$groups = $this->w->Auth->getGroups();
		
		if ($groups) {
			foreach ($groups as $group) {
				$flg = $this->w->Auth->user()->inGroup($group);
				if ($flg)
					$myid[$group->id] = $group->id;
			}
		}
		// list of IDs to check for report membership, my ID and my group IDs
		$id = implode(",",$myid);
		$flow_module = $this->w->currentHandler();
		
		$rows = $this->_db->sql("SELECT distinct r.id,r.title from ".ReportMember::getDbTableName()." as m inner join ".Report::getDbTableName()." as r on m.report_id = r.id where m.user_id in (" . $id . ") and r.flow_module = '" . $flow_module . "' and r.is_deleted = 0 and m.is_deleted = 0 order by r.is_approved desc,r.title")->fetch_all();
		$rows = $this->fillObjects("Report",$rows);
		return $rows;
	}
	
	// return menu links of APPROVED and NOT DELETED report IDs for a given a user ID as member
	function & getReportsforNav() {
		$repts = array();
		$reports = $this->getReportsbyModulenId();
		
		if ($reports) {
			foreach ($reports as $report) {
				$this->w->menuLink("report/runreport/".$report->id,$report->title,$repts);
			}
		}
		return $repts;
	}
	
	// return list of members attached to a report for given report ID
	function & getReportMembers($id) {
		return $this->getObjects("ReportMember",array("report_id"=>$id,"is_deleted"=>0));
	}
	
	// return member for given report ID and user id
	function & getReportMember($id, $uid) {
		return $this->getObject("ReportMember",array("report_id"=>$id,"user_id"=>$uid));
	}
	
	// return a users full name given their user ID
	function & getUserById($id) {
		$u = $this->w->auth->getUser($id);
		return $u ? $u->getFullName() : "";
	}
	
	// for parameter dropdowns, run SQL statement and return an array(value,title) for display
	function & getFormDatafromSQL($sql) {
		$rows = $this->_db->sql($sql)->fetch_all();
		if ($rows) {
			foreach ($rows as $row) {
				$arr[] = array($row['title'],$row['value']);
			}
			return $arr;
		}
	}

	// given a report SQL statement, return recordset
	function & getRowsfromSQL($sql) {
		return $this->_db->sql($sql)->fetch_all();
	}

	// given a report SQL statement, return recordset
	function & getExefromSQL($sql) {
		return $this->_db->sql($sql)->execute();
	}
	
	// convert dd/mm/yyyy date to yyy-mm-dd for SQL statements
	function & date2db($date) {
		if ($date) {
			list($d,$m,$y) = preg_split("/\/|-|\./", $date);
			return $y."-".$m."-".$d;
		}
	}
	
	// return all tables in the DB for display
	function & getAllDBTables() {
		$dbtbl = array();
		$sql = "show tables in flow";
		$tbls = $this->_db->sql($sql)->fetch_all();
		
		if ($tbls) {
			foreach ($tbls as $tbl) {
				$dbtbl[] = array($tbl['Tables_in_flow'],$tbl['Tables_in_flow']);
			}
		}
		return $dbtbl;
	}
	
	// return array of fields/type in a given table
	function & getFieldsinTable($tbl) {
		$dbflds = "";

		if ($tbl != "") {
			$sql = "show columns in " . $tbl;
			$flds = $this->_db->sql($sql)->fetch_all();
			
			if ($flds) {
				$dbflds = "<table cellpadding=0 cellspacing=0 border=0>\n";
				$dbflds .= "<tr><td><b>Field</b></td><td><b>Type</b></td></tr>\n";
				foreach ($flds as $fld) {
					$dbflds .= "<tr><td>" . $fld['Field'] . "</td><td>" . $fld['Type'] . "</td></tr>\n";					
				}
				$dbflds .= "</table>\n";
			}
		}
		return $dbflds;
	}
	
	function & getSQLStatementType($report_code) {
		// return our list of SQL statements
//		preg_match_all("/@@[a-zA-Z0-9_\s\|,;\(\)\{\}<>\/\-='\.@:%\+\*\$]*?@@/",preg_replace("/\n/"," ",$report_code), $arrsql);
		preg_match_all("/@@.*?@@/",preg_replace("/\n/"," ",$report_code), $arrsql);
		
		// if we have statements, continue ...
		if ($arrsql) {
			foreach ($arrsql as $sql) {
				if ($sql) {
					foreach ($sql as $s) {
						list($title,$sql) = preg_split("/\|\|/",$s);
						// put on one line just to be sure 
						$sql = preg_replace("/\n/", " ", trim($sql));
						$arr = preg_split("/\s/", $sql);
						$action .= $arr[0] . ", ";
					}
				}				
			}
			$action = rtrim($action,", ");
			
			// return comma delimited string of actions of SQL for display only
			return $action;
		}
		else {
			return "No action Found";
		}
	}
	
	// create an array of available report output formats for inclusion in the parameters form
	function & selectReportFormat() {
		$arr = array();
		$arr[] = array("Web Page","html");
		$arr[] = array("Comma Delimited File","csv");
		$arr[] = array("PDF File","pdf");
		$arr[] = array("XML","xml");
		return array(array("Format","select","format",null,$arr));
	}

	// export a recordset as CSV
	function exportcsv($rows, $title) {
		// require the necessary library
		require_once("parsecsv/parsecsv.lib.php");

		// set filename
		$filename = str_replace(" ","_",$title) . "_" . date("Y.m.d-H.i") . ".csv";

		// if we have records, comma delimit the fields/columns and carriage return delimit the rows
		if ($rows) {
			foreach ($rows as $row) {
				//throw away the first line which list the form parameters
				$crumbs = array_shift($row);
				$title = array_shift($row);
				$hds = array_shift($row);
				$hvals = array_values($hds);

				// find key of any links
				foreach ($hvals as $h) {
					if (stripos($h,"_link")) {
						list($fld,$lnk) = preg_split("/_/", $h);
						$ukey[] = array_search($h,$hvals);
						unset($hds[$h]);
					}
				}
										
				// iterate row to build URL. if required
				if ($ukey) {
					foreach ($row as $r) {
						foreach ($ukey as $n => $u) {
							// dump the URL related fields for display
							unset($r[$u]);
						}
						$arr[] = $r;
					}
					$row = $arr;
					unset($arr);
				}
											
				$csv = new parseCSV();
				$this->w->out($csv->output ($filename, $row, $hds));
				unset($ukey);
			}
			$this->w->sendHeader("Content-type","application/csv");
			$this->w->sendHeader("Content-Disposition","attachment; filename=".$filename);
			$this->w->setLayout(null);
		}
	}

	// export a recordset as PDF
	function exportpdf($rows, $title) {
		$filename = str_replace(" ","_",$title) . "_" . date("Y.m.d-H.i") . ".pdf";

		// using TCPDF so grab includes
		require_once('tcpdf/config/lang/eng.php');
		require_once('tcpdf/tcpdf.php');

		// instantiate and set parameters
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Pyramid Power');
		$pdf->SetTitle($title);
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->setLanguageArray($l);
		
		// no header, set font and create a page
		$pdf->setPrintHeader(false);
		$pdf->SetFont("helvetica", "B", 9);
		$pdf->AddPage();

		// Pyramid logo
		$pdf->Image('img/logo_top.png', 10, 10, 0, 0, 'PNG', 'http://flow.pyramidlocal.com.au/img/logo_top.png', 'T');
		// title of report
		$hd = "<h1>" . $title . "</h1>";
		$pdf->writeHTMLCell(0,10,60,15,$hd,0,1,0,true);
		$created = date("d/m/Y g:i a");
		$pdf->writeHTMLCell(0,10,60,25,$created,0,1,0,true);
		
		// display recordset
		if ($rows) {
			foreach ($rows as $row) {
				//throw away the first line which list the form parameters
				$crumbs = array_shift($row);
				$title = array_shift($row);
				$hds = array_shift($row);
				$hds = array_values($hds);
			
				$results = "<h3>" . $title . "</h3>";
				$results .= "<table cellpadding=2 cellspacing=2 border=0 width=100%>\n";
				foreach ($row as $r) {
					$i = 0;
					foreach ($r as $field) {
						if (!stripos($hds[$i],"_link")) {
							$results .= "<tr><td width=20%>" . $hds[$i] . "</td><td>" . $field . "</td></tr>\n";
						}
						$i++;
					}
					$results .= "<tr><td colspan=2><hr /></td></tr>\n";
				}
				$results .= "</table><p>";
				$pdf->writeHTML($results, true, false, true, false);
			}
		}

		// set for 'open/save as...' dialog
		$pdf->Output($filename, 'D');
	}
	
	// export a recordset as XML
	function exportxml($rows, $title) {
		$filename = str_replace(" ","_",$title) . "_" . date("Y.m.d-H.i") . ".xml";

		$this->w->out("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n");
		$this->w->out("<report>\n");
		$this->w->out("\t<author>Pyramid Power</author>\n");
		$this->w->out("\t<title>" . $title . "</title>\n");
		$this->w->out("\t<created>" . date("d/m/Y h:i:s") . "</created>\n");
		
		// if we have records ...
		if ($rows) {
			foreach ($rows as $row) {
				//throw away the first line which list the form parameters
				$crumbs = array_shift($row);
				$title = array_shift($row);
				$hds = array_shift($row);
				$hds = array_values($hds);
			
				$this->w->out("\t<rows title=\"".$title."\">\n");
				
				foreach ($row as $r) {
					$this->w->out("\t\t<row>\n");
					$i = 0;
					foreach ($r as $field) {
						if (!stripos($hds[$i],"_link")) {
							$this->w->out("\t\t\t<" . preg_replace("/\s+/","",$hds[$i]) . ">" . htmlentities($field) . "</" . preg_replace("/\s+/","",$hds[$i]) . ">\n");
						}
						$i++;
					}
					$this->w->out("\t\t</row>\n");
				}
				$this->w->out("\t</rows>\n");
			}
		}
		$this->w->out("</report>\n");
		
		// set header for 'open/save as...' dialog
		$this->w->sendHeader("Content-type","application/xml");
		$this->w->sendHeader("Content-Disposition","attachment; filename=".$filename);
		$this->w->setLayout(null);
	}
	
	// function to substitute special terms
	function putSpecialSQL($sql) {
		if ($sql != "") {
			$special = array();
			$replace = array();
		
			// get user roles
			$usr = $this->w->Auth->user();
			foreach ($usr->getRoles() as $role) {
				$roles .= "'" . $role ."',";
			}
			$roles = rtrim($roles,",");
		
			// $special must be in terms of a regexp for preg_match
			$special[0] = "/\{\{current_user_id\}\}/";
			$replace[0] = $_SESSION["user_id"];
			$special[1] = "/\{\{roles\}\}/";
			$replace[1] = $roles;
			$special[2] = "/\{\{webroot\}\}/";
			$replace[2] = $this->w->localUrl();
		
			// replace and return
			return preg_replace($special,$replace,$sql);
		}
	}
	
	// function to check syntax of report SQL statememnt
	function & getcheckSQL($sql) {
		// checking for rows will return false if no data is returned, even if SQL is ok
		// so let's just run the statement and try to catch any exceptions otherwise SQL runs ok
		try {
			$this->startTransaction();
			$rows = $this->getExefromSQL($sql);
			$this->rollbackTransaction();
				
			return true;
		}
		catch (Exception $e) {
			// SQL returns errors so clean up and return false
			$this->rollbackTransaction();
			$this->_db->clear_sql();
			return false;
		}
	}
}

class Report extends DbObject {
	var $title;			// report title
	var $flow_module;	// flow module report pertains to
	var $category; 		// category of report given by Lookup
	var $description;	// description of report
	var $report_code; 	// the 'code' describing the report
	var $sqltype;		// determine type of statement: select/update/insert/delete
	var $is_approved;	// has the Report Admin approved this report
	var $is_deleted;	// is report deleted
	
	var $_modifiable;	// employ the modifiable aspect
	
	// actual table name
	function getDbTableName() {
		return "report";
	}
	
	// return a category title using lookup with type: ReportCategory
	function getCategoryTitle() {
		$c = $this->Report->getObject("Lookup",array("type"=>"ReportCategory","code"=>$this->category));
		return $c->title;
	}
	
	// build form of parameters for generating report
	function getReportCriteria() {
		// set form header
		$arr = array(array("Select Report Criteria","section"));
		$arr[] = array("Description","static","description",$this->description);
		
		// build array of all contents within any [[...]]
		preg_match_all("/\[\[.*?\]\]/",preg_replace("/\n/"," ",$this->report_code), $form);
		
		// if we've found elements meeting that style ....
		if ($form) {
			// foreach of the elements ...
			foreach ($form as $element) {
				// if there is actually an element ...
				if ($element) {
					// it will be as an array so ....
					foreach ($element as $f) {
						// element enclosed in [[...]]. dump [[ & ]]
						$patterns = array();
						$patterns[0] = "/\[\[\s*/";
						$patterns[1] = "/\s*\]\]/";
						$replacements = array();
						$replacements[0] = "";
						$replacements[1] = "";
						$f = preg_replace($patterns, $replacements, $f);
				
						// split element on ||. rules provide for at most 4 parts in strict order
						list($name,$type,$label,$sql) = preg_split("/\|\|/", $f);
						$name = trim($name);
						$type = trim($type);
						$label = trim($label);
						$sql = trim($sql);
						
						$sql = $this->Report->putSpecialSQL($sql);
						
						// do something different based on form element type
						switch ($type) {
							case "select":
								if ($sql != "") {
									// if sql exists, check SQL is valid
									$flgsql = $this->Report->getcheckSQL($sql);
				
									// if valid SQL ...
									if ($flgsql) {
										//get returns for display as dropdown
										$values = $this->Report->getFormDatafromSQL($sql);
									}
									else {
										// there is a problem, say as much
										$values = array("SQL error");
									}
								}
								else {
									// there is a problem, say as much
									$values = array("No SQL statement");
								}
								// complete array which becomes form dropdown
								$arr[] = array($label,$type,$name,$_REQUEST[$name],$values);
								break;
							case "checkbox":
							case "text":
							case "date":
							default:
								// complete array which becomes other form element type
								$arr[] = array($label,$type,$name,$_REQUEST[$name]);
						} 
					}
				}
			}
		}
		// get the selection of output formats as array
		$format = $this->Report->selectReportFormat();
		// merge arrays to give all parameter form requirements
		$arr = array_merge($arr,$format);
		// return form
		return $arr;
	}
	
	// generate the report based on selected parameters
	function getReportData() {
		// build array of all contents within any @@...@@
//		preg_match_all("/@@[a-zA-Z0-9_\s\|,;\(\)\{\}<>\/\-='\.@:%\+\*\$]*?@@/",preg_replace("/\n/"," ",$this->report_code), $arrsql);
		preg_match_all("/@@.*?@@/",preg_replace("/\n/"," ",$this->report_code), $arrsql);
		
		// if we have statements, continue ...
		if ($arrsql) {
			// foreach array element ...
			foreach ($arrsql as $strsql) {
				// if element exists ....
				if ($strsql) {
					// it will be as an array, so ...
					foreach ($strsql as $sql) {
						// strip our delimiters, remove newlines
						$sql = preg_replace("/@@/", "", $sql);
						$sql = preg_replace("/[\r\n]+/", " ", $sql);
						
						// split into title and statement fields
						list($stitle,$sql) = preg_split("/\|\|/", $sql);
						$title = array(trim($stitle));
						$sql = trim($sql);
						
						// determine type of SQL statement, eg. select, insert, etc.
						$arrsql = preg_split("/\s+/", $sql);
						$action = strtolower($arrsql[0]);
		
						$crumbs = array(array());
						// each form element should correspond to a field in our SQL where clause ... substitute
						// do not use $_REQUEST because it includes unwanted cookies
						foreach (array_merge($_GET, $_POST) as $name => $value) {
							// convert input dates to yyyy-mm-dd for query
							if (startsWith($name,"dt_"))
								$value = $this->Report->date2db($value);
				
							// substitute place holder with form value
							$sql = str_replace("{{".$name."}}", $value, $sql);
		
							// list parameters for display
							if (($name != "FLOW_SID") && ($name != "format"))
								$crumbs[0][] = $value;
							}

						// if our SQL is still intact ...
						if ($sql != "") {
							// check the SQL statement for special parameter replacements
							$sql = $this->Report->putSpecialSQL($sql);
							// check the SQL statement for validity
							$flgsql = $this->Report->getcheckSQL($sql);

							// if valid SQL ...
							if ($flgsql) {
								// starter arrays
								$hds = array();
								$flds = array();
								$line = array();
		
								// run SQL and return recordset
								if ($action == "select") {
									$rows = $this->Report->getRowsfromSQL($sql);
				
									// if we have a recordset ... 
									if ($rows) {
										// iterate ...
										foreach ($rows as $row) {
											// if row actually exists
											if ($row) {
												// foreach field/column ...
												foreach ($row as $name => $value) {
													// build our headings array
													$hds[$name] = $name;
													// build a fields array
													$flds[] = $value;
												}
												// put fields array into a line array and reset field array for next record
												$line[] = $flds;
												unset($flds);
											}
										}
										// wrap headings array appropriately
										$hds = array($hds);
										// merge to create completed report for display
										$tbl = array_merge($crumbs,$title,$hds,$line);
										$alltbl[] = $tbl;
										unset($line);
										unset($hds);
										unset($crumbs);
										unset($tbl);
									}
									else {
										$alltbl[] = array(array("No Data Returned for selections"), $stitle, array("Results"), array("No data returned for selections"));
										
									}
								}
								else {
									// create headings
									$hds = array(array("Status","Message"));
									
									// other SQL types do not return recordset so treat differently from SELECT
									try {
										$this->startTransaction();
										$rows = $this->Report->getExefromSQL($sql);
										$this->commitTransaction();
										$line = array(array("SUCCESS","SQL has completed successfully"));
									}
									catch (Exception $e) {
										// SQL returns errors so clean up and return error
										$this->rollbackTransaction();
										$this->_db->clear_sql();
										$line = array(array("ERROR","A SQL error was encountered: " . $e->getMessage()));
									}
									$tbl = array_merge($crumbs,$title,$hds,$line);
									$alltbl[] = $tbl;
									unset($line);
									unset($hds);
									unset($crumbs);
									unset($tbl);
								}
							}
							else {
								// if we fail the SQL check, say as much
								$alltbl = array(array("ERROR"),array("There is a problem with your SQL statement:".$sql));
							}
						}
						else {
							// if we fail the SQL check, say as much
							$alltbl = array(array("ERROR"),array("There is a problem with your SQL statement"));
						}
					}
				}
			}
		}
		else {
			$alltbl = array(array("ERROR"),array("There is a problem with your SQL statement"));
		}
		return $alltbl;
	}
}
// report member object
class ReportMember extends DbObject {
	var $report_id;		// report id
	var $user_id; 		// user id
	var $role;			// user role: user, editor
	var $is_deleted; 	// deleted flag
	
	// actual table name
	function getDbTableName() {
		return "report_member";
	}
}

class ReportFeed extends DBObject {
	var $report_id;		// source report id
	var $title;			// feed title
	var $description;	// feed description
	var $key;			// special feed key
	var $url;			// url to access feed
	var $dt_created;	// date created
	var $is_deleted;	// is deleted flag

	// actual table name
	function getDbTableName() {
		return "report_feed";
	}

	// get feed key upon insert of new feed
	function insert() {
		if (!$this->key)
			$this->key = uniqid();

		// insert feed into database
		parent::insert();
	}
}

class ReportMailMerge extends DBObject {
	var $report_id;		// source report id
	var $title;			// MM title
	var $description;	// MM description
	var $to;			// To: header
	var $fromname;		// From: header name
	var $fromemail;		// From: header email
	var $cc;			// CC: header
	var $bcc;			// BCC: header
	var $subject;		// Subject: header
	var $mmbody;		// body message template
	var $dt_created;	// date created
	var $is_deleted;	// is deleted flag
	
	//actual table name
	function getDbTableName() {
		return "report_mail_merge";
	}
}

class ReportMailMergeRun extends DBObject {
	var $mailmerge_id;	// source report id
	var $to;			// To: header
	var $fromname;		// From: header name
	var $fromemail;		// From: header email
	var $cc;			// CC: header
	var $bcc;			// BCC: header
	var $subject;		// Subject: header
	var $dt_created;	// date created
	var $dt_schedule;	// when to trigger mass email send
	var $is_deleted;	// is deleted flag
	
	//actual table name
	function getDbTableName() {
		return "report_mail_merge_run";
	}
}

class ReportMailMergeRecipient extends DBObject {
	var $mailmergerun_id;	// source mail merge id
	var $recipient_email;	// recipient email
	var $subject;
	var $mmbody;		// body message template
	var $is_sent;			// has this email been sent?
	
	//actual table name
	function getDbTableName() {
		return "report_mail_merge_recipient";
	}
}
