<?php 

class AgendaService extends DbService {
	
/**
 * function getAgs() returns: 
 * 1. null if no class-name
 * 2. object (for  id = int-Number) 
 * 3. array(objects) for $idOrWhere = null
 * 4. array(objects) for $idOrWhere[]
 * 
 **/	
	function & getAgs($ClassName=null, $idOrWhere=null, $orderBy=null)
	{
		if(!$ClassName)
		{
			return null;
		}
		
		  if (is_scalar($idOrWhere)) 
		  {
		  	$whereArray['id']=$idOrWhere;
		  	$whereArray['is_deleted']=0;		
	        $j = $this->getObject($ClassName,$whereArray);  // one object for given id
	      } 
	      elseif (is_array($idOrWhere) ) 
	      {
	      	$whereArray=$idOrWhere;
	      	$whereArray['is_deleted']=0;
	      	$j = $this->getObjects($ClassName,$whereArray);  // array of objects      
	      }
		  elseif (!$idOrWhere ) 
	      {
	      	$whereArray= array('is_deleted'=>0);
	      	$j = $this->getObjects($ClassName,$whereArray);  // array of objects      
	      }
		
	      
	      if(is_array($j) && $orderBy)
	      {
				//usort($j, "cmp_obj");	      
	      }
	      
			return $j;
	}
	
	function cmp_obj($a, $b)
    {
        if ($a->dt_created == $b->dt_created) {
            return 0;
        }
        return ($a->dt_created < $b->dt_created) ? +1 : -1;
    }
	
    
    /**
     * returns all AgScheduiles objs which the user owns or takes part in.
     * To be shown on Schedules-List view.
     * **/
    function getUserScheds()
    {
    	
    	$uid = $this->w->auth->user()->id;
    	$ownScheds = $this->w->Agenda->getAgs('AgSchedule', array('owner_user_id'=>$uid, 'is_group_sch'=>0));
    	
    	// group scheds
    	
    	
    	
    	return  $ownScheds;
    }
    
    
    
    /**
     * Schedules can be selected on /agenda-schedule/ - Calendars view.
     * **/
    function getSelectedScheds($schedsIDs=null)
    {
    	$uid = $this->w->auth->user()->id;
    	    	
    	if(!$schedsIDs){
    		// get default schedules according to user settings 
    		$defSchedSet = $this->w->Agenda->getAgs('AgUserSettings', array('user_id'=>$uid,'title'=>'defaultScheds'));
    		$schedsIDs = array();
    		if($defSchedSet)
    		{
    			foreach ($defSchedSet as $set)
    			{
    				array_push($schedsIDs, $set->sched_id);
    			}
    		}
    	}
    	    	
    	if($schedsIDs){
	    	foreach ($schedsIDs as $id)
	    	{
	    		$sched = null;
	    	 	$sched = $this->w->Agenda->getAgs('AgSchedule', $id);
	    	 	if($sched) $schedArr[] = $sched;
	    	}
    	}
    	
    	return $schedArr;
    }
    
    
    
} // Service class end

   
/**
 * AgEvent can be a part of personal of group schedule
 * 
 * **/
class AgEvent extends DbObject{
	
	var $schedule_id;  // FK (schedule == calendar)
	// user the event assigned to. Can be not the event creator.
	// group event - no user_id. Display in group schedule.
	var $owner_user_id;      
	
	var $title;
	var $type;       // meeting, research... // colors can be assigned by types.
	//var $d_date;
	var $dt_start;   // DATETIME
	var $dt_end;
	
	//  [1,0] busy==1 means time is 'unavailable' on any Group schedules.
	//  if the event belongs to group schedule then it is busy time any way.
	var $busy;  
	
	// multi-days events point to the same row_id all attachments can be available for all of them.
	//var $row_id; // default = 0;  
	
	var $dt_created;
	var $modifier_id;
	var $dt_modified;
	var $is_deleted;
	
	private $_color;  // comes from calendar.
	
//------------------------------------	
	
	function getDbTableName() {
		return "agenda_event";
	}
	
	function __toString()
	{
		return array($this->title, $this->type, formatDate($this->td_start) );
	}
//-------------------------------------
	
	function getInfoStr()
	{
		$start = date('d/m/Y g:i a', $this->dt_start);
		$end = date('d/m/Y g:i a', $this->dt_end);
		
		//$schedTitle = $this->w->Agenda->getAgs('AgSchedule', $this->schedule_id)->title;
		
		return $str = $this->schedule_id."::".$this->owner_user_id."::".$this->title."::".$this->type."::".$start."::".$end."::".$this->busy."::".$this->id;
		
	}
	
	function getColor()
	{
		if(!$this->_color){
			// get user settings
			$uid = $this->w->auth->user()->id;
			$color = $this->w->Agenda->getAgs('AgUserSettings', 
												array('user_id'=>$uid, 'sched_id'=>$this->schedule_id, 'title'=>'schedColor'));
			 
			if(!$color) $this->_color = 'green';
		}
		return $this->_color;
	}
}

    
/**
 * 
 * **/
class AgSchedule extends DbObject{
	
	var $title;
	var $is_group_sch;
	var $owner_user_id;
	var $creator_id;
	
	var $is_deleted;
	
	private $_color;
//------------------------------------------------
	function getDbTableName() {
		return "agenda_schedule";
	}
	
	function __toString()
	{
		return array($this->title, $this->type, formatDate($this->td_start) );
	}
//-------------------------------------------------	
	
	function getColor()
	{
		if(!$this->_color){
			// get user settings
			$uid = $this->w->auth->user()->id;
			$color = $this->w->Agenda->getAgs('AgUserSettings', 
												array('user_id'=>$uid, 'sched_id'=>$this->schedule_id, 'title'=>'schedColor'));
			 
			if(!$color) $this->_color = 'green';
		}
		return $this->_color;
	}
	

	function getDayEvents($dateStamp)
	{
		if(!$dateStamp) return null;
		
		$date = date('Y-m-d', $dateStamp);
		
		$sql = "SELECT * FROM `agenda_event` 
				WHERE `schedule_id`=$this->id 
				AND `is_deleted`='0'  
				AND DATE_FORMAT(`dt_start`, '%Y-%m-%d') = '".$date."'
				";
		
		$events = $this->_db->sql($sql)->fetch_all(); // array
		
		if($events)
		{
			foreach ($events as &$e){
				$e['dt_start'] = strtotime($e['dt_start']);
				$e['dt_end'] = strtotime($e['dt_end']);
			}
		}
		
		return $events;
	}
	
	
	// check if user set this scheduled as a default to show: 
	function isUserDefault($uid = null)
	{
		if(!$uid) return null;
		
		$defaultSchedArr = $this->w->Agenda->getAgs('AgUserSettings', 
									array('user_id'=>$uid, 'sched_id'=>$this->id, 'title'=>'defaultScheds'));
									
	    if($defaultSchedArr[0]->value == '1'){
	    	return true;
	    }
	    
	    return  false;			
	}
	
}


/**
 * User can take part in many Groups
 * **/
class AgUsersInGroups extends DbObject{
	
	var $group_id;
	var $user_id;
	var $role;		//role in group schedule - admin, editor, reader
	
	var $is_deleted;
	
	
	
	function getDbTableName() {
		return "agenda_user_in_group";
	}


}




/**
 * User can take part in many Groups
 * **/
class AgGroupSched extends DbObject{
	
	var $goup_id;
	var $sched_id;
	
	var $is_deleted;
	
	function getDbTableName() {
		return "agenda_group_sched";
	}


}





/*
 * 
 * User can assign colors for chosen categories for chosen schedule.
 * Colors for different schedules as well.
 * Default values can be assigned as soon as schedule created.
 * 
 * */
class AgUserSettings extends DbObject{
	
	var $user_id;
	var $sched_id;
	var $title;
	var $value;
	
	var $dt_created;
	var $modifier_id;
	var $dt_modified;
	var $is_deleted;
		
	// for given user_id and sched_id only 1 record with 'defaultScheds' and title=1 should be set 
	//'title'=>'defaultScheds' 
	// value => 1
	
	// defaultView supposed to be index of item selected from
	// array('Day','Week', '6 Weeks', 'Year', 'Agenda');
	
	//'schedColor' 1 record for sched_id and user_id combination.
	
	// For generic user settings $sched_id == 0,
	// userHrsStart
	// userHrsEnd
	// if a user have strt=8.00 till 18.00 no display
	// 
//-------------------------------------	
	
	function getDbTableName() {
		return "agenda_user_settings";
	}
	
	function __toString()
	{
		return array($this->title, $this->type, formatDate($this->td_start) );
	}
	
//--------------------------------------
	
}


/**
 * Group has a title. Users can be assigned to AgGroups.
 * 
 * **/
class AgGroup extends DbObject{
	
	var $title;
	
	var $creator_id;
	var $dt_created;
	var $modifier_id;
	var $dt_modified;
	
	var $is_deleted;
	
//-----------------------------------------	
	
	function getDbTableName() {
		return "agenda_group";
	}


}



    
    



    
    
class AgendaHoliday extends DbObject{
	
	var $dt_date;    // DATETIME
	var $title;
	//var $state;
	
	var $act;
	var $nsw;
	var $nt;
	var $qld;
	var $sa;
	var $tas;
	var $vic;
	var $wa;
	var $national;
            
	var $dt_created;
	var $is_deleted;
	
	
	function getDbTableName() {
		return "agenda_au_holidays";
	}
	
	function __toString()
	{
		return array($this->title, $this->date, $this->state);
	}
	
}



