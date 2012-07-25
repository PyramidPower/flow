<?php
function edit_GET(Web &$w) {
	$p = $w->pathMatch("id");

	$usr = array();
	$grps = array();

	// get news item
	$item = $w->News->getNewsItem($p['id']);

	if ($item) {
		// get any groups allowed to view this item
		$groups = $w->News->getNewsGroups($p['id']);

		// if active groups, create array. below we'll create group array using User object
		if ($groups) {
			foreach ($groups as $group) {
				if ($group->is_active == "0")
				$grps[] = $group->group_id;
			}
		}
	} else {
		$item = new News($w);
	}
	// get all users
	$users = $w->auth->getUsers();

	// only interested in groups
	foreach ($users as $user) {
		if ($user->is_group == "1") {
			$usr[] = $user;

			// if this group is one of our selected groups, create array appropriate for 'View by Group' select
			if (in_array($user->id, $grps))
			$grp[] = array($user->login, $user->id);
		}
	}

	$w->ctx("grp",$grp);
	$w->ctx("usr",$usr);

	$w->ctx("item",$item);
	news_navigation($w,"News");
}

/**
 * handle edit POST event
 * @param Web $w
 */
function edit_POST(Web &$w) {
	news_navigation($w,"News");
	$p = $w->pathMatch("id");
	if ($p['id']) {
		$news = $w->service("News")->getNewsItem($p['id']);
		if (!$news) {
			$w->error("News Article not available","/news/index");
		}
	} else {
		$news = new News($w);
	}
	$news->subject = $_REQUEST['news_subject'];
	$news->teaser = $_REQUEST['news_teaser'];
	$news->body = $_REQUEST['news_body'];
	if ($p['id']) {
		$news->update();
		$msg = "News item updated";
	} else {
		$news->insert();
		$msg = "News item added";
	}

	// add 'View by Group' records
	if ($news->id) {
		$arrdb['news_id'] = $news->id;
		 
		// a group may have been dumped, but we only know which groups have been selected for inclusion
		// so flag all groups for this news item inactive. if they are on select list they will be updated to active below
		$newsgroups = $w->News->getNewsGroups($arrdb['news_id']);
		 
		if ($newsgroups) {
			foreach ($newsgroups as $ng) {
				$thisgrp = $w->News->getGroupByNews($ng->group_id,$arrdb['news_id']);
				$thisgrp->is_active = "1";
				$thisgrp->update();
			}
		}
		 
		// for each selected group complete population of input array
		if ($_REQUEST['group']) {
			foreach ($_REQUEST['group'] as $group) {
				$arrdb['group_id'] = $group;
				$arrdb['is_active'] = "0";
					
				// check to see if group already exists for this news item
				$grp = $w->News->getGroupByNews($arrdb['group_id'],$arrdb['news_id']);

				// if no group, create record
				if (!$grp) {
					$grp = new News_group($w);
					$grp->fill($arrdb);
					$grp->insert();
				}
				else {
					// if group exists, update the record - ie. is_active = yes
					$grp->fill($arrdb);
					$grp->update();
				}
			}
		}
	}

	$w->msg($msg,"/news/index");
}
