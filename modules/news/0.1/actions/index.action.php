<?php

function index_ALL(Web &$w) {
    news_navigation($w,"News");
    $news = $w->service('News')->getLatest();
    $w->ctx("news",&$news);
}
