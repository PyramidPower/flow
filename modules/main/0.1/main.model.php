<?php

class MainService extends DbService {
	// look for a 'portal.tpl.php' templates for each module
	function & loadPortalTemplates() {
        $handlers = $this->w->handlers();
        foreach ($handlers as $model) {
            $file = $this->w->getHandlerDir($model)."templates/portal.tpl.php";
            if (file_exists($file)) {
            	$files[] = $file;
            }
        }
		return $files;
    }

    // return nicely formatted module titles
    function & getTitle($title) {
    	switch ($title) {
    		case "news":
				$cnt = $this->w->News->getUserNewsCount();
				$cnt = ($cnt != "") ? $cnt : "0";
				$title = strtoupper($title) . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $cnt . " unread news items";
    			break;
   			case "inbox":
    			$new_total = $this->w->Inbox->getNewMessageCount($this->w->Auth->user()->id);
				$new_total ? $new_total = $new_total['COUNT(*)'] : "0";
				$title = strtoupper($title) . "&nbsp;&nbsp;-&nbsp;&nbsp;" . $new_total . " unread messages";
				break;
    		case "task":
				$title = "TASKS - the last 7 days activity";
    			break;
    		default:
				$title = strtoupper($title);
    			break;
    	}
    	return $title;
    }
}
