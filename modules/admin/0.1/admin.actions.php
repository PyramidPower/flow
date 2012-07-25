<?php
// $Id: admin.actions.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

function index_ALL(Web &$w) {
    admin_navigation($w,"Dashboard");
    $w->ctx("currentUsers",$w->Admin->getLoggedInUsers());
}

function users_GET(Web &$w) {
    admin_navigation($w,"Users");

    $users = array(array("Login","First Name","Last Name","Admin","Active","Created","Last Login","Operations"));
    $result = $w->db->sql("select user.id as id,login,firstname,lastname,is_admin,is_active,user.dt_created as dt_created,dt_lastlogin from user left join contact on user.contact_id = contact.id where user.is_deleted = 0 AND user.is_group = 0")->fetch_all();
    foreach ($result as $user) {
        $line = array();
        $line[]=$user['login'];
        $line[]=$user['firstname'];
        $line[]=$user['lastname'];
        $line[]=$user['is_admin'] ? "X" : "";
        $line[]=$user['is_active'] ? "X" : "";
        $line[]=$user['dt_created'];
        $line[]=$user['dt_lastlogin'];
        $view = Html::box($w->localUrl("/admin/useredit/".$user['id']."/box"),"Edit",true)."&nbsp;";
		$view .= Html::b("/admin/permissionedit/".$user['id'],"Permissions")."&nbsp;";
        if ($user['is_active']) {
            $view .= Html::b($w->localUrl("/admin/useract/".$user['id']."/0"),"Suspend","Are you sure to suspend this user?");
        } else {
            $view .= Html::b($w->localUrl("/admin/useract/".$user['id']."/1"),"Activate","Are you sure to activate this user?");
        }
        $view .= "&nbsp;".Html::b($w->localUrl("/admin/userdel/".$user['id']),"Delete","Are you sure to delete this user?")."&nbsp;";
        $line[]=$view;
        $users[]=$line;
    }
    $w->ctx("table",Html::table($users,null,"tablesorter",true));
}

/**
 * Display a list of all groups which are not deleted
 *
 * @param <type> $w
 */
function groups_GET(Web &$w) 
{
    admin_navigation($w,"Groups");
    
    $table = array(array("Title","Parent Groups","Operations"));
    
    $groups = $w->auth->getGroups();
    
    if ($groups)
    {
    	foreach ($groups as $group)
    	{
        	$ancestors = array();
    		    			
    		$line = array();
    		
    		$line[] = $w->auth->user()->is_admin ? Html::box($w->localUrl("/admin/groupedit/".$group->id),"<u>".$group->login."</u>") : $group->login;
    		//if it is a sub group from other group;
    		$groupUsers = $group->isInGroups();
    		
    		if ($groupUsers)
    		{
    			foreach ($groupUsers as $groupUser)
    			{
    				$ancestors[] = $groupUser->getGroup()->login;
    			}
    		}
    		$line[] = count($ancestors) > 0 ? "<div style=\"color:green;\">".implode(", ", $ancestors)."</div>" : "";
    		
        	$operations = Html::b("/admin/moreInfo/".$group->id,"More Info")."&nbsp;";
        	
        	if ($w->auth->user()->is_admin)
        		$operations .= Html::b("/admin/groupdelete/".$group->id,"Delete","Are you sure you want to delete this group?")."&nbsp;";
	        	
        	$line[] = $operations;
        	
        	$table[] = $line;
    	}
    }
    
    if ($w->auth->user()->is_admin)
    {
    	$w->out(Html::box("/admin/groupadd", "New Group", true));
    }
    $w->out(Html::table($table,null,"tablesorter",true));
}

/**
 * Display member and permission infomation
 *
 * @param <type> $w
 */
function moreInfo_GET(Web &$w)
{
	$option = $w->pathMatch("group_id");
	
	admin_navigation($w, $w->auth->getUser($option['group_id'])->login);
	
	if ($w->auth->user()->is_admin || $w->auth->getRoleForLoginUser($option['group_id'], $w->auth->user()->id) == "owner")
	{
		$w->ctx("addMember", Html::box("/admin/groupmember/".$option['group_id'],"New Member",true)."&nbsp;");
	}
	$w->ctx("editPermission", Html::b("/admin/permissionedit/".$option['group_id'],"Edit Permissions")."&nbsp;");
	
	//fill in member table;
	$table = array(array("Name","Role","Operations"));
		
	$groupMembers = $w->auth->getGroupMembers($option['group_id']);
			
	if ($groupMembers)
	{
		foreach ($groupMembers as $groupMember)
		{
			$line = array();
			
			$style = $groupMember->role == "owner" ? "<div style=\"color:red;\">" : "<div style=\"color:blue;\">";
			
			$name = $groupMember->getUser()->is_group == 1 ? $groupMember->getUser()->login : $groupMember->getUser()->getContact()->getFullName();
			
			$line[] = $style.$name."</div>";
			$line[] = $style.$groupMember->role."</div>";
			
			if ($w->auth->user()->is_admin || $w->auth->getRoleForLoginUser($option['group_id'], $w->auth->user()->id) == "owner")
			{
				$line[] = Html::a("/admin/memberdelete/".$option['group_id']."/".$groupMember->id,"Delete",null,null,"Are you sure you want to delete this member?");
			}
			else 
			{
				$line[] = null;
			}
			$table[] = $line;
		}
	}
    $w->ctx("memberList", Html::table($table,null,"tablesorter",true));
}

function permissionedit_GET(Web $w)
{
	$option = $w->pathMatch("group_id");
	
	$user = $w->auth->getUser($option['group_id']);
	
	$userName = $user->is_group == 1 ? $user->login : $user->getContact()->getFullName();
	
    admin_navigation($w,"Permissions - ".$userName);
	
    //fill in permission tables;
    $groupUsers = $w->auth->getUser($option['group_id'])->isInGroups();
    
    if ($groupUsers)
    {
    	foreach ($groupUsers as $groupUser)
    	{
    		$grs = $groupUser->getGroup()->getRoles();
    		
    		foreach ($grs as $gr)
    		{
    			$groupRoles[] = $gr;
    		}
    	}
    }

    $roles = $w->auth->getAllRoles();
    
    foreach ($roles as $role)
    {
    	$characters = explode("_", $role);
    	
    	if (count($characters) == 1)
    		array_unshift($characters, "admin");
    		        
    	$result[$characters[0]][] = $characters[1];
    }
    
    foreach ($result as $module=>$characters)
    {
    	$characters = array_chunk($characters, 4);
    	
    	foreach ($characters as $level=>$character)
    	{
    		foreach ($character as $r)
    		{
    			$roleName = $module == "admin" ? $r : implode("_", array($module,$r));
    			
    			$permission[ucwords($module)][$level][] = array($roleName,"checkbox","check_".$roleName,$w->auth->getUser($option['group_id'])->hasRole($roleName));
    		}
    	}
    }
    $action = $w->auth->user()->is_admin ? "/admin/permissionedit/".$option['group_id'] : null;
    
    $w->ctx("permission", Html::multiColForm($permission,$action,"POST","Save",null,null,array('goBack'=>'Go Back')));
    
    $w->ctx("groupRoles", json_encode($groupRoles));
}

function permissionedit_POST(Web &$w)
{
	$option = $w->pathMatch("group_id");
	//update permissions for user/group;
	$user = $w->auth->getUser($option['group_id']);
	//add roles;
    $roles = $w->auth->getAllRoles();
    
    foreach ($roles as $r) 
    {
        if ($_REQUEST["check_".$r] == 1) 
        {
            $user->addRole($r);
        }
    }
    //remove roles;
    $userRoles = $user->getRoles();
    
    foreach ($userRoles as $userRole) 
    {
        if (!$_REQUEST["check_".$userRole]) 
        {
            $user->removeRole($userRole);
        }
    }
    $returnPath = $user->is_group == 1 ? "/admin/moreInfo/".$option['group_id'] : "/admin/users";
    
    $w->msg("Permissions are updated!", $returnPath);
}

/**
 * Add new members to a group
 *
 * @param <type> $w
 */
function groupmember_GET(Web &$w)
{
	$option = $w->pathMatch("group_id");
	
	$users = $w->auth->getUsers();
	
	foreach ($users as $user)
	{
		$name = $user->is_group == 1 ? strtoupper($user->login) : $user->getContact()->getFullName();
		
		$select[$user->is_group][$name] = array($name,$user->id);
	}
	ksort($select[0]);
	ksort($select[1]);

	$template['New Member'] = array(array(array("Select Member: ","multiSelect","title",null,array_merge($select[0],$select[1])),
																	 array("","hidden","group_id",$option['group_id'])));
 	if ($w->auth->user()->is_admin)
 	{
 		$template['New Member'][0][] = array("Owner","checkbox","is_owner");
 	}
														   
	$w->out(Html::multiColForm($template,"/admin/groupmember","POST","Save"));
	
	$w->setLayout(null);
}

function groupmember_POST(Web &$w)
{
	$groupUsers = $w->auth->getUser($_REQUEST['group_id'])->isInGroups();
	
	if ($groupUsers)
	{
		foreach ($groupUsers as $groupUser)
		{
			$groupUser->getParents();
		}
	}

	foreach ($_REQUEST['title'] as $member_id)
	{
		$existUser = $w->auth->getUser($member_id)->isInGroups($_REQUEST['group_id']);
		
		if (!$existUser)
		{
			if (!$_SESSION['parents'] || !in_array($member_id, $_SESSION['parents']))
			{
				$groupMember = new Group_User($w);
				$groupMember->group_id = $_REQUEST['group_id'];
				$groupMember->user_id = $member_id;
				$groupMember->role = ($_REQUEST['is_owner'] && $_REQUEST['is_owner'] == 1) ? "owner" : "member";
				$groupMember->insert();
			}
			
			if ($_SESSION['parents'] && in_array($member_id, $_SESSION['parents']))
			{
				$exceptions[] = $w->auth->getUser($member_id)->login;
			}
		}
		else
		{
			$user = $existUser[0]->getUser();
			
			$exceptions[] = $user->is_group == 1 ? $user->login : $user->getContact()->getFullName();
		}
	}
	unset($_SESSION['parents']);
	
	if ($exceptions)
		$w->error(implode(", ", $exceptions)." can not be added!", "/admin/moreInfo/".$_REQUEST['group_id']);
	else
		$w->msg("New members are added!", "/admin/moreInfo/".$_REQUEST['group_id']);
}

/**
 * Display edit group dialog
 *
 * @param <type> $w
 */
function groupedit_GET(Web &$w)
{
	$option = $w->pathMatch("group_id");
	
	$user = $w->auth->getUser($option['group_id']);

	$template['Edit Group'] = array(array(array("Group Title: ","text","title",$user->login)));
	
	$w->out(Html::multiColForm($template,"/admin/groupedit/".$option['group_id'],"POST","Save"));
	
	$w->setLayout(null);
}

function groupedit_POST(Web &$w)
{
	$option = $w->pathMatch("group_id");
	
	$user = $w->auth->getUser($option['group_id']);
	$user->login = $_REQUEST['title'];
	$user->update();

	$w->msg("Group info updated!", "/admin/groups");
}

function groupdelete_GET(Web &$w)
{
	$option = $w->pathMatch("group_id");
	
	$user = $w->auth->getUser($option['group_id']);
	$user->delete();
	
	$roles = $user->getRoles();
	
	foreach ($roles as $role)
	{
		$user->removeRole($role);
	}
	$members = $w->auth->getGroupMembers($option['group_id']);
	
	if ($members)
	{
		foreach ($members as $member)
		{
			$member->delete();
		}
	}
	$w->msg("Group is deleted!", "/admin/groups");
}

function memberdelete_GET(Web &$w)
{
	$option = $w->pathMatch("group_id","member_id");
	
	$member = $w->auth->getGroupMemberById($option['member_id']);
	
	if ($member)
	{
		$member->delete();
	}
	$w->msg("Member is deleted!", "/admin/moreInfo/".$option['group_id']);
}

/**
 * Display User edit form
 *
 * @param <type> $w
 */
function useredit_GET(Web &$w) {
    $p = $w->pathMatch("id","box");
    $user = $w->auth->getObject("User",$p["id"]);
    if ($user) {
        admin_navigation($w,"Administration - Edit User - ".$user->login);
    } else {
        if (!$p['box']){
            $w->error("User ".$w->ctx("id")." does not exist.","/admin/users");
        }
    }
    $w->ctx("user",$user);

    // no layout if displayed in a box
    if ($p['box']) {
        $w->setLayout(null);
    }
}

/**
 * Handle User Edit form submission
 *
 * @param <type> $w
 */
function useredit_POST(Web &$w) {
    $w->pathMatch("id");
    $errors = $w->validate(array(
            array("login",".+","Login is mandatory")
    ));
    if ($_REQUEST['password'] && ($_REQUEST['password'] != $_REQUEST['password2'])) {
        $error[]="Passwords don't match";
    }
    $user = $w->auth->getObject("User",$w->ctx('id'));
    if (!$user) {
        $errors[]="User does not exist";
    }
    if (sizeof($errors) != 0) {
        $w->error(implode("<br/>\n",$errors),"/admin/useredit/".$w->ctx("id"));
    }
    $user->login = $_REQUEST['login'];

    $user->fill($_REQUEST);
    if ($_REQUEST['password']) {
        $user->setPassword($_REQUEST['password']);
    } else {
        $user->password = null;
    }
    $user->is_admin = isset($_REQUEST['is_admin']) ? 1 : 0;
    $user->is_active = isset($_REQUEST['is_active']) ? 1 : 0;
    $user->update();

    // adding roles
    $roles = $w->auth->getAllRoles();
    foreach ($roles as $r) {
        if ($_REQUEST["check_".$r]==1) {
            $user->addRole($r);
        }
    }
    // deleting roles
    foreach ($user->getRoles() as $r) {
        if (!$_REQUEST["check_".$r]) {
            $user->removeRole($r);
        }
    }

    $contact = $user->getContact();
    if ($contact) {
        $contact->fill($_REQUEST);
        $contact->private_to_user_id= null;
        $contact->update();
    }

    $w->msg("User ".$user->login." updated.","/admin/users");
}

/**
 * Display User edit form in colorbox
 *
 * @param <type> $w
 */
function useradd_GET(Web &$w) {
    $p = $w->pathMatch("box");
    if (!$p['box']) {
        admin_navigation($w,"Add User");
    } else {
        $w->setLayout(null);
    }
}

/**
 * Handle User Edit form submission
 *
 * @param <type> $w
 */
function useradd_POST(Web &$w) {
    $errors = $w->validate(array(
            array("login",".+","Login is mandatory"),
            array("password",".+","Password is mandatory"),
            array("password2",".+","Password2 is mandatory"),
    ));
    if ($_REQUEST['password2'] != $_REQUEST['password']) {
        $errors[]="Passwords don't match";
    }
    if (sizeof($errors) != 0) {
        $w->error(implode("<br/>\n",$errors),"/admin/useradd");
    }

    // first saving basic contact info
    $contact = new Contact($w);
    $contact->fill($_REQUEST);
    $contact->dt_created = time();
    $contact->private_to_user_id= null;
    $contact->insert();

    // now saving the user
    $user = new User($w);
    $user->login = $_REQUEST['login'];
    $user->setPassword($_REQUEST['password']);
    $user->is_active = $_REQUEST['is_active'] ? $_REQUEST['is_active'] : 0;
    $user->is_admin = $_REQUEST['is_admin'] ? $_REQUEST['is_admin'] : 0;
    $user->dt_created = time();
    $user->contact_id = $contact->id;
    $user->insert();
    $w->ctx("user",$user);

    // now saving the roles
    $roles = $w->auth->getAllRoles();
    foreach ($roles as $r) {
        if ($_REQUEST["check_".$r]==1) {
            $user->addRole($r);
        }
    }
    $w->msg("User ".$user->login." added","/admin/users");
}

function useract_GET(Web &$w) {
    $w->pathMatch("id","active");
    $user = $w->auth->getObject("User",$w->ctx("id"));
    if ($user) {
        $user->is_active = $w->ctx("active");
        $user->update();
        $w->msg("User ".$user->login." ".($user->is_active ? "activated" : "suspended"),"/admin/users");
    } else {
        $w->error("User ".$w->ctx("id")." does not exist.","/admin/users");
    }

}

function userdel_GET(Web &$w) {
    $w->pathMatch("id");
    $user = $w->auth->getObject("User",$w->ctx("id"));
    if ($user) {
        $user->is_deleted = 1;
        $user->update();
        $w->msg("User ".$user->login." deleted.","/admin/users");
    } else {
        $w->error("User ".$w->ctx("id")." does not exist.","/admin/users");
    }

}

/**
 * Display add group dialog
 *
 * @param <type> $w
 */
function groupadd_GET(Web &$w)
{
	$template['New Group'] = array(array(array("Group Title: ","text","title")));
	
	$w->out(Html::multiColForm($template,"/admin/groupadd","POST","Save"));
	
	$w->setLayout(null);
}

function groupadd_POST(Web &$w)
{
	$user = new User($w);
	$user->login = $_REQUEST['title'];
	$user->is_group = 1;
	$user->insert();

	$w->msg("New group added!", "/admin/groups");
}


// lookup

function lookup_ALL(Web &$w) {
    admin_navigation($w,"Lookup");

	$types = $w->Admin->getLookupTypes();
	
	$typelist = Html::select("type",$types, $_REQUEST['type']);
    $w->ctx("typelist",$typelist);
    
	// tab: Lookup List
	$where = array();
	if ($_REQUEST['type'] != "") {
		$where['type'] = $_REQUEST['type']; 
	}
    $lookup = $w->Admin->getAllLookup($where);
    
    $line[] = array("Type","Code","Title","");
    
    if ($lookup) {
    	foreach ($lookup as $look) {
    		$line[] = array(
						$look->type,
						$look->code,
						$look->title,
						Html::box($w->localUrl("/admin/editlookup/".$look->id."/".urlencode($_REQUEST['type']))," Edit ",true) .
						"&nbsp;&nbsp;&nbsp;" .
						Html::b($webroot."/admin/deletelookup/".$look->id."/".urlencode($_REQUEST['type'])," Delete ", "Are you sure you wish to DELETE this Lookup item?")
					);
    	}
    }
    else {
    	$line[] = array("No Lookup items to list");
    }
    
    // display list of items, if any
	$w->ctx("listitem",Html::table($line,null,"tablesorter",true));
    
    
    // tab: new lookup item
	$types = $w->Admin->getLookupTypes();
    
   	$f = Html::form(array(
	array("Create a New Entry","section"),
	array("Type","select","type", null,$types),
	array("or Add New Type","text","ntype"),
	array("Key","text","code"),
	array("Value","text","title"),
	),$w->localUrl("/admin/newlookup/"),"POST"," Save ");
    	
	$w->ctx("newitem",$f);
}

function newlookup_POST(Web &$w) {
    admin_navigation($w,"Lookup");

    $_REQUEST['type'] = ($_REQUEST['ntype'] != "") ? $_REQUEST['ntype'] : $_REQUEST['type'];
    
    $err = "";
    if ($_REQUEST['type'] == "")
    	$err = "Please add select or create a TYPE<br>";
    if ($_REQUEST['code'] == "")
    	$err .= "Please enter a KEY<br>";
    if ($_REQUEST['title'] == "")
    	$err .= "Please enter a VALUE<br>";
	if ($w->Admin->getLookupbyTypeCode($_REQUEST['type'],$_REQUEST['code']))
		$err .= "Type and Key combination already exists";
		    
    if ($err != "") {
    	$w->error($err,"/admin/lookup/?tab=2");
    }
    else {
	    $lookup = new Lookup($w);
		$lookup->fill($_REQUEST);
		$lookup->insert();
    
		$w->msg("Lookup Item added","/admin/lookup/");
    }
}

function editlookup_GET(Web &$w) {
	$p = $w->pathMatch("id","type");
		
	$lookup = $w->Admin->getLookupbyId($p['id']);
	
	if ($lookup) {
		$types = $w->Admin->getLookupTypes();
	
    	$f = Html::form(array(
		array("Edit an Existing Entry","section"),
		array("Type","select","type", $lookup->type,$types),
		array("Key","text","code",$lookup->code),
		array("Value","text","title", $lookup->title),
		),$w->localUrl("/admin/editlookup/".$lookup->id."/".$p['type']),"POST"," Update ");

		$w->setLayout(null);
		$w->out($f);
	}
	else {
		$w->msg("No such Lookup Item?","/admin/lookup/");
	}

}

function editlookup_POST(Web &$w) {
	$p = $w->pathMatch("id","type");

    $err = "";
    if ($_REQUEST['type'] == "")
    	$err = "Please add select a TYPE<br>";
    if ($_REQUEST['code'] == "")
    	$err .= "Please enter a KEY<br>";
    if ($_REQUEST['title'] == "")
    	$err .= "Please enter a VALUE<br>";
		    
    if ($err != "") {
    	$w->error($err,"/admin/lookup/?type=".$p['type']);
    }
    else {
		$lookup = $w->Admin->getLookupbyId($p['id']);
		
		if ($lookup) {
			$lookup->fill($_REQUEST);
			$lookup->update();
			$msg = "Lookup Item edited";		
		}
		else {
			$msg = "Could not find item?";
		}
		$w->msg($msg,"/admin/lookup/?type=".$p['type']);
    }
}

function deletelookup_ALL(Web &$w) {
	$p = $w->pathMatch("id","type");
		
	$lookup = $w->Admin->getLookupbyId($p['id']);
		
	if ($lookup) {
		$arritem['is_deleted'] = 1;
		$lookup->fill($arritem);
		$lookup->update();
		$w->msg("Lookup Item deleted","/admin/lookup/?type=".$p['type']);
	}
	else {
		$w->msg("Lookup Item not found?","/admin/lookup/?type=".$p['type']);
	}
}

?>
