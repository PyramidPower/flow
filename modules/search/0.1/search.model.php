<?php
// $Id: search.model.php 877 2010-11-12 03:07:36Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia


class SearchService extends DbService {
    function getSearchIndexes() {
        $idx = array();
        if ($this->w->auth->allowed("contact/view")) {
            $idx[]=array("Contacts", "idx_Contact");
        }
        if ($this->w->auth->allowed("sales/gcleadview")) {
            $idx[]=array("Leads", "idx_Form_data_gc" );
        }
        if ($this->w->auth->allowed("vehicle/more")) {
            $idx[]=array("Vehicles", "idx_Vehicle" );
        }
        if ($this->w->auth->allowed("news/view")) {
            $idx[]=array("News", "idx_News" );
        }
        if ($this->w->auth->allowed("wiki/view")) {
            $idx[]=array("Wiki", "idx_WikiPage" );
        }
        if ($this->w->auth->allowed("pages/index")) {
            $idx[]=array("Pages", "idx_Pages" );
        }
        if ($this->w->auth->allowed("asset/edit")) {
            $idx[]=array("Asset", "idx_Asset" );
        }
        if ($this->w->auth->allowed("task/viewtask")) {
            $idx[]=array("Tasks", "idx_Task" );
        }
        if ($this->w->auth->allowed("operations/index")) {
         	$idx[]=array("Operations", "idx_OpsJob" );
        }
        if ($this->w->auth->allowed("sales-utilities/view")) {
         	$idx[]=array("Utilities", "idx_UtilitiesContact" );
        }
        return $idx;
    }

    function & getObjectForIndex($index, $id) {
        $table = str_replace("idx_", "", $index);
        return $this->getObject($table, $id);
    }
}
?>
