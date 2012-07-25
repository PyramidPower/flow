<?php
// $Id$
// (c) 2010 Pyramid Power, Australia

function index_ALL(Web &$w) {
	$w->ctx("wikis",$w->Wiki->getWikis());
}

function wikichanges_GET(Web &$w) {
	$pm = $w->pathMatch("wid","pagename");
	$wiki = $w->Wiki->getWikiById($pm['wid']);
	if (!$wiki || !$wiki->canRead($w->auth->user()) ) {
		$w->error("No access to this wiki.");
	}
	$w->ctx("wiki",$wiki);
}

function pagechanges_GET(Web &$w) {
	$pm = $w->pathMatch("wid","pagename");
	$wiki = $w->Wiki->getWikiById($pm['wid']);
	if (!$wiki || !$wiki->canRead($w->auth->user()) ) {
		$w->error("No access to this wiki.");
	}
	$wp = $wiki->getPage($pm['pagename']);
	if (!$wp) {
		$w->error("Page does not exist.","/wiki/index");
	}
	$w->ctx("wiki",$wiki);
	$w->ctx("page",$wp);
	}

function editmember_GET(Web &$w) {
	$pm = $w->pathMatch("wid","mid");
	$wiki = $w->Wiki->getWikiById($pm['wid']);
	if (!$wiki || !$wiki->isOwner($w->auth->user()) ) {
		$w->error("No access to this wiki.");
	}
	$mem = $wiki->getUserById($pm['mid']);
	if (!$mem) {
		$mem = new WikiUser($w);
	}
	$w->ctx("wiki",$wiki);
	$w->ctx("mem",$mem);
	$w->setLayout(null);
}

function editmember_POST(&$w) {
	$pm = $w->pathMatch("wid","mid");
	$wiki = $w->Wiki->getWikiById($pm['wid']);
	if (!$wiki || !$wiki->isOwner($w->auth->user()) ) {
		$w->error("No access to this wiki.");
	}
	$mem = $wiki->getUserById($pm['mid']);
	if (!$mem) {
		$mem = new WikiUser($w);
	}
	$mem->user_id = $w->request("user_id");
	$mem->role = $w->request("role");
	$mem->wiki_id = $wiki->id;
	$mem->insertOrUpdate();
	$w->msg("Member updated.","/wiki/members/".$wiki->id);
}

function members_GET(&$w) {
	$pm = $w->pathMatch("id");
	$wiki = $w->Wiki->getWikiById($pm['id']);
	if (!$wiki || !$wiki->isOwner($w->auth->user())) {
		$w->error("No access to this wiki.");
	}
	$w->ctx("wiki",$wiki);
	$w->ctx("title",$wiki->title." - Members");
}

function view_GET(Web &$w) {
	$pm = $w->pathMatch("wikiname","pagename");
	$wiki = $w->Wiki->getWikiByName($pm['wikiname']);
	if (!$wiki) {
		$w->error("Wiki does not exist.");
	}
	$wp = $wiki->getPage($pm['pagename']);
	if (!$wp) {
		$wp = $wiki->addPage($pm['pagename'],"New Page.");
	}
	if ($pm['pagename'] == "HomePage") {
		$_SESSION['wikicrumbs'][$pm['wikiname']] = array();
	} else {
		$_SESSION['wikicrumbs'][$pm['wikiname']][$pm['pagename']] = 1;
	}
	$w->ctx("body",wiki_format_creole($wiki,$wp));
	$w->ctx("wiki",$wiki);
	$w->ctx("page",$wp);
	$w->ctx("attachments",$w->service("File")->getAttachments($wp));
	$w->ctx("title",$wiki->title." - ".$wp->name);
}

function edit_GET(Web &$w){
	$pm = $w->pathMatch("wikiname","pagename");
	try {
		$wiki = $w->Wiki->getWikiByName($pm['wikiname']);
	} catch (WikiException $ex) {
		$w->error($ex->getMessage(),"/wiki/index");
	}
	$wp = $wiki->getPage($pm['pagename']);
	if (!$wp) {
		$w->error("Page does not exist.","/wiki/index");
	}
	$w->ctx("wiki",$wiki);
	$w->ctx("page",$wp);
	$w->ctx("attachments",$w->service("File")->getAttachments($wp));
	$w->ctx("title",$wiki->title." - ".$wp->name);
}

function edit_POST(Web &$w) {
	$pm = $w->pathMatch("wikiname","pagename");
	$wiki = $w->Wiki->getWikiByName($pm['wikiname']);
	if (!$wiki) {
		$w->error("Wiki does not exist.");
	}
	$wp = $wiki->getPage($pm['pagename']);
	if (!$wp) {
		$w->error("Page does not exist.");
	}
	$wiki->updatePage($pm['pagename'],$w->request("body"));
	$w->msg("Page updated.","/wiki/view/".$pm['wikiname']."/".$pm['pagename']);
}

function markup_GET(Web &$w) {
	$w->setLayout(null);
}
function createwiki_GET(Web &$w) {
	$w->setLayout(null);
}

function createwiki_POST(Web &$w) {
	$title = $w->request("title");
	$is_public = $w->request("is_public");
	try {
		$wiki = $w->Wiki->createWiki($title, $is_public);
	} catch (WikiException $ex) {
		$w->error($ex->getMessage(),"/wiki/index");
	}
	if ($wiki) {
		$w->msg("Wiki ".$title." created.","/wiki/view/".$wiki->name."/HomePage");
	} else {
		$w->error("Wiki couldn't be created","/wiki/index");
	}
}