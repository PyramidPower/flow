<?php
class AddressService extends DbService
{
	function getPost($where)
	{
		$objects = $this->getObjects("Australian_Postcodes",$where);
	
		return $objects;
	}
	
	function getPostColumns($columns, $where)
	{
		$rows = $this->_db->select($columns)->from("australian_postcodes")->where($where)->fetch_all();
		
		return $rows;
	}
	
	function getPostSelect($where,$column)
	{
		$rows = $this->_db->sql("SELECT DISTINCT $column FROM australian_postcodes")->where($where)->order_by($column,"ASC")->fetch_all();
		
		if ($rows)
		{
			foreach ($rows as $row)
			{
				$results[] = $row[$column];
			}
			
			return $results;
		}
	}
	
	function getSuburbAndPostcodeByState($state)
	{
		$rows = $this->_db->sql("SELECT Suburb, Pcode FROM australian_postcodes")->where(array('State'=>$state))->orderby('Suburb','ASC')->fetch_all();
		
		if ($rows)
		{
			foreach ($rows as $row)
			{
				$results[$row['Suburb']][] = $row['Pcode'];
			}
			return $results;
		}
	}
	
	function getArea($where)
	{
		$objects = $this->getObjects("Area",$where);
		
		return $objects;
	}
	
	function getAddressTemplate($parameters = null)
	{
		if ($parameters)
		{
			foreach ($parameters as $key=>$value)
			{
				$$key = $value;
			}
		}
		$address["Address details"] = array(array(array("Address (Primary)","text","primaryAddress",$primaryAddress)),
		array(array("Address (Secondary)","text","secondaryAddress",$secondaryAddress)),
		array(array("Address (Optional)","text","optionalAddress",$optionalAddress)),
		array(array("State","select","stateAddress",$stateAddress,$stateAddressArray)),
		array(array("Suburb","select","suburbAssign",$suburbAssign,$suburbArray)),
		array(array("Post Code","select","postCodeAssign",$postCodeAssign,$postAddressArray)));
		return $address;
	}
}
