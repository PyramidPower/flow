<?php
class Page_history extends Page 
{
    var $page_id;
    
    function update() 
    {
        DbObject::update();
    }
    
    function insert() 
    {
        DbObject::insert();
    }
}
