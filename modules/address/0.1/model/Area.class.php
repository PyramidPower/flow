<?php
class Area extends DbObject
{
	var $id;
	var $table_name;
	var $object_id;
	var $postcode_id;
	
	function getDbTableName()
	{
		return "area";
	}
	
	function getPostDetail()
	{
		$object = $this->getObject("Australian_Postcodes", array('id'=>$this->postcode_id));
		
		return $object;
	}
	function getRecordObject()
	{
		$recordObject = $this->getObject($this->table_name, array('id'=>$this->object_id));
		
		return $recordObject;
	}	
}
