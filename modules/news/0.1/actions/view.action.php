<?php
function view_GET(Web &$w) {
    $p = $w->pathMatch("id","print");
    $item = $w->service("News")->getNewsItem($p['id']);
    
    if ($item) {
	    $w->ctx("item",$item);
    	if ($p['print']) {
	        $w->setLayout("print");
    	} else {
        	news_navigation($w,"News");
	    }
    	$w->service("News")->markItemRead($p['id']);
    }
    else {
        $w->msg("News Article not available","/news/index");
    }
}
