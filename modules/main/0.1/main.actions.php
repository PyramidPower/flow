<?php 

function main_index_ALL(Web &$w) {
	main_navigation($w, "Flow Home");
	
	$portal = $w->Main->loadPortalTemplates();
	
	$strOut = "Click on the titles below to view the latest <b>Pyramid Power Flow</b> details.<p>";
	$strOut .= "<div id=\"accordion\">";
	
	foreach ($portal as $page) {
		$content = require_once $page;
		$arr = explode("/",$page);
		$title = $w->Main->getTitle($arr[1]);
		
		$strOut .= "<div>";
		$strOut .= "<h3><a href=\"#\">" . $title . "</a></h3>";
		$strOut .= "<div>";
		$strOut .= $content;
		$strOut .= "</div></div>";
	}
	$strOut .=	"</div>";
	
	$w->ctx("portal",$strOut);
}

?>