<?php
// $Id: file.actions.php 602 2010-09-28 05:51:08Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia
//ajax call to postcode and suburb information when adding or editing
function address_selectAjax_GET(Web &$w)
{
	$select = $w->pathMatch("select","option");
	
	$results = array();
		
	if ($select['select'])
	{
		$rows = $w->Address->getPostSelect(array('State'=>$select['select']),$select['option']);
		
		if ($rows)
		{
			if ($select['option'] == "Pcode") 
			{
				$returnSelect = Html::select("postCodeAssign",$rows,null);
			}
			elseif ($select['option'] == "Suburb")
			{
				$returnSelect = Html::select("suburbAssign",$rows,null);
			}
			
			$w->out(json_encode($returnSelect));
		}
	}
	
	$w->setLayout(null);
}

	/*
	 * For operations editAgency.tpl.php
	 * */
	function getSuburbAndPostcodeByStateJSON_ALL(Web $w)
	{
		$p = $w->pathMatch("state");
		$state = $p['state'];
		
		$w->setLayout(null);
		
		$rows = $w->auth->_db->sql("SELECT Suburb, Pcode FROM australian_postcodes")->where(array('State'=>$state))->orderby('Suburb','ASC')->fetch_all();
		
		if ($rows)
		{
			foreach ($rows as $row)
			{
				$results[$row['Suburb']][] = $row['Pcode'];
			}
			//return json_encode($results);
			$w->out(json_encode($results));
		}
	}
?>
