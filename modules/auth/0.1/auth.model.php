<?php
// $Id: auth.model.php 829 2010-11-05 04:07:09Z adam@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

class AuthService extends DbService {

    var $_roles;
    var $_roles_loaded = false;
    var $_user = null;

    function login($login, $password, $client_timezone) {
        $password = User::encryptPassword($password);
        $user_data = $this->_db->get("user")->where("login",$login)->and("password",$password)->and("is_active","1")->and("is_deleted","0")->fetch_row();        
        if ($user_data != null) {
            $user = new User($this->w);
            $user->fill($user_data);
            $user->updateLastLogin();
            $_SESSION['user_id'] = $user->id;
            $_SESSION['timezone'] = $client_timezone;
            return $user;
        } else {
            return null;
        }
    }

    function loginLocalUser() {
    		
    }
    
    function __init() {
        $this->_loadRoles();
    }

    function loggedIn() {
        return array_key_exists('user_id',$_SESSION);
    }

    function & user() {
        if (!$this->_user && $this->loggedIn()) {
            $this->_user = $this->getObject("User", $_SESSION['user_id']);
        }
        return $this->_user;
    }

    function allowed($path,$url=null) {
        $p =explode("/", $path);
        $handler = $p[0];
        $hsplit = explode("-",$handler);
        $handler = array_shift($hsplit);
        if (!in_array($handler, $this->w->handlers())) {
            return false;
        }
        if ($this->user()) {
            if ($this->user()->allowed($this->w,$path)) {
                return $url ? $url : true;
            }
        } else {
            return function_exists("anonymous_allowed") && anonymous_allowed($this->w,$path);
        }
        return false;
    }

    function getAllRoles() {
        $this->_loadRoles();
        if (!$this->_roles) {
            $roles = array();

            $funcs = get_defined_functions();
            foreach ($funcs['user'] as $f) {
                if (preg_match("/^role_(.+)_allowed$/", $f, $matches)) {
                    $roles[]=$matches[1];
                }
            }
            $this->_roles = $roles;
        }
        return $this->_roles;
    }

    function _loadRoles() {
        // do this only once
        if ($this->_roles_loaded)
            return;

        $handlers = $this->w->handlers();
        foreach ($handlers as $model) {
            $file = $this->w->getHandlerDir($model).$model.".roles.php";
            if (file_exists($file)) {
                require_once $file;
            }
        }
        $this->_roles_loaded = true;
    }

    function & getUser($id) {
        return $this->getObject("User", $id);
    }

    function & getUsers($includeDeleted = false) {
        return $this->getObjects("User",array('is_deleted', $includeDeleted ? 1 : 0),true);
    }

    function & getUserForContact($cid) {
    	return $this->getObject("User", array("contact_id",$cid));
    }

    function & getUsersForRole($role) {
        if (!$role) {
            return null;
        }
        $users = $this->getUsers();
        $roleUsers = array();
        if ($users) {
        	foreach ($users as $u) {
        		if ($u->hasRole($role)) {
        			$roleUsers[] = $u;
        		}
        	}
        }
        return $roleUsers;
    }
    
    function & getGroups()
    {
    	$rows = $this->_db->get("user")->where(array('is_active'=>1,'is_deleted'=>0,'is_group'=>1))->fetch_all();
    	
    	if ($rows)
    	{
    		$objects = $this->fillObjects("User", $rows);
    		
    		return $objects;
    	}
    	return null;
    }
    
    function getGroupMembers($group_id = null, $user_id = null)
    {
    	if ($group_id)
    		$option['group_id'] = $group_id;
    	
    	if ($user_id)
    		$option['user_id'] = $user_id;
    	
    	$groupMembers = $this->getObjects("Group_User", $option, true);
    	
    	if ($groupMembers)
    	{
    		return $groupMembers;
    	}
    	return null;
    }
    
    function getGroupMemberById($id)
    {
    	$groupMember = $this->getObject("Group_User", $id);
    	
    	if ($groupMember)
    	{
    		return $groupMember;
    	}
    	return null;
    }
    
	function getRoleForLoginUser($group_id, $user_id)
	{
		$groupMember = $this->getObject("Group_User", array('group_id'=>$group_id,'user_id'=>$user_id));
		
		if ($groupMember)
		{
			return $groupMember->role;
		}
		return null;
	}
}


class User extends DbObject {

    var $login;
    var $is_admin;
    var $password;
    var $is_active;
    var $dt_lastlogin;
    var $dt_created;
    var $contact_id;
    var $is_deleted;
    var $is_group;

    var $_roles;
    var $_contact;

    function delete() {
        $contact = $this->getContact();
        if ($contact) {
            $contact->delete();
        }
        $this->is_deleted = 1;
        $this->is_active = 0;
        $this->password = "";
        $this->update();
    }

    function getContact() {
        if (!$this->_contact) {
            $this->_contact = $this->getObject("Contact", $this->contact_id);
        }
        return $this->_contact;
    }
    
    function isInGroups($group_id = null)
    {
    	$groupUsers = isset($group_id) ? $this->getObjects("Group_User", array('user_id'=>$this->id,'group_id'=>$group_id)) : $this->getObjects("Group_User", array('user_id'=>$this->id));
    	
    	if ($groupUsers)
    	{
    		return $groupUsers;
    	}
    	return null;
    }
    
	function inGroup($group) {
    	$groupmembers = $this->Auth->getGroupMembers($group->id, null);
    	
    	if ($groupmembers) {
	    	foreach ($groupmembers as $member) {
    			if ($member->user_id == $this->id)
    				return true;

    			$usr = $this->Auth->getUser($member->user_id);
    			if ($usr->is_group == "1")
    				$flg = $this->inGroup($usr);
    			if ($flg)
    				return true;
	    	}
    	}
    }
    
    function getFirstName()
    {
    	$contact = $this->getContact();
    	
    	if ($contact) {
            $name = $contact->getFirstName();
        }
        return $name;
    }
    
    function getSurname()
    {
    	$contact = $this->getContact();
    	if ($contact) {
            $name = $contact->getSurname();
        }
        return $name;
    }

    function getFullName() {
        $contact = $this->getContact();
        $name = ucfirst($this->login);
        if ($contact) {
            $name = $contact->getFullName();
        }
        return $name;
    }

    function getSelectOptionTitle() {
        return $this->getFullName();
    }

    function getShortName() {
        $contact = $this->getContact();
        $name = ucfirst($this->login);
        if ($contact) {
            $name = $contact->firstname;
        }
        return $name;
    }

    function getRoles($force = false) {
        if (!$this->_roles || $force) {
            $this->_roles = array();
            
            $groupUsers = $this->isInGroups();
            
            if ($groupUsers)
            {
            	foreach ($groupUsers as $groupUser)
            	{
            		$groupRoles = $groupUser->getGroupRoles();
            		
            		foreach ($groupRoles as $groupRole)
            		{
            			if (!in_array($groupRole, $this->_roles))
            				$this->_roles[] = $groupRole;
            		}
            	}
            }
            $rows = $this->getObjects("user_role",array("user_id",$this->id),true);
            
            if ($rows) 
            {
                foreach ($rows as $row) 
                {
                	if (!in_array($row->role, $this->_roles))
                    	$this->_roles[]=$row->role;
                }
            }
        }
        return $this->_roles;
    }

    function updateLastLogin() {
        $data = array("dt_lastlogin" => $this->time2Dt(time()));
        $this->_db->update("user",$data)->where("id",$this->id)->execute();
    }

    function hasRole($role) {
    	if ($this->is_admin) {
    		return true;
    	}
        if ($this->getRoles()) {
            return in_array($role, $this->_roles);
        } else {
            return false;
        }
    }

    function hasAnyRole($roles) {
    	if ($this->is_admin) {
    		return true;
    	}
    	if ($roles) {
	    	foreach ($roles as $r) {
	    		if ($this->hasRole($r)) {
	    			return true;
	    		}
	    	}
    	}
    	return false;
    }
    
    function addRole($role) {
        if (!$this->hasRole($role)) {
            $data = array(
                    "user_id"=>$this->id,
                    "role" => $role
            );
            $this->_db->insert("user_role",$data)->execute();
        }
    }

    function removeRole($role) {
        if ($this->hasRole($role)) {
            $this->_db->delete("user_role")->where("user_id",$this->id)->and("role",$role)->execute();
            $this->getRoles(true);
        }
    }

    function allowed(&$w,$path) {
        if (!$this->is_active) {
            return false;
        }
        if ($this->is_admin) {
            return true;
        }
        if ($this->getRoles()) {
            foreach ($this->getRoles() as $rn) {
                $rolefunc = "role_".$rn."_allowed";
                if (function_exists($rolefunc)) {
                    if ($rolefunc($w,$path)) {
                        return true;
                    }
                } else {
                    $w->logError("Role '".$rn."' does not exist!");
                }
            }
        }
        return false;
    }

    function encryptPassword($password) {
        return sha1($password);
    }

    function setPassword($password) {
        $this->password = $this->encryptPassword($password);
    }

}

class user_role extends DbObject {
	var $user_id;
	var $role;
}



