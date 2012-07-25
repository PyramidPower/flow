<?php

function view_GET(Web &$w) {
	$p = $w->pathMatch("m","a");
	// first see if we need to split into sub modules
	$module = $p['m'];
	$action = $p['a'];
	
	// check if help is allowed for this topic
	if (!$w->auth->allowed($p['m'].'/'.$p['a'])) {
		$w->ctx("help_content","Sorry, there is no help for this topic.");
	}
	
	// check for subhandler
	if (strcontains($p['m'], array("-"))) {
		$ms = explode("-", $p['m']);
		$module = $ms[0];
		$submodule = $ms[1];
	}	

	// find a module toc
	$tocf = getHelpFileContent($w, $module,null,$module."_toc");
	if ($tocf) {
		$w->ctx("module_toc",$module.'/'.$module."_toc");
		$w->ctx("module_title",extractTitle($tocf));
	}
	
	// load help file
	$help_file = getHelpFilePath($w,$module,$submodule,$action);
	$content = "Sorry, this help topic is not yet written.";
	if (file_exists($help_file)) {
		$content = file_get_contents($help_file);
	}
	
	
	// set context
	$w->ctx("help_content",helpMarkup(pruneRestricted($w, $content),$module));
	$w->ctx("module",$module);
	$w->ctx("submodule",$submodule);
	$w->ctx("action",$action);

}

/**
 * Show a Table of Contents by searching
 * through all modules for the file 
 * ./help/<module>_toc.help
 * 
 * @param unknown_type $w
 */
function toc_GET(Web $w) {
	$tocs = getAllHelpTocFiles($w);
	foreach($tocs as $handler => $path) {
		if ($w->auth->allowed($handler.'/index')) {
			$content = file_get_contents($path);
			$title = extractTitle($content);
			$ul[]=Html::a(WEBROOT.'/help/view/'.$handler.'/'.$handler.'_toc',$title?$title:ucfirst($handler));
		}
	}
	$w->out("<h2>Table of Contents</h2>");
	$w->out(Html::ul($ul));
}

/**
 * Contact Helpdesk Dialog
 * 
 * @param unknown_type $w
 */
function contact_GET(Web &$w) {	
}

function contact_POST(Web &$w) {
    $subject = "FLOW HELPDESK: ".$w->request("subject");
    $message = $w->request("message");
    if ($subject || $message) {
        $w->Inbox->notifyRoleUsers("help_contact",$subject,$message,$w->auth->user()->id);
    }
    $w->out("Your message has been sent to the Flow helpdesk.<br/> You will be contacted as soon as possible.");
}

/**
 * Send media files from within
 * a modules help/media folder
 * 
 * @param unknown_type $w
 */
function media_GET(Web &$w) {
	$p = $w->pathMatch("m","f");
	$m = $p['m'];
	$f = $p['f'];
	
	$filename = str_replace("..", "", ROOT."/".$w->getHandlerDir($m).'/help/media/'.$f); 
	$w->sendFile($filename);
}