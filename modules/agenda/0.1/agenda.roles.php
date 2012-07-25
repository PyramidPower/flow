<?php

#############################################################################################
##
## Name the roles as _operations_createinvoices_
##
## No rolesWithUpperCase no '_' in the middle !
## Only rolessuchthisonewillwork .
##
## Check acces:
##
## $w->auth->user()->hasAnyRole(array('operations_manager','operations_createpurchaseorder')
##
## $w->auth->user()->hasRole('operations_viewinvoices')
##
#############################################################################################

function role_agenda_manager_allowed(&$w,$path) 
{
   return preg_match("/agenda(-.*)?\//",$path);
}



//-----------------------------------------------------------------
//	Holiday Calendar functionality access 
//-----------------------------------------------------------------
function role_agenda_manageholidayscal_allowed(Web $w, $path)
{
	$h = "/agenda-holidays\/auHolidaysList/";  
	return 		preg_match($h, $path) ;
}



















/**
 * Agencies admins and agencies members restrictions:

function role_operations_user_allowed(&$w,$path) 
{
    $index = "/(operations\/index)/";  
    
    $installers_index = "/operations-agency\/(.*)/";  // to do: apply methods restrictions!
        
    $actions_scheduling = "/operations-schedule\/(.*)/";
    
    $jobs = "/operations-job\/(.*)/";
    
    $teams = "/operations-team\/(.*)/";
    $members = "/operations-member\/(.*)/"; 
    $address = "/address(-.*)?\//";
    
    $paySchedule = "/operations-payschedule\/viewAgencyPaySchedule/";
    $purchOrders = "/operations-purchorders\/viewAgencyPurchOrders/";
    $jpo = "/operations-payschedule\/viewJobAccounting/";  
    $invoices = "/operations-invoices\/viewAgencyInvoices/";
    
    $forms = "/operations-form\/(.*)/";   
    
    return 		preg_match($index, $path) 
    		||  preg_match($installers_index, $path) 
    		||  preg_match($actions_scheduling, $path)
    		|| 	preg_match($jobs, $path)
    		|| 	preg_match($teams, $path)
    		|| 	preg_match($members, $path)
    		|| 	preg_match($address, $path)
    		||  preg_match($paySchedule, $path)
    		|| 	preg_match($purchOrders, $path)
    		|| 	preg_match($jpo, $path)
    		|| 	preg_match($invoices, $path)
    		|| 	preg_match($forms, $path); 
   
}


function role_operations_scheduler_allowed(&$w,$path) 
{
    $index = "/(operations\/index)/";  
    
    $installers_index = "/operations-agency\/index/";
        
    $actions_scheduling = "/operations-schedule\/(.*)/";
    
    $actions_logistics = "/operations-logistics\/(.*)/";
    
    $jobs = "/operations-job\/(.*)/";
    
    $address = "/address(-.*)?\//";  // access to AJAX calls for filtering jobs
    
    // for someone who is scheduler and team admin
    $installers_index = "/operations-agency\/(.*)/";
    $teams = "/operations-team\/(.*)/";
    $members = "/operations-member\/(.*)/";
        
    $paySchedule = "/operations-payschedule\/viewAgencyPaySchedule/";
    $jobAcc = "/operations-payschedule\/viewJobAccounting/";
    $purchOrders = "/operations-purchorders\/viewAgencyPurchOrders/";
    $po = "/operations-payschedule\/createJobPO/";    
    $invoices = "/operations-invoices\/(.*)/";
    $forms = "/operations-form\/(.*)/";  
    
    return 		preg_match($index, $path) 
    		|| 	preg_match($installers_index, $path) 
    		||  preg_match($actions_scheduling, $path)
    		||  preg_match($actions_logistics, $path)
    		|| 	preg_match($jobs, $path)
    	 	|| 	preg_match($address, $path)
    	 	|| 	preg_match($installers_index, $path)
    	 	|| 	preg_match($members, $path)
    	 	|| 	preg_match($teams, $path)
    		||  preg_match($paySchedule, $path)
    		||  preg_match($jobAcc, $path)
    		|| 	preg_match($purchOrders, $path)
    		|| 	preg_match($invoices, $path)
    		|| 	preg_match($forms, $path)
    		|| 	preg_match($po, $path); 
    		
    
  	 
}

*/
