<?php

function pages_history_GET(Web &$w)
{
	$id = $w->pathMatch("id");
	
	$history = $w->Page->getPageItems("page_history",array('id'=>$id['id']));
	
	$w->ctx("his",$history[0]);
	
	$w->setLayout(null);
}

/**
 * This function is used to auto save page content when user is editing the page, with interval 1 min
 *  
 **/
function pages_timer_POST(Web &$w)
{
	$id = $w->pathMatch("id");
	
	$page = $w->Page->getPage($w,$id['id']);
	
	if ($page)
	{
		$page->body = $_POST['body'];
		$page->update();
		
		echo "Page saved...";
	}
	$w->setLayout(null);
}

function debug($array)
{
	echo "<pre>";
	print_r($array);
	echo "</pre>";
}
?>
