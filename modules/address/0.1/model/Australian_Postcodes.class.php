<?php
class Australian_Postcodes extends DbObject
{
	var $id;
	var $Pcode;
	var $Suburb;
	var $State;
	var $Comments;
	var $DeliveryOffice;
	var $Presortindicator;
	var $ParcelZone;
	var $BSPnumber;
	var $BSPname;
	var $Category;
	var $Lat;
	var $Long;
	
	function getDbTableName()
	{
		return "australian_postcodes";
	}
}
