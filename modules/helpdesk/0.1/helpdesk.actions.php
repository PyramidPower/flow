<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
function helpdesk_navigation(&$w,$title = null) {
    if ($title) {
        $w->ctx("title",$title);
    }
    $nav = array();
    if ($w->auth->loggedIn()) {
        $nav[]=Html::a($w->localUrl("/helpdesk/new"),"New Tickets");
        $nav[]=Html::a($w->localUrl("/helpdesk/open"),"Open Tickets");
        $nav[]=Html::a($w->localUrl("/helpdesk/create"),"Create Ticket");
        $nav[]=Html::a($w->localUrl("/helpdesk/reports"),"Reports");
    }

    $w->ctx("navigation", $nav);
}
function helpdesk_index_ALL(Web &$w) {
    helpdesk_navigation($w,"Helpdesk");
}
?>