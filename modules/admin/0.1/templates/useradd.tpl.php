<?php
// $Id: useradd.tpl.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

$form['User Details'][]=array(
array("Login","text","login"),
array("Admin","checkbox","is_admin"),
array("Active","checkbox","is_active"));

$form['User Details'][]=array(
array("Password","password","password"),
array("Repeat Password","password","password2"));

$form['Contact Details'][]=array(
array("First Name","text","firstname"),
array("Last Name","text","lastname"));
$form['Contact Details'][]=array(
array("Title","select","title",null,lookupForSelect($w, "title")),
array("Email","text","email"));

$roles = $w->auth->getAllRoles();
$roles = array_chunk($roles, 4);
foreach ($roles as $r) {
	$row = array();
	foreach ($r as $rf) {
		$row[]=array($rf,"checkbox","check_".$rf);
	}
	$form['User Roles'][]=$row;
}

print Html::multiColForm($form,$w->localUrl("/admin/useradd"),"POST","Save");

?>
