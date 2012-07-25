
<div id="ag_errorDiv" style="width:280px; height:30px; font-weight:bold; border:0;"></div>

<div id="formDiv" style="width:300px; float:left; margin-top:20px;">

	<form id="newHolidayForm" action="" style="width:280px; background: #ccc; border: 2px solid green;">
		<p style=" padding: 2px; margin:0; background: #72C267; text-align:center;">Add a new Holiday</p>
		<p style="width: 100%; margin-top:5px; padding-left:2px;">
			<span>Title:</span> <input type="text" id="title" size="30"> 
		</p>
		<p style="width: 100%; margin-top:5px; padding-left:2px;">
			<span>Date:</span>  <input class="date_picker" type="text" name="date"  size="30" id="date"/> 
		</p> 
		
		<?php 
		//($name, $items, $value=null, $class=null, $style=null, $allmsg = "-- Select --")
		$statesArr = getStateSelectArray();
		
		//$statesArr = array_merge($statesArr, array('National'=>'National') );
		
		//$statesChecks = array( 'National'=>"<input type='checkbox' name='stateChs' value='National'>");
		
		//$states = HTML::select('state', $statesArr,'National'); 
		?>
		
		<p style="width: 100%; margin-top:5px; padding-left:2px;">
			<input type='checkbox' id='national' value='national'><label for="national">National</label><br />
			<?php 
				foreach($statesArr as $s)
				{
					$n = strtolower($s[0]);
					echo "<input type='checkbox' id='$n' value='$n'><label for='stateChs'>{$s[0]}</label><br />";  
				}
			?>
			 
		</p>
		
		<p style="text-align:center;"><input type="submit" id="newHolidaySubmit" value="Save"> </p>
	</form>
</div>


<div id="ag_info" style="float:left; padding:0;">
	<div id="controls">
		<p>
		
		<?php 
		
		$select = "<select id='ag_selectLink'>";  
		
		$urls = $w->Admin->getObjects( 'Lookup', array('type'=>'AgendaHolidaysURL'));
		if($urls)
		{
			foreach ($urls as $url){
				$selectList[$url->code] = $url->title;
				
				$select .= "<option value='$url->code'>$url->title</option>";
			}
			 
			$select .= "</select>";
			
		}
			echo $select;
		
		?>
		 
		<button id="ag_showLinkContent" value="Display">Display</button>
		</p>
		
	</div>
	<div id="ag_cont" style="width:1200px; height:800px; ">
		<iframe id="ag_infoIframe" src="" style="border: none; width:100%; height:100%;">
		</iframe>
		
	</div>
</div>






<script type="text/javascript">

$('.date_picker').datepicker({dateFormat: 'dd/mm/yy'});
$('.date_picker').keyup( function(event) { $(this).val('');} );

var ll = "<img src="+'<?=$webroot?>'+"/img/ajax-loader.gif />";


 $("#ag_showLinkContent").click(function(){

		var link = $('#ag_selectLink').val();

		$('#ag_infoIframe').attr('src',link);

	 });



	$("#newHolidaySubmit").live('click', function(e){

		var form = $('#formDiv').html();
		$(this).attr("disabled", true);
		e.preventDefault();

		// clear error message if it was any:
		$('#errorDiv').text("");

	       
	    var title = $('#title').val();      
	    var date = $('#date').val();
	   	   
	   var n = $('#national').is(':checked') ? 1 : 0 ;

	   var act = $('#act').is(':checked') ? 1 : 0 ;
	   var nsw = $('#nsw').is(':checked') ? 1 : 0 ;
	   var nt = $('#nt').is(':checked') ? 1 : 0 ;
	   var qld = $('#qld').is(':checked') ? 1 : 0 ;
	   var sa  = $('#sa').is(':checked') ? 1 : 0 ;
	   var tas = $('#tas').is(':checked') ? 1 : 0 ;
	   var vic = $('#vic').is(':checked') ? 1 : 0 ;
	   var wa  = $('#wa').is(':checked') ? 1 : 0 ;

	   // console.log('title: '+title+' d: '+date+' n: '+n);

	   
	    var ajaxURL = "/agenda-holidays/ajaxAddHoliday"; 
	      
	    $(this)
	    //.html("Updating "+ll)
	    	.load(ajaxURL, {title: title, dt_date: date, 
		    	            national:n, act:act, nsw:nsw, nt:nt, qld:qld,
		    	            sa:sa, tas:tas, vic:vic, wa:wa}, function(response){

		    	
				if(response != 'ok'){ 
					$('#ag_errorDiv').text("error");
					$('#ag_errorDiv').css('border','1px solid red');
				}else{
					$('#ag_errorDiv').text("Holiday Added.");
					$('#ag_errorDiv').css('border','1px solid blue');
					}

				var title = $('#title').val('');      
			    var date = $('#date').val('');
			    
				//$('#formDiv').html(form);
				$("#newHolidaySubmit").attr("disabled", false);

				//$('.date_picker').datepicker({dateFormat: 'dd/mm/yy'});
				//$('.date_picker').keyup( function(event) { $(this).val('');} );
					
		    });
	}) ;
	

</script>