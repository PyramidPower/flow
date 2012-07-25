<?php
function admin_navigation(&$w,$title,$prenav=null) {
    if ($title) {
        $w->ctx("title",$title);
    }
    $nav = $prenav ? $prenav : array();
    if ($w->auth->loggedIn()) {
        $w->menuLink("admin/users","List Users",$nav);
        $w->menuLink("admin/groups","List Groups",$nav);
        $w->menuLink("admin/lookup","Lookup",$nav);
        $w->menuLink("admin-audit/index","Auditing",$nav);
        $w->menuLink("admin-forms/index","PDF Forms",$nav);
    }

    $w->ctx("navigation", $nav);
}