<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function role_pages_view_allowed(&$w,$path)
{
	$actions = "/pages\/(index";
    $actions .= "|index";
    $actions .= ")/";

    return preg_match($actions, $path);	
}

function role_pages_viewpartial_allowed(&$w,$path)
{
	return role_pages_view_allowed($w, $path);
}

function role_pages_edit_allowed(&$w,$path)
{
	$actions = "/pages\/(index";
    $actions .= "|edit";
    $actions .= ")/";

    return preg_match($actions, $path);	
}

function role_pages_editpartial_allowed(&$w,$path)
{
	return role_pages_edit_allowed($w, $path);
}

function role_pages_add_allowed(&$w,$path)
{
	$actions = "/pages\/(index";
    $actions .= "|create";
    $actions .= ")/";

    return preg_match($actions, $path);	
}

function role_pages_addpartial_allowed(&$w,$path)
{
	return role_pages_add_allowed($w, $path);
}

function role_pages_delete_allowed(&$w,$path)
{
	$actions = "/pages\/(index";
    $actions .= "|delete";
    $actions .= ")/";

    return preg_match($actions, $path);	
}

function role_pages_deletepartial_allowed(&$w,$path)
{
	return role_pages_delete_allowed($w, $path);
}

function role_pages_comment_allowed(&$w,$path)
{
	$actions = "/pages\/(index";
    $actions .= "|comment";
    $actions .= ")/";

    return preg_match($actions, $path);	
}

function role_pages_history_allowed(&$w,$path)
{
	$actions = "/pages\/(index";
    $actions .= "|history";
    $actions .= ")/";

    return preg_match($actions, $path);	
}

function role_pages_invitation_allowed(&$w,$path)
{
	$actions = "/pages\/(index";
    $actions .= "|invitation";
    $actions .= ")/";

    return preg_match($actions, $path);	
}

function role_pages_invitationpartial_allowed(&$w,$path)
{
	return role_pages_invitation_allowed($w, $path);
}
?>
