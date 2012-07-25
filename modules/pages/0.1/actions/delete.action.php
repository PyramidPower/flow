<?php
function pages_delete_ALL(Web &$w)
{
	$id = $w->pathMatch("page_id","comment_id");
	
	if ($w->auth->user()->hasRole("pages_delete") || $w->Page->getPageRole($w, $id['page_id']) == "pages_editor")
	{
		if($id['comment_id'])
		{
			$w->Page->deleteItem($w,"page_comment",array('id'=>$id['comment_id']));
	
		    $w->msg("Comment deleted!", "/pages/index/level/".$id['page_id']."#2");
		}
		else
		{
			$w->Page->deleteItem($w,"page",array('id'=>$id['page_id']));
			
		    $w->msg("Page deleted!", "/pages/index");
		}
	}
	else 
	{
		$w->error("You don't have permission to delete things!", "/pages/index/level/".$id['page_id']);
	}
}
?>