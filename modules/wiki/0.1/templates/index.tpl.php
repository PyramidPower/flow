<div class="tabs">
<div class="tab-head"><a class="active"
	href="<?=WEBROOT."/wiki/index"?>">List</a></div>
<div class="tab-body">
<div id="tab-1"><?=Html::box(WEBROOT."/wiki/createwiki","Create New Wiki",true)?>
<p />
<?php
if ($wikis) {
	$table[]=array(
		"Wiki Title",
		"Date Created",
		"Last Modified Date",
		"Modified By",
		"Last Page Modified");
	foreach($wikis as $wi) {
		$p = $wi->getPageById($wi->last_modified_page_id);
		$table[]=array(
		Html::a(WEBROOT."/wiki/view/".$wi->name."/HomePage","<b>".$wi->title."</b>"),
		formatDateTime(0 + $wi->dt_created), 
		formatDateTime(0 + $p->dt_modified), 
		$p->w->auth->getUser($p->modifier_id)->getFullName(),
		Html::a(WEBROOT."/wiki/view/".$wi->name."/".$p->name,$p->name));
	}
	echo Html::table($table,"wikilist","tablesorter",true);
}
?></div>
</div>
</div>
