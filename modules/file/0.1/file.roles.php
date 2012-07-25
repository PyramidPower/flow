<?php
// $Id: file.roles.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

function role_file_upload_allowed(&$w,$path) {
    $actions = "/file\/(index";
    $actions .= "|attach";

    $actions .= ")/";
    return preg_match($actions, $path);
}

function role_file_download_allowed(&$w,$path) {
    $actions = "/file\/(index";
    $actions .= "|path";
    $actions .= "|atthumb";
    $actions .= "|atfile";
    $actions .= "|atdel";
    $actions .= "|printview";
    $actions .= ")/";
    return preg_match($actions, $path);
}

?>
