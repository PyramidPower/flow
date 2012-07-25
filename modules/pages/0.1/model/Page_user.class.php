<?php
class Page_user extends DbObject {
    var $page_id;
    var $user_id;
    var $role_id;
    var $broken_inheritance_children;
    var $dt_created;
    
    function getPage()
    {
    	$object = $this->getObject("Page", $this->page_id);
    	
    	return $object;
    }
    
    function getUser()
    {
    	$object = $this->getObject("Page_user", $this->user_id);
    	
    	return $object;
    }
}
