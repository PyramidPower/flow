<?php
class PagesHelper
{	
	function loadPageContext(Web &$w,$level)
	{
		$w->ctx("level", $level);
		
		$pageData = $w->Page->getPages($w,$level,false);
		
		//get IDs of page list to check if the requested page is in the list
		if ($pageData)
		{
			foreach ($pageData as $temp)
			{
				$pageIdPool[] = $temp->id;
			}
		}
		
		//if the requested page is in the list, then return it and the rest sub pages;
		if ($pageIdPool && in_array($level,$pageIdPool))
		{
			$key = array_search($level,$pageIdPool);
			
			$page = $pageData[$key];
			
			unset($pageData[$key]);
			
			if ($page->is_transition == 0)
			{
				$w->ctx("page", $w->Page->onView($w,$page,$level));
				
				PagesHelper::loadCommentContext($w, $level);
				
				PagesHelper::loadHistoryContext($w, $level);
				
				PagesHelper::loadInvitationHistoryContext($w, $level);
				
				PagesHelper::loadAttachmentsContext($w, $level);
				
				PagesHelper::loadAdminContext($w,$page);
			}
			$w->ctx("pageLists", $pageData);
			
			$title = $page->subject;
		}
		
		//if the request page is not in the list, only show the sub pages;
		else 
		{
			$w->ctx("pageLists", $pageData);
			
			$title = $pageData[0]->subject;
		}
		
		return $title;
	}
	
	function loadCommentContext(Web &$w, $id)
	{
		$results = $w->Page->getPageItems("page_comment",array('page_id'=>$id));
	
		if ($results)
		{
			$w->ctx("comments", $results);		
		}
		$w->ctx("com_size", "Comments (".count($results).")");
	}
	
	function loadHistoryContext(Web &$w, $id)
	{
		$history = array(array("Editor","Date & Time","Operation"));
		
		$records = $w->Page->getPageItems("page_history",array('page_id'=>$id));
		
		if ($records)
		{
			foreach ($records as $record)
			{
				$array = array();
		
				$array[] = $record->getCreator()->getFullName();
				$array[] = date('d/m/Y H:i',$record->dt_created);
				$array[] = Html::box(WEBROOT."/pages/history/$record->id","View Page",true);
				
				$history[] = $array;
			}
		}
		
		$w->ctx("history", Html::table($history, null, "tablesorter", true));
	}
	
	function loadInvitationHistoryContext(Web &$w,$page_id)
	{
		$results = $w->Page->getPageItems("page_user",array('page_id'=>$page_id));
		
		$table = array(array("Invitee","Role","Invite Date","Operation"));
		
		if ($results)
		{
			foreach ($results as $result)
			{
				$array = array();
				
				$user = $w->auth->getUser($result->user_id);
				
				if ($user->is_group == 1)
				{
					$body = "";
					
					$groupMembers = $w->auth->getGroupMembers($user->id);
					
					if ($groupMembers)
					{
						foreach ($groupMembers as $groupMember)
						{
							$body .= $groupMember->getUser()->is_group == 1 ? "<u>".$groupMember->getUser()->login."</u>" : $groupMember->getUser()->getContact()->getFullName()."<br/>";
						}
					}
					$username = "<span title=\"cssbody=[addressBody] cssheader=[addressHeader] header=[Group Members] body=[$body] fade=[on]\"><u>".strtoupper($user->login)."</u></span>";
				}
				else 
				{
					$username = $user->getContact()->getFullName();
				}
				$array[] = $username;
				$array[] = $result->role_id == 1 ? "Editor" : "Reader";
				$array[] = date('d/m/Y H:i', $result->dt_created);
				$array[] = Html::a("/pages/invitation/$result->page_id/$result->user_id","Cancel");
				
				$table[] = $array;
			}
		}
		$w->ctx("invHistory", Html::table($table, null, "tablesorter", true));
	}
	
	function loadAttachmentsContext(Web &$w,$page_id)
	{
		$attachments = array(array("Preview","Title","Description","Upload Time","Uploaded By","Operation"));	
			
		$results = $w->File->getAttachments("page",$page_id);
	
		if ($results)
		{
			foreach ($results as $result)
			{	
				if (file_exists(FILE_ROOT.$result->fullpath))
				{
					$array = array();
					
					if ($result->isImage())
					{
						$preview = "<a href=\"$webroot/file/atthumb/$result->id/800/600/a.jpg\" rel=\"gallery\">$result->filename</a>";
					}
					else
					{
						$preview = Html::a("$webroot/file/atfile/$result->id",$result->filename);
					}
					
					$array[] = $preview;
					$array[] = $result->title;
					$array[] = $result->description;
					$array[] = date('d/m/Y H:i',$result->dt_modified);
					$array[] = $w->auth->getUser($result->modifier_user_id)->getFullName();
					
					if ($w->auth->user()->hasRole("pages_delete"))
					{
						$array[] = Html::a($webroot."/file/atdel/".$result->id."/pages+index+level+$page_id#3","[Delete]",null,null,"Delete this attachment?");	
					}
					else 
					{
						$array[] = null;
					}
					
					$attachments[] = $array;
				}
			}
		}
		$w->ctx("attachments",Html::table($attachments, null, "tablesorter", true));
		$w->ctx("att_size", "Attachments (".(count($attachments) - 1).")");
	}
	
	function loadAdminContext(Web &$w,$page)
	{
		$public = $page->is_public == 0 ? "Private" : "Public";
		
		$adminForm["Owner Info"] = array(array(array("Creator Name","text","-creatorName",$page->getCreator()->getFullName()),
											   array("Open To","static","openTo",$public)),
										 array(array("Owner Name","text","-ownerName",$page->getOwner()->getFullName())));	
	
		// Only pages_admin can change owner of the page;
		if ($w->auth->user()->hasRole("pages_invitation") || $w->auth->user()->id == $page->owner_id)
		{
			$adminForm["Owner Info"][1][] = array("Change To","autocomplete","changeTo",null,$w->auth->getUsers());
		}						   
											   
		if ($w->auth->user()->hasRole("pages_viewpartial"))
		{
			$role = $w->Page->getPageRole($w,$page->id);
			
			if ($role)
			{
				$temp = explode("_",$role);
				
				$userRole = $temp[1];
			}
			else 
			{
				if ($page->is_public == 1)
				{
					$userRole = 'reader';
				}
			}
		}
		elseif ($w->auth->user()->hasRole("pages_view"))
		{
			$userRole = 'admin';
		}
		
		$w->ctx("userRole",$userRole);
		
		$adminForm["User Info"] = array(array(array("User Name","static","userName",$w->auth->user()->getFullName()),
											  array("Has Role","static","hasRole",$userRole)));
											  
		$parentPage = $page->getParent();
		
		$inheritsTo = $parentPage ? $parentPage->subject : "Root Folder";
		
		$adminForm["Page Info"] = array(array(array("Inherits To","static","inheritsTo",$inheritsTo),
											  array("Create Time","static","createTime",date('d/m/Y H:i',$page->dt_created)),
											  array("","hidden","pageSubject",$page->subject),
											  array("","hidden","pageId",$page->id)));	
											  
		if ($w->auth->user()->hasRole("pages_invitation") || $w->auth->user()->id == $page->owner_id)
		{
			$select = array(array("pages_editor",1),array("pages_reader",2));
			
			$adminForm["Send Invitation"] = array(array(array("Send To","autocomplete","sendTo",null,$w->auth->getUsers()),
																					  array("As Role","select","asRole",null,$select)));
														
			$w->ctx("adminForm", Html::multiColForm($adminForm,$w->localUrl("/pages/invitation"),"POST","Submit"));	
		}
		else 
		{							
			$w->ctx("adminForm", Html::multiColForm($adminForm));	
		}
	}
}