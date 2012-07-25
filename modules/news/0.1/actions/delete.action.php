<?php
function delete_GET(Web &$w) {
    $p = $w->pathMatch("id","print");
    $item = $w->service("News")->getNewsItem($p['id']);
    if ($item) {
        $item->delete();
        $w->msg("News item deleted","/news/index");
    } else {
        $w->msg("News Article not available","/news/index");
    }
}
