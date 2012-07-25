// JavaScript Document

var current_tab = 1;
        function switchTab(num){
        	// when changes in EditForm have been made - no navigation untill saved
        	// EditForm.children.change
        	if (is_changed) return;   
            if (num == current_tab) return;
            $('#tab-'+current_tab).hide();
            $('#tab-link-'+current_tab).removeClass("active");
            $('#tab-'+num).show().addClass("active");
            $('#tab-link-'+num).addClass("active");
            current_tab = num;

            function showhide(sid, hid) {
                $('div#'+hid).hide();
                $('div#'+sid).fadeIn(1000);
            }

        }

//        //   Agency calendar-schedule
//        //  set agency_id and submit the form
//        $('.cal_job_div_wrapper').click(function(event){ 
//        	
//        	var aid = $(this).children().filter(":input[name='agency_id']").val();
//        	var dateSQL = $(this).children().filter(":input[name='dateSQL']").val();
//        	
//        	$('#form_agency_id').val(aid);
//        	$('#form_dateSQL').val(dateSQL);
//        	var a2 = $('#form_dateSQL').val();
//        	//alert(dateSQL);
//        	$('#scheduleJobToTeam').submit();
//        });
        
     
        // in use on Agencies list page (agency.tpl.) and edit - areas assign tab (editAgency.tpl.)
        $("#check_all").click(checkAllCheckboxes);
        
        function checkAllCheckboxes()
        {
        	if($(this).attr('checked')){
        		$('.checkbox').attr("checked",true);
        	}
        	else{
        		$('.checkbox').attr("checked",false);
        	}
        }


       var is_changed = 0;
       var del = "";
     
    	
        // Tracking changes made in form with id=#EditForm - for managers or admins. 
        // Form without id and Save button (for members) will not be tracked.
        // If any changes - prevent navigation from the page:    
      	$('#EditForm').children().change(function(event){
    	  is_changed = 1;
    	 // alert('fre');
    	  });
      	
           	
        $("#top-nav a, .left-nav a, .tab-head a").click(function(event){  
        	if (is_changed){
        		alert('Please save the changes with Save button \n');
    			event.preventDefault(); 
    		  } 
          }); 
        

        
        
//        $('#cancel').click( function(event) {   
//  		  location.href = "/operations-agency/";
//         }).confirm();

/****************************************
 * 
 * 			Ajax calls
 * 
 * **************************************/        
 
    	$.ajaxSetup (
    			{
    				cache: false
    			});
        
  // IN USE in operations.team.php, attached in addTeamMember.tpl.php      
        var selectURL = "/operations-member/checkMemberAjax/";
        var aid = $('[name=agency_id]').val();
        
        $("select[id='user_id'] option").click(function(event)
        		{
	    			//alert(selectURL);
        	
        				$.getJSON(
		    					selectURL + aid + "/" + $(this).val(),
		    					function(result)
		    					{
		    						if(result) alert(result);   						
		    					}
	     					);
    		
        		}); // end c
 
        
        

        
        
        
        
        
  /**************************************************************
  *															
  *	 POP-Up dialog for operations.job.actions.php editJob()
  * 	
  **************************************************************/															//
        
        var $dialog = $('<div id="dialog"></div>').html('')
        .dialog({
                  autoOpen: false,
                  width: 550,
                  position: ['right','top'],
                  resizable: true,
                  title: 'Details'
               });

        var details = $('#JobQuoteDetails').html();
        
		$('.pop-up').click(function() {
			$dialog.html(details);
			$dialog.dialog('open');
		// prevent the default action, e.g., following a link
		return false;
		});
  ///															//
  ////////////////////////////////////////////////////////////////      

     
     
        // the following is not good as the check_all checkbox itself is not checked ?!
        
// 	  $("#check_all").toggle(
//      function(){
//  		    $('.checkbox').attr("checked",true);
//  		    $('#check_all').attr("checked","checked");
//  		    },
////      //
//  		    function(){
//  		    $('.checkbox').attr("checked",false);
//  		    $('#check_all').attr("checked",false);
// 		    }
//      
//  		  );
        
        
//      $('#submit').click(function(event){
//   	     //return confirm('Save ?');
//   	    	     if($('#location_id').val()=="")   { alert ("Please Select Location");      return false;}
//   	    	    //var p_price = $('#purchase_price').val();
//   	    	    // if(!parseFloat(p_price, 10)>0){alert ("Please input Purchase Price"); return false;}
//   	    	     return true;
//   	         });   
    
   