<?php
class PageService extends DbService 
{
	/**
	 * This function return an array of page objects, which is valid for the login user (including public & private pages);
	 * The array consists of current request page, and all its valid sub-pages, hence if you want all valid pages available
	 * in db, you need to call this function recursively according to each return;
	 * 
	 * @param Web $w
	 * @param String $sessions
	 * 			  Current page id that the user is requesting, using this name is because we used to use session variable to maintain the tree structure, now it's moot, treat $sessions == $page_id;
	 * @param boolean $issession
	 * 			   This variable is always equal to false, navigation tree has been abandoned;
	 **/
	function getPages(Web &$w,$sessions,$issession=true)
	{
		$results = array();

		/**
		 * if the user is admin; 
		 **/
		if ($w->auth->user()->hasRole("pages_view"))
		{	
			/**
			 * (NO LONGER AVAILABLE, IGNORE THIS SECTION!)
			 * 
			 * Create the content of the navigation tree;
			 **/
			if ($issession == true)
			{
				foreach ($sessions as $session)
				{
					$objects = $this->getPageItems("page", array('inherit_permissions_page_id'=>$session));
					
					foreach ($objects as $object)
					{
						$results[] = $object;
					}
				}
				return $results;
			}
			
			/**
			 * Create the view of page content; 
			 **/
			else
			{
				$objects = $this->getPageItems("page", array('inherit_permissions_page_id'=>$sessions));
				
				$page = $this->getObject("Page", $sessions);
				
				if ($page)
				{
					array_unshift($objects,$page);
				}
				return $objects;
			}
		}

		/**
		 * if the user is not admin, like reader or editor; 
		 **/
		elseif ($w->auth->user()->hasRole("pages_viewpartial"))
		{	
			$this->getBrokenPagePool($w);
			
			/**
			 * (NO LONGER AVAILABLE, IGNORE THIS SECTION!)
			 * 
			 * Create the content of the navigation tree;
			 **/
			if ($issession == true)
			{
				foreach ($sessions as $session)
				{	
					/**
					 * Read in Public content
					 **/
					$objects = $this->getPageItems("page", array('inherit_permissions_page_id'=>$session,'is_public'=>1));
					
					if ($objects)
					{
						foreach ($objects as $object)
						{
							$results[] = $object;
						}
					}
					
					/**
					 * Read in private content if exist;
					 **/
					$permittedPages = $this->getPermittedPages($w->auth->user()->id);
					
					if ($permittedPages)
					{
						foreach ($permittedPages as $permittedPage)
						{
							$path = explode("_",$permittedPage->path);
							
							$path[] = $permittedPage->id;

							/**
							 * if the user click the link of transition role;
							 **/
							if (in_array($session,$path))
							{	
								$key = array_search($session,$path);
										
								$temp = $this->getObject("Page", $path[$key+1]);
								
								/**
								 * if transition role exist; 
								 **/
								if ($temp)
								{	
									if (!in_array($temp,$results))
									{	
										$results[] = $temp;
									}
								}
								
								/**
								 * if it's permitted role or children of permitter role;
								 **/
								else 
								{	
									$temp = $this->getPageItems("Page",array('inherit_permissions_page_id'=>$session));
									
									foreach ($temp as $sub)
									{
										if (!in_array($sub->id,$_SESSION['pool']))
										{
											if (!in_array($sub,$results))
											{
												$results[] = $sub;
											}
										}
									}
								}
							}
							
							/**
							 * if the user click the link of permitted role or its children or random page out of permitter page's path; 
							 **/
							else
							{	
								$temp = $this->getPageItems("page",array('inherit_permissions_page_id'=>$session));
								
								foreach ($temp as $sub)
								{
									/**
									 * if the children is not listed in the broken pool; 
									 **/
									if (!in_array($sub->id,$_SESSION['pool']))
									{
										$path = explode("_",$sub->path);
										
										if (in_array($permittedPage->id,$path) && !in_array($sub,$results))
										{
											$sub->is_transition = 0;
											
											$results[] = $sub;
										}
									}
								}
							}
						}	
					}				
				}
				return $results;
			}
			
			/**
			 * Create the view of page content; 
			 **/
			else 
			{
				/**
				 * Read in public Content 
				 **/
				$objects = $this->getPageItems("page", array('inherit_permissions_page_id'=>$sessions,'is_public'=>1));

				if ($objects)
				{
					foreach ($objects as $object)
					{
						$results[] = $object;
					}
				}
				
				/**
				 * Add public base page into content view; 
				 **/
				$base = $this->getObject("Page", $sessions);
				
				if ($base && $base->is_public == 1)
				{
					$base->is_transition = 0;
					
					array_unshift($results,$base);
				}
				
				/**
				 * Read in private content 
				 **/
				$permittedPages = $this->getPermittedPages($w->auth->user()->id);
				
				if ($permittedPages)
				{
					foreach ($permittedPages as $permittedPage)
					{
						$path = explode("_",$permittedPage->path);
						
						$path[] = $permittedPage->id;
						
						/**
						 * if the user click the link of transition role;
						 **/
						if (in_array($sessions,$path))
						{	
							$key = array_search($sessions,$path);

							/**
							 * Add private base page into page content; 
							 **/
							$base = $this->getObject("Page", $path[$key]);
							
							if ($base && !in_array($base,$results))
							{
								if ($key == count($path)-1)
								{
									$base->is_transition = 0;
								}
								else 
								{
									$base->is_transition = 1;
								}	
								$results[] = $base;
							}
													
							$temp = $this->getObject("Page", $path[$key+1]);
							
							/**
							 * if transition role exist; 
							 **/
							if ($temp)
							{	
								if (!in_array($temp,$results))
								{	
									if ($key+1 == count($path)-1)
									{
										$temp->is_transition = 0;
									}
									else 
									{
										$temp->is_transition = 1;
									}
									$results[] = $temp;
								}
							}
							
							/**
							 * if permitted role of children of permitted role exist; 
							 **/
							else 
							{	
								$temp = $this->getPageItems("page",array('inherit_permissions_page_id'=>$sessions));
								
								foreach ($temp as $sub)
								{
									if (!in_array($sub->id,$_SESSION['pool']))
									{
										if (!in_array($sub,$results))
										{
											$sub->is_transition = 0;
											
											$results[] = $sub;
										}
									}
								}
							}
						}
						
						/**
						 * if the user click the link of permitted role or its children or random page out of permitted page's path; 
						 **/
						else
						{	
							/**
							 * Add base page if exists; 
							 **/
							$base = $this->getObject("Page", $sessions);
							
							if ($base && !in_array($base->id,$_SESSION['pool']))
							{
								$path = explode("_",$base->path);
								
								if (in_array($permittedPage->id,$path) && !in_array($base,$results))
								{
									$base->is_transition = 0;
									
									$results[] = $base;
								}
							}
							
							$temp = $this->getPageItems("page",array('inherit_permissions_page_id'=>$sessions));
							
							/**
							 * Add page to content view if there are not in the broken page pool; 
							 **/
							foreach ($temp as $sub)
							{
								if (!in_array($sub->id,$_SESSION['pool']))
								{
									$path = explode("_",$sub->path);
									
									if (in_array($permittedPage->id,$path) && !in_array($sub,$results))
									{
										$sub->is_transition = 0;
										
										$results[] = $sub;
									}
								}
							}
						}
					}
				}
				return $results;
			}
		}
	}
	/**
	 * This function return the single valid page that user is requesting;
	 * 
	 * @param Web $w
	 * @param String $id the requesting page id 
	 **/
	function getPage($w,$id)
	{	
		if ($w->auth->user()->hasRole("pages_view"))
		{
			return $this->getObject("Page", $id);
		}
		elseif ($w->auth->user()->hasRole("pages_viewpartial")) 
		{
			$this->getBrokenPagePool($w);
					
			$base = $this->getObject("Page", $id);
			
			if ($base && $base->is_public == 1)
			{
				return $base;
			}
			else 
			{
				$permittedPages = $this->getPermittedPages($w->auth->user()->id);
				
				if ($permittedPages)
				{
					foreach ($permittedPages as $permittedPage)
					{
						$path = explode("_",$permittedPage->path);
						
						$path[] = $permittedPage->id;
						
						if (in_array($id,$path))
						{
							$key = array_search($id,$path);
							
							$base = $this->getObject("Page", $id);
							
							if ($key == count($path)-1)
							{	
								return $base;
							}
						}
						else 
						{
							$base = $this->getObject("Page", $id);
							
							$path = explode("_",$base->path);
							
							if (!in_array($base->id,$_SESSION['pool']) && in_array($permittedPage->id,$path))
							{
								return $base;
							}
						}
					}
				}
			}
		}
	}
	/**
	 * This function return an array of page objects that is valid for current login user or valid for all the groups
	 * that the user has been attached to, and this is an recursive function;
	 * 
	 * @param String $user_id the user id of current login user;
	 **/
	function getPermittedPages($user_id)
	{
		$results = array();
		
    	$option['user_id'] = $user_id;
    	
    	$userPages = $this->getObjects("Page_user", $option);
    	//if page is assigned to user;
    	if ($userPages)
    	{
    		foreach ($userPages as $userPage)
    		{
    			$page = $userPage->getPage();
    			
    			if ($page && !in_array($page, $results))
    				$results[] = $page;
    		}
    	}
    	//add pages which are assigned to user's parent group;
        $groupUsers = $this->w->auth->getGroupMembers(null, $user_id);
    	
    	if ($groupUsers)
    	{
    		//if page is assigned to user's group;
    		foreach ($groupUsers as $groupUser)
    		{
    			$pages = $this->getPermittedPages($groupUser->group_id);
    			
    			if ($pages)
    				$results = array_merge($results, $pages);
    		}
    	}
    	return $results;
	}
	/**
	 * This function return items other than page table, no permission checking;
	 * 
	 * @param String $db table name (page_user, page_history, page_comment)
	 * @param Array $array parameters, e.g. id=>$id
	 **/
	function getPageItems($db,$array)
	{
		$rows = $this->_db->get($db)->where($array)->fetch_all();
		
		$objects = $this->fillObjects($db, $rows);
		
		return $objects;
	}
	/**
	 * (NO LONGER AVAILABLE, IGNORE THIS SECTION!)
	 **/
	function getInheritancePages($parent_id)
	{
		$id=$parent_id;
		
		$results = array();
		
		$rows = $this->_db->sql("SELECT * FROM page WHERE path REGEXP '_".$parent_id."(_?$|_+)'")->fetch_all();
		
		$objects = $this->fillObjects("Page", $rows);
		
		foreach ($objects as $object)
		{
			$results[] = $object->subject;
		}
		return $results;
	}
	/**
	 * (NO LONGER AVAILABLE, IGNORE THIS SECTION!)
	 **/
	// return an array of premissable pages for display as a 'select'
	function getUserPageTitles() {
		// set pages array
		$pages = array();
		
		// get all pages
		$allpages = $this->getObjects("Page", array("is_deleted"=>0));
		
		// if pages, create array using parent as value
		// we'll use thus value as our 'level' in getPages function
		if ($allpages) {
			foreach ($allpages as $page) {
				$arr[] = $page->inherit_permissions_page_id;
			}

			// if we have pages, getPages for each 'level'
			// returns the page of that ID plus all its child pages, if permissions allow
			if ($arr) {
				foreach ($arr as $num) {
					$page = $this->getPages($this->w,$num,false);
					// merge returned pages
					$pages = array_merge($pages,$page);
				}
			}
			
			// pages is an object, but we want unique records only.
			// convert to array in form appropriate for display as 'select'
			foreach ($pages as $pg) {
				$p[$pg->id] = array($pg->subject,$pg->id);
			}
			return $p;
		}
	}
	
	function getPageIdBySubject($subjects)
	{
		$results = array();
		
		foreach ($subjects as $subject)
		{
			$row = $this->_db->get("page")->where("subject",$subject)->fetch_row();
			
			$results[] = $row['id'];
		}
		return $results;
	}
	/**
	 * This function return the valid role for the given page by calling sub-function checkRoleRecursively();
	 * 
	 * @param Web $w
	 * @param String $id the id of given page;
	 **/
	function getPageRole($w,$id)
	{
		$page = $this->getPage($w,$id);
		
		$permittedPages = $this->getPermittedPages($w->auth->user()->id);
		
		if ($permittedPages)
		{
			foreach ($permittedPages as $permittedPage)
			{
				$path = explode("_", $page->path);
				
				$path[] = $page->id;
				
				if (in_array($permittedPage->id, $path))
				{
					$role = $this->checkRoleRecursively($permittedPage->id, $w->auth->user()->id);
					
					return $role;
				}
			}
		}
	}
	/**
	 * This is a sub-function of getPageRole(), to get group role first, then use individual role to override it;
	 * 
	 * @param String $page_id the id of given page;
	 * @param String $user_id the id of user;
	 **/
	function checkRoleRecursively($page_id, $user_id)
	{
        $groupUsers = $this->w->auth->getGroupMembers(null, $user_id);
    
    	if ($groupUsers)
    	{
    		foreach ($groupUsers as $groupUser)
    		{
    			$hasRole = $this->checkRoleRecursively($page_id, $groupUser->group_id);
    			
    			if ($hasRole)
    				$role = $hasRole;
    		}
    	}
		//override permissions for individual;    	
		$pageUser = $this->getObject("Page_user", array('page_id'=>$page_id, 'user_id'=>$user_id));
		
		//if the page is assigned to a user;
		if ($pageUser)
		{
			$role = $pageUser->role_id == 1 ? "pages_editor" : "pages_reader";
		}
    	return $role;
	}
	/**
	 * (NO LONGER AVAILABLE, IGNORE THIS SECTION!)
	 **/
	function getRoleById($id)
	{
		$row = $this->_db->get("page_select")->where('id',$id)->fetch_row();
		
		$explode = explode("_",$row['value']);

		return $explode[1];
	}
	/**
	 * (NO LONGER AVAILABLE, IGNORE THIS SECTION!)
	 **/
	function getBrokenPagePool(Web &$w)
	{
		$page_users = $this->getPageItems("page_user",array('user_id'=>$w->auth->user()->id));
		
		foreach ($page_users as $page_user)
		{
			/**
			 * release pages which were broken, and now added again  
			 **/
			$children = $this->getPageItems("page", array('inherit_permissions_page_id'=>$page_user->page_id));
			
			if ($children)
			{
				foreach ($children as $child)
				{
					if ($_SESSION['pool'] && in_array($child->id,$_SESSION['pool']))
					{
						$key = array_search($child->id,$_SESSION['pool']);
						
						unset($_SESSION['pool'][$key]);
					}
				}
			}
			
			/**
			 * means the user wants to break part or all of its children from inheritance; 
			 **/
			if ($page_user->broken_inheritance_children)
			{
				$break_ids = explode(",",$page_user->broken_inheritance_children);
				
				foreach ($break_ids as $break_id)
				{
					if (!$_SESSION['pool'])
					{
						$_SESSION['pool'][] = $break_id;
					}
					elseif ($_SESSION['pool'] && !in_array($break_id,$_SESSION['pool']))
					{
						$_SESSION['pool'][] = $break_id;
					}
					
					$rows = $this->_db->get("page")->where(array('inherit_permissions_page_id'=>$break_id))->fetch_all();

					if ($rows)
					{
						foreach ($rows as $row)
						{
							if (!in_array($row['id'],$_SESSION['pool']))
							{
								$_SESSION['pool'][] = $row['id'];	
							}
						}
					}
				}
			}
		}
		
		if (!$_SESSION['pool'])
		{
			$_SESSION['pool'][] = -1;
		}
	}
	/**
	 * This function delete related table record, for page table, restore the hierarchy structure after a record is deleted;
	 * 
	 * @param Web $w
	 * @param String $table the name of the table where you want to perform delete action;
	 * @param Array $array parameters to perform delete action;
	 **/
	function deleteItem($w,$table,$array)
	{
		if ($table == "page_user" || $table == "page_comment")
		{
			$this->_db->delete($table)->where($array)->execute();
		}
		else
		{
			$base = $this->getObject("Page", $array['id']);
			
			$this->_db->delete($table)->where($array)->execute();
			
			//Change all descendents' inherit_permissions_page_id;
			$objects = $this->getPageItems("page", array('inherit_permissions_page_id'=>$base->id));
			
			if ($objects)
			{
				foreach ($objects as $object)
				{
					$object->inherit_permissions_page_id = $base->inherit_permissions_page_id;
					$object->update();
				}
			}
			
			//Change all descendents' path;
			$rows = $this->_db->sql("SELECT * FROM page WHERE path REGEXP '_".$base->id."(_?$|_+)'")->fetch_all();
			
			if ($rows)
			{
				$objects = $this->fillObjects("Page", $rows);
				
				foreach ($objects as $object)
				{
					$object->path = str_replace("_".$base->id,"",$object->path);
					$object->update();
				}
			}
	
			//Change is_parent attribute if necessary
			$rows = $this->_db->get("page")->where('inherit_permissions_page_id',$base->inherit_permissions_page_id)->fetch_all();
			
			if (!$rows)
			{
				$object = $this->getObject("Page", $base->inherit_permissions_page_id);
				$object->is_parent = 0;
				$object->update();
			}
			
            $mso = new Inbox_message($this->w);
            $mso->message = "The page \"".$base->subject."\" is deleted by: ".$this->w->auth->user()->getContact()->getFullName()." (at ".date("Y-m-d H:m:i").")";
            $mso->insert();
            
            $this->w->Inbox->addMessage("Page is deleted", $mso, $base->owner_id, null);
            
            if ($base->owner_id != $base->creator_id)
            	$this->w->Inbox->addMessage("Page is deleted", $mso, $base->creator_id, null);
		}
	}
	/**
	 * The html template for adding and editing a page;
	 * 
	 * @param String $subject the subject of the page;
	 * @param String $body the body of the page;
	 * @param Boolean $public if this is public or private page, default to private page;
	 **/
	function pageTemplate($subject=null,$body=null,$public=0)
	{
		$form['Page Content'] = array(array(array("Subject","text","subject",$subject),
											array("Public","checkbox","public",$public)),
								  	  array(array("Body","textarea","page_body",$body,100,26)));
    	return $form;
	}
	/**
	 * The html template for comment;
	 * 
	 *  @param String $page_id the current page id;
	 *  @param String $parent_id the comment which you would lie to reply to;
	 *  @param String $quote the quote to which you would like to reply;
	 *  @param dt_modified $dt_modified the date and time of modification;
	 **/
	function commentTemplate($page_id, $parent_id, $quote=null, $dt_modified=null)
	{
		$form['Comment to this page'] = array(array(array("","textarea","comment",null,60,20),
											  		array("","hidden","page_id",$page_id),
											  		array("","hidden","parent_id",$parent_id),
											  		array("","hidden","quote",$quote),
											  		array("","hidden","dt_modified",$dt_modified)));
		return $form;
	}
	/**
	 * This function get a single comment object;
	 * 
	 * @param String $id the id of the comment;
	 **/
	function getCommentItem($id)
	{
		$row = $this->_db->get('page_comment')->where('id',$id)->fetch_row();
		
		$result = $this->getObjectFromRow("page_comment", $row);
		
		return $result;
	}
	/**
	 * (NO LONGER AVAILABLE, IGNORE THIS SECTION!)
	 **/
	function getSelect($type)
	{
		$rows = $this->_db->get("page_select")->where("type",$type)->fetch_all();
		
		if ($rows)
		{
			$results = array();
			
			foreach ($rows as $row)
			{
				$value[] = $row['value'];
				
				$id[] = $row['id'];
			}
			
			$results[] = $value;
			
			$results[] = $id;
			
			return $results;	
		}
	}
	/**
	 * This function parse markup when display the page;
	 * 
	 * @param Web $w
	 * @param DbObject $object the page object to display;
	 * @param String $id the id of the page;
	 **/
	function onView($w,$object,$id)
	{	
		$pattern = '[\[\[(\w+(\|\w+=.+(\.\w+)?)*)\]\]]';
		
		$parser = new Page_parser($w);
		
		$result = $parser->parseCommand(__FUNCTION__,$object->body,$pattern,$id);
		
		$object->body = $result;
		
		return $object;
	}
	/**
	 * This function parse markup when save the page;
	 * 
	 * @param Web $w
	 * @param String $body the body of the page;
	 * @param String $id the id of the page;
	 **/
	function onSave($w,$body,$id)
	{
		$pattern = '[\{\{(\w+(\|\w+=.+)*)\}\}]';
		
		$parser = new Page_parser($w);
		
		$result = $parser->parseCommand(__FUNCTION__,$body,$pattern,$id);
		
		$body = $result;
		
		return $body;
	}
	
	function debug($array)
	{
		echo "<pre>";
		print_r($array);
		echo "</pre>";
	}
}

