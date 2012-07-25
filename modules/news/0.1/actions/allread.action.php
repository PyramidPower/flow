<?php
function news_allread_GET(Web &$w) {
    $w->service('News')->markAllItemsRead();
    $w->msg("All news items marked as read.","/news/index");
}
