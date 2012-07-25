<?php 
require_once "web.lib/db.php";
require_once "web.lib/web.php";
require_once "web.lib/html.php";
require_once "web.lib/roles.php";
require_once "web.lib/functions.php";
include_once "conf/module.conf.php";

define("LIBPATH", str_replace("\\", "/", dirname(__FILE__).'/lib'));
define("FILE_ROOT", str_replace("\\", "/", dirname(__FILE__)."/files/"));
define("MEDIA_ROOT", str_replace("\\", "/", dirname(__FILE__)."/media/"));
define("ROOT", str_replace("\\", "/", dirname(__FILE__)));

set_include_path(get_include_path() . PATH_SEPARATOR . LIBPATH);
require_once "crystal-0.4/Crystal.php";

// ============== Init web ==============
function init_PRE_ALL(&$w) {
	global $modules;
	
	// add this to the audit table!
	$blacklist = array();
	foreach ($modules as $name => $options) {
		if ($options['audit'] === false) {
			$blacklist[] = array($name,"*");
		} else if ($options['audit'] === true && $options['audit_blacklist']) {
			foreach($options['audit_blacklist'] as $action) {
				$blacklist[]=array($name,$action);
			}
		}
	}
	$w->Admin->addAuditLogEntry($blacklist);
		
    // set the top navigation
    $nav = array();
    if ($w->auth->loggedIn()) {
        $nav[]=$w->menuLink("main/index","Home");
    	$nav[]=$w->menuLink("inbox/index","Inbox".$w->service('Inbox')->inboxCountMarker());
        $nav[]=$w->menuLink("news/index","News".$w->service('News')->getUserNewsCountMarker());
        
        foreach ($modules as $name => $options) {
        	if ($options['topmenu'] && $name != "inbox" && $name != "news") {
            	$w->menuLink($name."/index",ucfirst($name),$nav);
        	}
        }
    }
    $w->ctx("top_navigation", $nav);
}

//=============== Timezone ======================
date_default_timezone_set('Australia/Sydney');

//=============== init web.php ==================
$web = new Web();
$db_config = array(
        'hostname' => 'localhost',
        'username' => 'flow_user',
        'password'=> '4UVCy@a8dd',
        'database' => 'flow',
        'driver' => 'mysql'
);

$web->db = Crystal::db($db_config);
$web->setPreHandlers("init");
$web->setModules($modules);
$web->setLogLevel("info");
$web->_webroot = "https://flow.pyramidpower.com.au";

define("WEBROOT", $web->_webroot);
//============== Initial Context Items =========
$web->ctx("COMPANY_NAME","Pyramid Power");
//============== start application =============
$web->start();
