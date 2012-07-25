<?php
// $Id$
// (c) 2010 Pyramid Power, Australia

function role_copyme_role1_allowed(&$w,$path) {
	// do stuff to allow access
	    $actions = "/copyme\/(index";
    $actions .= "|index";
    $actions .= "|action1";
    $actions .= "|action2";
    $actions .= ")/";
    return preg_match($actions, $path);	
}