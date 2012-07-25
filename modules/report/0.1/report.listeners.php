<?php
function report_listener_PRE_ACTION(&$w) {
   	// build Navigation to Reports for current Module
    if ($w->auth->loggedIn()) {
		$boxes = $w->ctx("boxes");
    	$reports = $w->Report->getReportsforNav();
        	if ($reports) {
				$boxes["Reports"] = Html::ul($reports,null,"navlinks");
           		$w->ctx("boxes",$boxes);
		}
    }
}
