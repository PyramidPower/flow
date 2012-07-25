<?php
function getHelpFilePath(Web &$w,$handler,$subhandler,$action) {
	$help_file = $w->getHandlerDir($handler)."/help".($subhandler ? "/".$subhandler : "")."/".$action.".help";
	if (file_exists($help_file)) {
		return $help_file;
	}
	return null;
}

function getHelpFileContent(Web &$w,$handler,$subhandler,$action) {
	$p = getHelpFilePath($w,$handler, $subhandler, $action);
	if ($p) {
		return file_get_contents($p);
	}
	return null;
}

function extractTitle($content) {
	if (preg_match_all("/\[\[title\|(.*?)\]\]/", $content, $matches)) {
		return $matches[1][0];
	}		
}

/**
 * Go through all registered handlers and find the file
 * <handler>/help/<handler>_toc.help
 */
function getAllHelpTocFiles(Web &$w) {
	foreach ($w->handlers() as $h) {
		$p = getHelpFilePath($w, $h,null,$h."_toc");
		if ($p) {
			$toc[$h]=$p;
		} 
	}
	return $toc;
}

/**
 * Remove restricted paragraphs from help file
 * So that only parts are left which the user is
 * allowed to see
 * 
 * restricted parts are marked as:
 * 
 * [[restricted|role1,role2,role3...]]
 * restricted text paragraph
 * [[endrestricted]]
 * 
 * @param Web $w
 * @param string $content
 */
function pruneRestricted($w,$content) {
	$c = "";
	$restricted = false;
	foreach (explode("\r\n", $content) as $l) {
		if (preg_match_all("/\[\[restricted\|(.*?)\]\]/", $l, $matches)) {
			$roles = explode(',',$matches[1][0]);
			if (!$w->auth->user()->hasAnyRole($roles)) {
				$restricted = true;
			}
		} else if (startsWith($l, "[[endrestricted]]")) {
			$restricted = false;	 
		} else if (!$restricted) {
			$c .= $l."\r\n";
		}
	}
	return $c;
}

function helpMarkup($content,$module) {
	$content = str_replace("\r\n\r\n", "<p>", $content);
	$content = preg_replace("/\[\[title\|(.*?)\]\]/", "<h2>\\1</h2>", $content);
	$content = preg_replace("/\[\[button\|(.*?)\]\]/", "<button>\\1</button>", $content);
	$content = preg_replace("/\[\[help\|(.*?)\|(.*?)\]\]/",'<a href="'.WEBROOT.'/help/view/\\1">\\2</a>', $content);
	
	$content = replaceImage($content,$module);
	$content = replaceVideo($content,$module);
	return $content;
}

function replaceImage($content, $module) {
	$img = '<img src="'.WEBROOT.'/help/media/'.$module.'/\\1" border="0"/>';
	return preg_replace("/\[\[img\|(.*?)\]\]/", $img, $content);			
}

function replaceVideo($content,$module) {
	$video = "<span style=\" -moz-border-radius: 6px; 
			  	  			     -moz-box-shadow: 0 0 14px #123;
			  	  			     display: -moz-inline-stack;
			  	  			     display: inline-block;
         			  		     border: 2px solid black;\">";
	$video .= '<a href="'.WEBROOT.'/help/media/'.$module.'/\\2" style="display:block;width:700px;height:394px;" id="video\\1"></a>'.$end;
	$video .= '<script language="JavaScript">flowplayer("video\\1", "'.WEBROOT.'/js/flowplayer/flowplayer-3.2.5.swf", {clip: {autoPlay:false, autoBuffering:true, scaling:"fit"}});</script>';
	$video .= "</span>";
	return preg_replace("/\[\[video\|(.*?)\|(.*?)\]\]/", $video, $content);			
}
