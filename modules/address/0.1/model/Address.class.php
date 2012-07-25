<?php
class Address extends DbObject
{
	var $id;
	var $addressOne;
	var $addressTwo;
	var $addressThree;
	var $postcode_id;
	
	function getDbTableName()
	{
		return "address";
	}
	
	function getPostDetail()
	{
		$object = $this->getObject("Australian_Postcodes", array('id'=>$this->postcode_id));
		
		return $object;
	}
}
