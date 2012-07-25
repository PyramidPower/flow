<?php
class Page_comment extends DbObject {
    var $parent_id;
    var $page_id;
    var $dt_created;
    var $dt_modified;
    var $author_id;
    var $comment;
    var $quote;
    
    function getAuthor()
    {
        return $this->Auth->getUser($this->author_id);
    }
}
