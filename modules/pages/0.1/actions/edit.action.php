<?php
function pages_edit_GET(Web &$w)
{
	$id = $w->pathMatch('id');
	
	if ($w->auth->user()->hasRole("pages_edit") || $w->Page->getPageRole($w,$id['id']) == "pages_editor")
	{
		$result = $w->Page->getPage($w,$id['id']);
		
		$form = $w->Page->pageTemplate($result->subject,$result->body,$result->is_public);
		
		// If the login user is not the owner of the page, then he/she can not change public/private attribute;
		if ($w->auth->user()->id != $result->owner_id)
		{
			unset($form['Page Content'][0][1]);	
		}
		
		$view = Html::multiColForm($form,$w->localUrl("/pages/edit/".$id['id']),"POST","Save",null,null,array('cancel'=>'Go Back'));
		
		$w->ctx('id',$id['id']);
		$w->ctx('editForm',$view);	
	}
	else 
	{
		$w->error("You don't have permission to edit this page!", "/pages/index/level/".$id['id']);
	}
}

function pages_edit_POST(Web &$w)
{   
	$id = $w->pathMatch('id');
	
	if ($w->auth->user()->hasRole("pages_edit") || $w->Page->getPageRole($w, $id['id']) == "pages_editor")
	{
		$page = $w->Page->getPage($w, $id['id']);
		
		$page_history = new Page_history($w);
		$page_history->page_id = $page->id;
		$page_history->subject = $page->subject;
		$page_history->body = $page->body;
		$page_history->is_public = $page->is_public;
		$page_history->insert();
		
		$page->subject = $_REQUEST['subject'];
		$page->body = $w->Page->onSave($w,$_REQUEST['page_body'],$id['id']);
		$page->is_public = isset($_REQUEST['public']) ? 1 : 0;
		$page->update();
		
	    $w->msg("Page updated.","/pages/index/level/".$id['id']);
	}
	else 
	{
		$w->error("You don't have permission to edit this page!", "/pages/index/level/".$id['id']);
	}
}
?>