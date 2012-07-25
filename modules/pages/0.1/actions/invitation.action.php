<?php
function pages_invitation_GET(Web &$w)
{
	$receive = $w->pathMatch('page_id','user_id');
	
	if ($w->auth->user()->hasRole("pages_invitation") || $w->Page->getPageRole($w, $receive['page_id']) == "pages_editor")
	{
		//remove invitation record record from page_user table;
		$w->Page->deleteItem($w, "page_user", array('page_id'=>$receive['page_id'],'user_id'=>$receive['user_id']));
		
		$w->msg("Page invitation has been cancelled!","/pages/index/level/".$receive['page_id']."#5");
	}
	else 
	{
		$w->error("You don't have permission to cancel invitation!", "/pages/index/level/".$receive['page_id']."#5");
	}
}

function pages_invitation_POST(Web &$w)
{
	if ($w->auth->user()->hasRole("pages_invitation") || $w->Page->getPageRole($w, $_REQUEST['pageId']) == "pages_editor")
	{
		$receiver_id = $_REQUEST['sendTo'];

		$subject = "Invitation from \"$_REQUEST[ownerName]\" for page \"$_REQUEST[pageSubject]\"";
		
		$link = Html::a($webroot."/pages/index/level/".$_REQUEST['pageId'],$_REQUEST['pageSubject']);
		
		$character = explode("_",$_REQUEST['asRole']);
	
		$message = "<strong>$_REQUEST[ownerName]</strong> has send you an invitation to use page $link as <strong>$character[1]</strong>!";

		$role_id = $_REQUEST['asRole'];
		
		//store broken inheritance page's id
		$break_id = $_REQUEST['break'] ? implode(",",$w->Page->getPageIdBySubject($_REQUEST['break'])) : null;
		
		if ($receiver_id && $role_id)
		{	
	      	$page_user = new Page_user($w);
			$page_user->page_id = $_REQUEST['pageId'];
			$page_user->user_id = $receiver_id;
			$page_user->role_id = $role_id;
			$page_user->broken_inheritance_children = $break_id;
			$page_user->insert();
			
	    	$w->Inbox->addMessage($subject, $message, $receiver_id);
	    	
	        $w->msg("Invitation has been sent out!","/pages/index/level/".$_REQUEST['pageId']."#6");
		}
		elseif ($_REQUEST['changeTo'])
		{
			$page = $w->Page->getPage($w,$_REQUEST['pageId']);
			$page->owner_id = $_REQUEST['changeTo'];
			$page->update();
			
	        $w->msg("Owner has been changed!","/pages/index/level/".$_REQUEST['pageId']."#6");
		}
	}
	else 
	{
		$w->error("You don't have permission to manage this page!", "/pages/index/level/".$_REQUEST['pageId']."#6");
	}
}
?>