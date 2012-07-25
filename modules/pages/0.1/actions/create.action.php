<?php
function pages_create_GET(Web &$w)
{
	$level = $w->pathMatch("level","id");
	
	if ($w->auth->user()->hasRole("pages_add") || $w->Page->getPageRole($w, $level['id']) == "pages_editor")
	{
		$form = $w->Page->pageTemplate();
	
		if ($level['id'])
		{
			$id = $level['id'];
		}
		$view = Html::multiColForm($form,$w->localUrl("/pages/create/level/".$id),"POST","Save",null,null,array('cancel'=>'Go Back'));
		
		$w->ctx('id',$id);
		
		$w->ctx('createForm',$view);
	}
	else
	{
		$w->error("You don't have permission to create new page!", "/pages/index/level/".$level['id']);
	}
}

function pages_create_POST(Web &$w)
{
	$level = $w->pathMatch("level","id");
	
	if ($w->auth->user()->hasRole("pages_add") || $w->Page->getPageRole($w, $level['id']) == "pages_editor")
	{
		$page = new Page($w);
		
		$page->owner_id = $w->auth->user()->id;
		$page->subject = $_REQUEST['subject'];
		$page->body = $_REQUEST['page_body'];
		$page->inherit_permissions_page_id = $level['id'] ? $level['id'] : 0;
		
		//concatenate the parent's path with page's id to form it's own path; if the parent is root, then only store 0 as its path
		$page->path = $level['id'] ? $w->Page->getPage($w,$level['id'])->path."_".$level['id'] : 0;
		$page->insert();
		
		$parent_page = $w->Page->getPage($w,$level['id']);
		
		if ($parent_page)
		{
			$parent_page->is_parent = 1;
			$parent_page->update();
			
			$is_public = $parent_page->is_public == 0 ? 0 : $_REQUEST['public'];
		}
		else 
		{
			$is_public = $_REQUEST['public'];
		}
		
		$page->is_public = $is_public;
		$page->update();
		
	    $w->msg("New Page Added!","/pages/index/level/".$level['id']);
	}
	else
	{
		$w->error("You don't have permission to create new page!", "/pages/index/level/".$level['id']);
	}
}
?>