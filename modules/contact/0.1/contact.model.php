<?php
// $Id: contact.model.php 690 2010-10-11 04:32:13Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

class ContactService extends DbService {

    function & getContacts($type=null,$first=null) {
        $list = array();
        if ($first) {
            $like = " and concat_ws('',firstname,lastname,othername) like '".$first."%' ";
        }
        $this->_db->sql("select * from contact where is_deleted = 0 and (private_to_user_id = 0 or private_to_user_id is null or private_to_user_id = ".$this->w->auth->user()->id.") ".$like);
        $rows = $this->_db->fetch_all();
        return $this->fillObjects("Contact", $rows);
    }

    function & getContact($id) {
    	$c = $this->getObject("Contact", $id);
    	if ($c->canView($this->auth->user()))
        	return $c;
        else
        	return null; 
    }
    
    function & getContactTemplate($parameters = null)
    {
    	if ($parameters)
		{
			foreach ($parameters as $key=>$value)
			{
				$$key = $value;
			}
		}
		
        $lines = array(
        			array("Contact Details","section"),
	                array("Title","select","title",$title,lookupForSelect($this->w, "title")),
	                array("First Name","text","firstname",$firstname),
	                array("Last Name","text","lastname",$lastname),
	                array("Other Name","text","othername",$othername),
	                array("Communication","section"),
	                array("Home Phone","text","homephone",$homephone),
	                array("Work Phone","text","workphone",$workphone),
	                array("Work Mobile","text","mobile",$mobile),
	                array("Private Mobile","text","priv_mobile",$priv_mobile),
	                array("Fax","text","fax",$fax),
	                array("Email","text","email",$email),
	                array("Private","checkbox","private",$private_to_user_id));
                
   		return $lines;
    }
}

class ContactList extends DbObject {
	var $name;
	var $is_private_to_user_id;
	
	var $_modifiable;
	
	function getDbTableName() {
		return "contact_list";
	}
}

class ContactListMember extends DbObject {
	var $contact_list_id;
	var $contact_id;
	
	var $_modifiable;
	
	function getDbTableName() {
		return "contact_list_member";
	}
}

class Contact extends DbObject {

    var $firstname;
    var $lastname;
    var $othername;
    var $title;
    var $homephone;
    var $workphone;
    var $mobile;
    var $priv_mobile;
    var $fax;
    var $email;
    var $is_deleted;
    var $dt_created;
    var $dt_modified;
    var $private_to_user_id;

    function getFullName() {
        if ($this->firstname && $this->lastname) {
            return $this->firstname." ".$this->lastname;
        } else if ($this->firstname) {
            return $this->firstname;
        } else if ($this->lastname) {
            return $this->lastname;
        } else if ($this->othername) {
            return ($this->othername);
        }
    }
    
    function getFirstName()
    {
    	return $this->firstname;
    }
    
    function getSurname()
    {
    	return $this->lastname;
    }

    function getShortName() {
        if ($this->firstname && $this->lastname) {
            return $this->firstname[0]." ".$this->lastname;
        } else {
        	return $this->getFullName();
        }
    }
    
    function getPartner() {
        return null;
    }

    function getUser() {
        return $this->w->auth->getUserForContact($this->id);
    }

    function printSearchTitle() {
        $buf = $this->getFullName();
        return $buf;
    }
    function printSearchListing() {
        if ($this->private_to_user_id) {
            $buf .= "<img src='".$this->w->localUrl("/img/Lock-icon.png")."' border='0'/>";
        }
        $first = true;
        if ($this->workphone) {
            $buf .= "work phone ".$this->workphone;
            $first = false;
        }
        if ($this->mobile) {
            $buf.= ($first ? "":", ")."mobile ".$this->mobile;
            $first = false;
        }
        if ($this->email) {
            $buf.=($first ? "":", ").$this->email;
            $first = false;
        }
        return $buf;
    }

    function printSearchUrl() {
        return "contact/view/".$this->id;
    }

    function canList(&$user) {
        if ($this->private_to_user_id &&
                $this->private_to_user_id != $user->id &&
                !$user->hasRole("administrator")) {
            return false;
        }
        return true;
    }

    function canView(&$user = null) {
    	if (!$user) {
    		$user = $this->w->auth->user();
    	}
    	// only owners or admin can see private contacts
        if ($this->private_to_user_id &&
                $this->private_to_user_id != $user->id &&
                !$user->hasRole("administrator")) {
            return false;
        }
        // don't show contacts of suspended users
        $u = $this->getUser(); 
        if ( $u && (!$u->is_active || $u->is_deleted)) {
            return false;
        }
        return true;
    }
    function canEdit(&$user) {
        return ($user->hasRole("contact_editor")||$this->private_to_user_id == $user->id);
    }
    
    function canDelete(&$user) {
        $is_admin = $user->hasRole("contact_editor");
        $is_private = $this->private_to_user_id == $user->id;
        return $is_private || $is_admin;
    }

    function getDbTableName() {
    	return "contact";
    }
    
	function getSelectOptionTitle() {
		return $this->getFullName();
	}    
}
?>
