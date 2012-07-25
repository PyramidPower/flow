<?php
function admin_index_ALL(Web &$w) {
    admin_navigation($w,"Auditing");
    $form["Audit Log Filter"] = array(
    	array(
    		array("User","select","creator_id",null,$w->Admin->getLoggedUsers()),
    		array("Module","select","module",null,$w->Admin->getLoggedModules()),
    		array("Action","select","action",null,$w->Admin->getLoggedActions())
    	));
    $form["Date Range"] = array(
    	array(
    		array("From Date","date","dt_from"),
    		array("To Date","date","dt_to")
    	)
    );
    $w->out(Html::multiColForm($form,"/admin-audit/list","POST","Search"));
}

function admin_list_POST(Web &$w) {
	
}
