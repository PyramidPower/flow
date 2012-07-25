<?php
class HelpdeskService extends DbService {

    function getTicketStatuses() {
        return array(
            array("New",1),
            array("Assigned",2),
            array("In Progress",3),
            array("Need More Info",4),
            array("Deferred",5),
            array("Resolved",6),
            array("Rejected",7)
            );
    }

    function getTicketPriorities() {
        return array(
            array("Critical",1),
            array("High",2),
            array("Normal",3),
            array("Low",4)
            );
    }

    function & getTickets($user_id=null,$status=null) {
        $this->_db->get("helpdesk_ticket")->where("is_closed",0);
        if ($status) {
            $this->_db->and("current_status",$status);
        }
        if ($user_id) {
            $this->_db->and("current_assigned_user_id",$user_id);
        }
        $rows = $this->_db->order_by("current_priority")->fetch_all();
        if ($rows){
            $list = array();
            foreach ($rows as $row) {
                $list[]=$this->getObjectFromRow("Helpdesk_ticket", $row);
            }
            return $list;
        }
        return null;
    }

    function getCategoriesForSelect(){
        $rows = $this->_db->sql("select * from helpdesk_category where is_active = 1 order by parent_id asc")->fetch_all();
        if ($rows){
            $select = array();
            foreach($rows as $row) {
                $c = $this->getObjectFromRow("Helpdesk_category", $row);
                $select[]=array($c->getPath(),$c->id);
            }
            return $select;
        }
    }
}

class Helpdesk extends DbObject {
    var $title;
    var $dt_created;
    var $is_active;
    var $owner_id;
}

class Helpdesk_ticket extends DbObject {
    var $helpdesk_id;
    var $dt_created;
    var $dt_modified;
    var $raised_by_user_id;
    var $current_assigned_user_id;
    var $current_status;
    var $current_priority;
    var $is_closed;
    var $subject;
    var $description;
    
    var $partner_id;
    var $project_id;
    var $job_id;

    var $category_id;

    var $md_0;
    var $md_1;
    var $md_2;
    var $md_3;
    var $md_4;
    var $md_5;
    var $md_6;
    var $md_7;
    var $md_8;
    var $md_9;
}

class Helpdesk_ticket_update extends DbObject {
    var $helpdesk_ticket_id;
    var $dt_created;
    var $status;
    var $priority;
    var $comment;
    var $assigned_user_id;
    var $update_user_id;
    var $category_id;

    // free form metadata based on category
    var $md_0;
    var $md_1;
    var $md_2;
    var $md_3;
    var $md_4;
    var $md_5;
    var $md_6;
    var $md_7;
    var $md_8;
    var $md_9;
}

class Helpdesk_category extends DbObject {
    var $helpdesk_id;
    var $parent_id;
    var $title;
    var $description;
    var $is_active;

    var $md_0_title;
    var $md_0_type;
    var $md_0_values; // allowed values, leave blank for no validation
    var $md_0_help; // help text

    var $md_1_title;
    var $md_1_type;
    var $md_1_values; // allowed values, leave blank for no validation
    var $md_1_help; // help text

    var $md_2_title;
    var $md_2_type;
    var $md_2_values; // allowed values, leave blank for no validation
    var $md_2_help; // help text

    var $md_3_title;
    var $md_3_type;
    var $md_3_values; // allowed values, leave blank for no validation
    var $md_3_help; // help text

    var $md_4_title;
    var $md_4_type;
    var $md_4_values; // allowed values, leave blank for no validation
    var $md_4_help; // help text

    var $md_5_title;
    var $md_5_type;
    var $md_5_values; // allowed values, leave blank for no validation
    var $md_5_help; // help text

    var $md_6_title;
    var $md_6_type;
    var $md_6_values; // allowed values, leave blank for no validation
    var $md_6_help; // help text

    var $md_7_title;
    var $md_7_type;
    var $md_7_values; // allowed values, leave blank for no validation
    var $md_7_help; // help text

    var $md_8_title;
    var $md_8_type;
    var $md_8_values; // allowed values, leave blank for no validation
    var $md_8_help; // help text

    var $md_9_title;
    var $md_9_type;
    var $md_9_values; // allowed values, leave blank for no validation
    var $md_9_help; // help text



    function & getParent() {
        return $this->getObject("Helpdesk_category", $this->parent_id);
    }

}

?>
