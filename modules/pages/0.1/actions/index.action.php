<?php
function pages_index_ALL(Web &$w) 
{
	$option = $w->pathMatch("level", "id");
	
	$id = $option['level'] ? $option['id'] : 0;
	
	/**
	 * $_SESSION['root'] is used to maintain the structure of navigation tree, because you need to remeber the previous
	 * state of the structure with out using Ajax call; whenever the user click the homepage link, unset this variable
	 * to improve performance and save memory;
	 * 
	 * $_SESSION['pool'] is used only for pages_users; it stores broken inheritance page's id, so if a page is requested
	 * by user and the page id is not exist in this pool, then return the page to user, otherwise ignore the request;
	 **/
	if ($id == 0)
	{
		unset($_SESSION['root']);
		unset($_SESSION['pool']);
		unset($_SESSION['thumbView']);
	}

	if (!$_SESSION['root'] || !in_array($id, $_SESSION['root']))
	{
		$_SESSION['root'][] = $id;
	}
	$title = PagesHelper::loadPageContext($w, $id);
	
	$_SESSION['thumbView'][$id] = $id == 0 ? "Home" : $title;

	if ($w->auth->user()->hasRole("pages_add") || $w->Page->getPageRole($w, $id) == "pages_editor")
		$w->ctx("createButton", Html::b(WEBROOT."/pages/create/level/".$id, "New Page")."&nbsp;");
}
?>