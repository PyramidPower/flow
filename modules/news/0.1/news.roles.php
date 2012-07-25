<?php 
// $Id: news.roles.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia


function role_news_reader_allowed(&$w,$path) {
    $actions = "/news\/(index";
    $actions .= "|allread";
    $actions .= "|view";
    $actions .= ")/";
    return preg_match($actions, $path);
}

function role_news_admin_allowed(&$w,$path) {
    $actions = "/news\/(index";
    $actions .= "|edit";
    $actions .= "|archive";
    $actions .= "|delete";
    $actions .= ")/";
    return preg_match($actions, $path);
}

?>
