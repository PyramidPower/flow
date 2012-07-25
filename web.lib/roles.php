<?php
// $Id: roles.php 1009 2010-12-06 20:00:10Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

function role_administrator_allowed(&$w,$path) {
    return true;
}

function role_user_allowed(&$w,$path) {
    $include = array(
        "main",
        "auth",
        "contact",
        "map",
        "search",
        "inbox",
    	"address",
    	"mobile",
    	"onlinequote"
    );
    $path_explode = explode("/", $path);
    $handler = $path_explode[0];
    $action = $path_explode[1];
    $allowed = in_array($handler,$include);
    return $allowed;
}

function role_agent_allowed(&$w,$path) {
    $include = array(
        "main",
        "auth",
        "map",
        "search",
        "inbox",
    	"address",
    	"onlinequote",
    	"mobile"
    );
    $path_explode = explode("/", $path);
    $handler = $path_explode[0];
    $action = $path_explode[1];
    $allowed = in_array($handler,$include);
    return $allowed;
}

/**
 * This role is called when no user is logged in!
 * 
 * @param <type> $w
 * @return <type>
 */
function anonymous_allowed(&$w,$path) {
    // First check by specific IP addresses!
    // this is useful for scripts to be executed via cron jobs
    
    $ips = array(
        "111.118.166.108",
        "111.118.166.107",
    );
    if( in_array($w->requestIpAddress(),$ips)) {
        return true;
    }

    // check include paths for people
    $include = array(
        "auth/login",
    );    
    $in_path = in_array($path,$include);

    // check complete handlers
    $handlers = array(
    	"mobile",
    	"onlinequote"
    );
    $path_explode = explode("/", $path);
    $handler = $path_explode[0];
    $action = $path_explode[1];
    $allowed = in_array($handler,$handlers);
    
    return $allowed || $in_path || $has_ip || landh_access($w, $path);
}

/**
 * Check access for L&H Integration
 * 
 * @param unknown_type $w
 * @param unknown_type $path
 */
function landh_access(&$w,$path){
	/*
	$lhintegration_ips = array(
		"139.130.3.198",
	);
	*/
	$lhintegration_paths = array(
		"integration-landh/orders",
		"integration-landh/orderresponse",	
	);
	
	if (/*in_array($w->requestIpAddress(),$lhintegration_ips)  
		&&*/ in_array($path, $lhintegration_paths)) {
		return true;
	}
	return false;
	
}

?>
