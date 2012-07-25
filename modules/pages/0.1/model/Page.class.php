<?php
class Page extends DbObject 
{
    var $subject;
    var $teaser;
    var $body;
    
    var $owner_id;
    var $dt_created;
    var $creator_id;
    var $dt_modified;
    var $modifier_id;
    
    var $allow_comments; // 0/1
    var $moderate_comments; //0/1
    var $inherit_permissions_page_id;
    var $is_parent; //0/1
    var $path;
    var $is_transition; //0/1
    var $is_public; //0/1
    var $is_deleted; //0/1
    
    var $_users=null;
//    var $_versionable;
    
    /*
     * the way to inherit permissions is by
     * referencing a parent_page, even though
     * that page may not link to this page directly.
     *
     * The parent_page acts like a permission template
     * all pages that reference that page can inherit
     * it's permissions
     *
     * Also it's possible to list all child pages for a
     * parent page.
     *
     * What if the parent page itself inherits permissions?
     * then it will take some time to crawl along the
     * parent branches....
     *
     * if the page inherited permissions before and this is
     * later removed, the UI should allow to copy
     * all permissions to the new page ... or start
     * from scratch.
     */
    function printSearchTitle()
    {	
    	if ($this->subject)
    	{
    		return $this->subject;
    	}
    }
    
    function printSearchListing()
    {
    	if ($this->creator_id)
    	{
    		$result = "Created By: <strong>".$this->getCreator()->getFullName()."</strong>";
    	}
    	if ($this->dt_created)
    	{
    		$result .= " at: <strong>".date('d/m/Y H:i',$this->dt_created)."</strong>";
    	}
    	
    	return $result;
    }
    
    function printSearchUrl()
    {
    	return "pages/view/".$this->id;
    }
    
    function canDelete(User $user) {
    	if ($this->inherit_permissions_page_id) {
    		return $this->getParent()->canDelete($user);
    	}
        $u = $this->getPageUser($user);
        return $u && $u->can_delete;
    }

    function canEdit(User $user) {
    	if ($this->inherit_permissions_page_id) {
    		return $this->getParent()->canEdit($user);
    	}
    	$u = $this->getPageUser($user);
        return $u && $u->can_edit;
    }

    function canRead(User $user) {
    	if ($this->inherit_permissions_page_id) {
    		return $this->getParent()->canRead($user);
    	}
    	$u = $this->getPageUser($user);
        return $u && $u->can_read;
    }

    function canShare(User $user) {
    	if ($this->inherit_permissions_page_id) {
    		return $this->getParent()->canShare($user);
    	}
    	$u = $this->getPageUser($user);
        return $u && $u->can_share;
    }

    function canCreateAttachment(User $user) {
    	if ($this->inherit_permissions_page_id) {
    		return $this->getParent()->canCreateAttachment($user);
    	}
    	$u = $this->getPageUser($user);
        return $u && $u->can_create_attachment;
    }

    function canEditAttachment(User $user) {
    	if ($this->inherit_permissions_page_id) {
    		return $this->getParent()->canEditAttachment($user);
    	}
    	$u = $this->getPageUser($user);
        return $u && $u->can_edit_attachment;
    }

    function canCreateComment(User $user) {
    	if ($this->inherit_permissions_page_id) {
    		return $this->getParent()->canCreateComment($user);
    	}
    	$u = $this->getPageUser($user);
        return $u && $u->can_create_comment;
    }
    function canEditComment(User $user) {
    	if ($this->inherit_permissions_page_id) {
    		return $this->getParent()->canEditComment($user);
    	}
    	$u = $this->getPageUser($user);
        return $u && $u->can_edit_comment;
    }

    function & getPageUser(User $user) {
        if (!$this->_users || !array_key_exists($user->id, $this->_users)) {
            $user = $this->getObject("Page_user", array(array("page_id",$this->id),array("user_id",$user->id)));
            $this->_users[$user_id] &= $user;
        }
        return $this->_users[$user_id];
    }

    function & getPageUsers() {
		return array_values($this->_users);
    }
    
    function getCreator()
    {
        return $this->Auth->getUser($this->creator_id);
    }

    function getOwner() {
        return $this->Auth->getUser($this->owner_id);
    }
    
    function getParent()
    {
    	$object = $this->getObject("Page", array('id'=>$this->inherit_permissions_page_id));
    	
    	return $object;
    }

    function getModifier() {
        return $this->Auth->getUser($this->modifier_id);
    }

    function removeUser($user) {

    }
    
    function addUser($user) {
    	
    }
    
    function wikifyBody() {

    }

    function getSelectOptionTitle() {
   		return $this->subject;
    }
    
    function & getAttachments() {
    	return $this->File->getAttachments($this);
    }
}
