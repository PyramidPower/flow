<?php
class News extends DbObject {
    var $subject;
    var $teaser;
    var $body;
    var $dt_created;
    var $dt_modified;
    var $author_id;
    var $is_deleted;
    var $is_archived;

    function & getAuthor() {
        return $this->w->auth->getUser($this->author_id);
    }

    function insert() {
        $this->author_id = $this->w->auth->user()->id;
        $this->dt_created = time();
        $this->dt_modified = time();
        parent::insert();
    }

    function update() {
        parent::update();
        $this->_db->sql("delete from news_read where news_id = ".$this->id)->execute();
    }

    function archive() {
        $this->is_archived = 1;
        $this->update();
    }

    function isRead() {
        $user_id = $this->w->auth->user()->id;
        $id = $this->id;
        $isread = $this->_db->sql("select count(*) as count from news_read where user_id = $user_id and news_id = $id")->fetch_element("count");
        if (!$isread) {
            return false;
        }
        if ($isread) return true;
    }

    function printSearchTitle() {
        $buf = $this->subject;
        return $buf;
    }
    function printSearchListing() {
        //$buf = $this->teaser;
        return $buf;
    }

    function printSearchUrl() {
        return "news/view/".$this->id;
    }

    function canList(&$user) {
        return $user->hasRole("news_reader");
    }

    function canView(&$user) {
        return $user->hasRole("news_reader");
    }

}
