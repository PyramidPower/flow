<?php
function archive_GET(Web &$w) {
    $p = $w->pathMatch("id","print");
    $item = $w->service("News")->getNewsItem($p['id']);
    if ($item) {
        $item->archive();
        $w->msg("News item archived","/news/index");
    } else {
        $w->msg("News Article not available","/news/index");
    }
}