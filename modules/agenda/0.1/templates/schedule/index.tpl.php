<div id="schedulesSet" style="width:100%; margin-bottom:10px;">

	<?php /* 
		if($checks)
		{
			foreach ($checks as $ch)
			{
				echo $ch;
			}
		} */
	?>
	
	<!-- label for='checkAll' 
			style='
					border: 1px solid green; 
					padding: 3px 10px 3px 10px; 
					margin-left: 4px;
					border-radius: 10px;
					-moz-border-radius: 10px;
					-webkit-border-radius: 10px;
					'> ALL
		<input type='checkbox' value='all' id='checkAll' name="schedules[]" /></label>
	<button id="displaySchedules">Display</button>
	
	<br -->
</div>

<!-- a href="<?=$webroot?>/agenda-scheduleDay/ajaxCurrDay/<?=time()?>">Test Current Day</a -->


<div id="tabs">
		<ul>
			<li><a href="/agenda-scheduleDay/scheduleDayView">Day</a></li>
			<li><a href="#b">Week</a></li>
			<li><a href="#c">6 Weeks</a></li>
			<li><a href="#y">Year</a></li>
			<li><a href="#a">Agenda</a></li>
			<li><a href="/agenda-settings/userSettings">Settings</a></li>
		</ul>
		
		<div id="a">This is the content panel linked to the first tab,
		it is shown by default.</div>
		
		<div id="b">Week</div>
					
		<div id="c">6 week</div>
					
		
</div>


<div id='addEventDialog'>

<?php 

	$uid = $w->auth->user()->id;
	$schedsSelect = Html::select("schedsSelect",$scheds);
	
	print "<input type='hidden' id='user_id' value='{$uid}' />";
	
	
	print "Title: <input type='text' id='title' size='70' /><br />";
	
	print "<table style='width: 300px;'>";
	print '<tr><td>Select Schedule:</td><td>'.$schedsSelect.'</td></tr>';
	print "<tr><td>Type:</td><td><input type='text' id='type' size='10' /></td></tr>";
	print "<tr><td>Start:</td><td>".Html::datetimePicker('eventStart')."</td></tr>";
	print "<tr><td>End:</td><td>".Html::datetimePicker('eventEnd')."</td></tr>";
	print "<tr><td colspan='2'><label for='busy'>I am not available.</label><input type='checkbox' id='busy' ></td></tr></table>";
?>

</div>

<div id='editEventDialog'>
<?php
    print "<input type='hidden' id='e_id' value='0' />";        	
	print "<input type='hidden' id='user_id' value='{$uid}' />";

	print "Title: <input type='text' id='title' size='80' /><br />";
	
	print "<table style='width: 300px;'>";
	print '<tr><td>Select Schedule:</td><td>'.$schedsSelect.'</td></tr>';
	print "<tr><td>Type:</td><td><input type='text' id='type' size='10' /></td></tr>";
	print "<tr><td>Start:</td><td>".Html::datetimePicker('eventEditStart').'</td></tr>';
	print "<tr><td>End:</td><td>".Html::datetimePicker('eventEditEnd').'</td></tr>';
	print "<tr><td cplspan='2'><input type='checkbox' id='busy'><label for='busy'>I am not available.</label></td></tr></table>";
?>
</div>


<script type="text/javascript">


// loading image:
var ll = "<img src="+'<?=$webroot?>'+"/img/ajax-loader.gif />";

	
// set date to current at first:
var currDate = new Date();  //

// keep and update currStamp for navigating (passing to Ajax)
// the update to prev/next date happens on server side, then
// unix is to be updated to returned date:
var unix = Math.round(+new Date()/1000);
var url=""; 

//console.log('Date '+currDate+' Stamp '+unix);

//var sch = $(':checkbox').each().val();
var schedulesView = [];
//$('input:checkbox[name="schedules[]"]:checked').each(function(index) { schedulesView.push($(this).val());});







$(function(){

	
	$('input:checkbox[name="schedules[]"]:checked')
	.each(function(index) { 
			schedulesView.push($(this).val());
			});

	//--------------------------------------------------------------
	//   Tabs
	//--------------------------------------------------------------
	/*
		Schedule checkboxes can be clicked and any tab open.
		Then apropriate tab should be reloaded.
	*/
	$("#tabs").bind('tabsload',function(event, tab){

		switch(tab.index)
        {
			// because of Prev curr Next navigation on the page.
			// initial method should be called to have CurrentDay(or week or other) loded into the view. 
			case 0:	reloadDayContent("/agenda-scheduleDay/ajaxCurrDay/"+unix);
						break;
            case 1: //reloadWeekContent();
            			break;
            case 2: //reload6WeeksContent();
            			break;
            case 5: //
    			    break;
            default: 
            			alert('tabs load listener default - error');
        }

				/*
				if(index==0)
				{
					//reloadDayContent("/agenda-scheduleDay/ajaxCurrDay/"+unix);
				}
				*/

		});
			

	var tabOpts = {
			selected: null,
			collapsible: true, // 
			// set function to be called on select
			//select:handleSelect
			};

	//----------------------------------
	// creating widgit :
	$("#tabs").tabs(tabOpts);

	
	//---------------------------------------------------
	//		Display button functionality
	//---------------------------------------------------
	$("#displaySchedules").live('click', 
		function(){

		// 1. update schedulesView array:
		$('input:checkbox[name="schedules[]"]:checked')
			.each(function(index) { 
					schedulesView.push($(this).val());
					});

		
			//console.log('=======    Sschedules Array: ' + schedulesView);
			
			// 2. 	refresh open tab
			index = $("#tabs").tabs("option", "selected");
	
			switch(index)
		        {
					case 0:		reloadDayContent("/agenda-scheduleDay/ajaxCurrDay/"+unix);
								break;
		            case 1: 	//reloadWeekContent();
		            			break;
		            case 2: 	//reload6WeeksContent();
		            			break;
		            default: 
		            			$('#tabs').tabs('select', 0);
		            			//reloadDayContent("/agenda-scheduleDay/ajaxCurrDay/"+unix);
		        }

		});

	// -----------------------------------------
	//  Selecting a tab to open on page loading
	//------------------------------------------
	// $tab can be set on return from POST method,
	// but if it was not return from POST then open Default view 
	// according to user settings.
	// If non set - non open:
	var requestedTab = <?=$_REQUEST['tab'] ? $_REQUEST['tab'] : -1?>;
	var defaultView = <?=$defaultView>-1 ? $defaultView : -1 ?>;
	
	if(requestedTab > -1 ){
		$('#tabs').tabs('select', requestedTab);
	}else{
		if(defaultView > -1){
			$('#tabs').tabs('select', defaultView);
		}
	}
	
	}); // end of document ready function


	
//----------------------------------------------------------
//Day  Day view 
//----------------------------------------------------------


    //------------------------------------------------------
    // Day  Navigation
    //------------------------------------------------------
    
    // Date data to show the current day in navigation:
	var cDay=currDate.getDate();
	var cMonth=currDate.getMonth() + 1;
	var cYear=currDate.getFullYear();
		
    $('#currDateSpan').text("start with Today: "+cDay + "/" + cMonth + "/" + cYear); 

    $('#prevDay').live('click',function(e){
    	// make Ajax call to .php and update Day content
    	reloadDayContent("/agenda-scheduleDay/ajaxPrevDay/"+unix);
	});


    $('#nextDay').live('click',function(e){
		// make Ajax call to .php and update Day content
    	reloadDayContent("/agenda-scheduleDay/ajaxNextDay/"+unix);
	});


    
    //----------------------------------------------------
    // Reloading Day View Content
    //---------------------------------------------------
 	
	function reloadDayContent(url)
	{
		// show loader, then get new day 
		$('#scheduleDiv').html("Updating "+ll);

		//console.log("------ " + schedulesView);
		 
			$.ajax({
				 url: url,
				 type: "POST",
				 data: {
				      //  schedules: schedulesView,  // with 'traditional: true' Arrays will be in JASON format
				        unixT: unix
				    },
				 traditional: true,
				 success: function (response){
							var rObj = jQuery.parseJSON(response);

							// update time current date stamp
							unix = rObj.updatedDateStamp;
							// get rid of loader and update Date, day displayed														
							$('#currDateSpan').html(rObj.date);
							//console.log('Response date: ' + rObj.date);
							$("#scheduleDiv").html(rObj.htmlDay);
							//$("#scheduleDiv").append(rObj.dep);
							$("#scheduleDiv").append(rObj.noArray);
						
					}
				});
	}

//----------------------------------------------------------------------
//  addEvent   Dialog
//----------------------------------------------------------------------
	// to be filled on addEvent() and set as initial event Date
	var eventDate=null;

	addEvent = function(e){
			//console.log('gogo');
			eventDate = $(this).attr('id');
			$("#addEventDialog").dialog("open");
		};
		
	$('.addEvent').live('click', addEvent);

	
	var eventData = new Array();
	
	var create = function() {
		//console.log('CREATING');

		/*
		
		eventData['start'] = $("#addEventDialog input[id=eventStart]").val();
		
			parts = eventData['start'].split(' ');
			date = parts[0].split('/');
			d = date[0];
			m = date[1];
			y = date[2];
			
			time = parts[1].split(':');
			hr = time[0];
			min = time[1];
			if(parts[2]=='pm'){ hr = parseInt(hr) + 12; }

			var datum = new Date(Date.UTC('2009','01','13','23','31','30'));
			ts = datum.getTime()/1000;
			
			console.log('event start d: '+d + " m: " + m + " y: "+ y +" hr: "+hr+" min: "+min);
	
			//var ts = Date.UTC(y,m,d,hr,min) / 1000;
			console.log('event start ts: '+ts);
	*/
			

		urlAddEvent = "/agenda-schedule/ajaxCreateEvent/";

		$.ajax({
			 url: urlAddEvent,
			 type: "POST",
			 data: {
			          // with 'traditional: true' Arrays will be in JASON format
			        owner_user_id: 	$("#addEventDialog input[id=user_id]").val(),
			        schedule_id: 	$("#addEventDialog option:selected").val(),
			        title: 			$("#addEventDialog input[id=title]").val(),
			        start: 			$("#addEventDialog input[id=eventStart]").val(),
				    end: 			$("#addEventDialog input[id=eventEnd]").val(),
				    type:			$("#addEventDialog input[id=type]").val(),
			    	busy: 			$("#addEventDialog input[id=busy]").val()== 'on' ? 1 : 0
			    },
			 traditional: true,
			 success: function (response){
						
				 	$("#addEventDialog").dialog("close");

				 	reloadDayContent("/agenda-scheduleDay/ajaxCurrDay/"+unix);

				}
			});

	}

	

	
	var cancel = function() {
		$("#addEventDialog").dialog("close");
	}

	//----------------
	// options for Add event
	var dialogOpts = {
			autoOpen: false,
			width: 600,
			height: 350,
			title: 'Add Event',
			//modal: true,
			buttons: {
				"Create": create,
				"Cancel": cancel,
			},
			open: function() {
				// Clean form elements:
				$("#addEventDialog input[id=title]").val(''),
			    $("#addEventDialog input[id=eventStart]").val(''),
				$("#addEventDialog input[id=eventEnd]").val(''),
				$("#addEventDialog input[id=type]").val(''),
			    $("#addEventDialog input[id=busy]").val('')
				}
		};
	

	// Start ADD EVENT dealog:
	$("#addEventDialog").dialog(dialogOpts);





	
	//-------------------------------------------------------
	//		editEvent dialog
	//-------------------------------------------------------
	var eventInfoStr = null;
	
	editEvent = function(e){
			eventDate = $(this).attr('id');
			eventInfoStr = $(this).attr('name');
			$("#editEventDialog").dialog("open");
			
		};
		
	$('.editEvent').live('click', editEvent);

	//var eventData = new Array();
	
	var edit = function() {

		//console.log('EDITING');
		urlAddEvent = "/agenda-schedule/ajaxEditEvent/";
		

		$.ajax({
			 url: urlAddEvent,
			 type: "POST",
			 data: {
				// with 'traditional: true' Arrays will be in JASON format
				    eid:            $("#editEventDialog input[id=e_id]").val(),
			        owner_user_id: 	$("#editEventDialog input[id=user_id]").val(),
			        schedule_id: 	$("#editEventDialog option:selected").val(),
			        title: 			$("#editEventDialog input[id=title]").val(),
			        start: 			$("#editEventDialog input[id=eventEditStart]").val(),
				    end: 			$("#editEventDialog input[id=eventEditEnd]").val(),
				    type:			$("#editEventDialog input[id=type]").val(), 
			    	busy: 			$("#editEventDialog input[id=busy]").val()== 'on' ? 1 : 0
			    },
			 traditional: true,
			 success: function (response){

				  	$("#editEventDialog").dialog("close");

				 	reloadDayContent("/agenda-scheduleDay/ajaxCurrDay/"+unix);

				}
			});

	}

	

	
	var cancelEdit = function() {
		$("#editEventDialog").dialog("close");
	}

	var deleteEvent = function(){
		if(confirm('DELETE event ?')){

			//console.log('EDITING');
			urlDelEvent = "/agenda-schedule/ajaxDeleteEvent/";
			

			$.ajax({
				 url: urlDelEvent,
				 type: "POST",
				 data: {
					// with 'traditional: true' Arrays will be in JASON format
					    eid:            $("#editEventDialog input[id=e_id]").val(),
				        owner_user_id: 	$("#editEventDialog input[id=user_id]").val(),
				        schedule_id: 	$("#editEventDialog option:selected").val(),
				        title: 			$("#editEventDialog input[id=title]").val(),
				        start: 			$("#editEventDialog input[id=eventEditStart]").val(),
					    end: 			$("#editEventDialog input[id=eventEditEnd]").val(),
					    type:			$("#editEventDialog input[id=type]").val(), 
				    	busy: 			$("#editEventDialog input[id=busy]").val()== 'on' ? 1 : 0
				    },
				 traditional: true,
				 success: function (response){

					    $("#editEventDialog").dialog("close");

					 	reloadDayContent("/agenda-scheduleDay/ajaxCurrDay/"+unix);

					}
				});
			}
	}

	//----------------
	// options for Add event
	var dialogOptsEdit = {
			autoOpen: false,
			width: 600,
			height: 350,
			title: 'Edit Event',
			//modal: true,
			buttons: {
				"Save changes": edit,
				"Cancel changes": cancelEdit,
				"Delete event": deleteEvent
			},
			open: function() {
				
				// event info from AgEvent->GetInfoStr(): 
				//return $this->schedule_id."::".$this->owner_user_id."::".$this->title."::".$this->type."::".$start."::".$end."::".$this->busy;
				info = eventInfoStr.split('::');
				schedTitle = info[0];
				
				// go via all option and make one selected:
		        $("#editEventDialog option").each(function(){
					if($(this).val() == schedTitle){
						//console.log('option ' + $(this).val());
						$(this).attr('selected','selected');
						}
			    });

			    busy = info[6] == 1 ? 'on' : 'off';

			    $("#editEventDialog input[id=e_id]").val(info[7]);
		        // ownership as before:
		       	$("#editEventDialog input[id=user_id]").val(info[1]);
		       				
		        $("#editEventDialog input[id=title]").val(info[2]);
		        $("#editEventDialog input[id=eventEditStart]").val(info[4]);
			    $("#editEventDialog input[id=eventEditEnd]").val(info[5]);
			    $("#editEventDialog input[id=type]").val(info[3]);
			    $("#editEventDialog input[id=busy]").val()== 'on' ? 1 : 0;
				}
		};
	

	// Start EDIT EVENT dealog:
	$("#editEventDialog").dialog(dialogOptsEdit);
	
	
	//-------------------------------------------------------
	function toTimestamp(year,month,day,hour,minute,second){
		 var datum = new Date(Date.UTC(year,month-1,day,hour,minute,second));
		 return datum.getTime()/1000;
		}

	$('head').append('<link rel="stylesheet" type="text/css" href="/css/operations/schedule.style.css"/>	');

</script>





	
	
	
	
	
	
	
	