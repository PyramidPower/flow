<?php

function task_kanban_GET(Web $w) {
	$p = $w->pathMatch('id');
	$w->ctx("tg",$w->Task->getTaskGroup($p['id']));
}