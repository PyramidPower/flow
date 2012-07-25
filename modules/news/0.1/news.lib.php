<?php
function news_navigation(&$w,$title = null) {
    if ($title) {
        $w->ctx("title",$title);
    }
    $nav = array();
    if ($w->auth->loggedIn()) {
        $nav[]=Html::a($w->localUrl("/news/edit"),"Add News Item");
    }
    $w->ctx("navigation", $nav);
}
