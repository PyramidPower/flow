	<form id="newHolidayForm" action="/agenda-holidays/editHoliday/<?=$h->id?>" method="POST" style="width:280px; background: #ccc; border: 2px solid green;">
		
		<p style=" padding: 2px; margin:0; background: #72C267; text-align:center;">Edit Holiday</p>
		<p style="width: 100%; margin-top:5px; padding-left:2px;">
			<span>Title:</span> <input type="text" name="title" id="title" size="30" value='<?=$h->title?>' /> 
		</p>
		<p style="width: 100%; margin-top:5px; padding-left:2px;">
			<span>Date:</span>  <input class="date_picker" type="text" name="dt_date"  size="30" id="date" value='<?=date('d/m/Y', $h->dt_date);?>' /> 
		</p> 
		
		<?php 
		//($name, $items, $value=null, $class=null, $style=null, $allmsg = "-- Select --")
		$statesArr = getStateSelectArray();
		?>
		<p style="width: 100%; margin-top:5px; padding-left:2px;">
			
			<?php $ch = ($h->national == 1) ? 'checked="checked"' : "";
				echo "<input type='checkbox' name='national' $ch><label for='national'>National</label><br />";
			 
				foreach($statesArr as $s)
				{
					$n = strtolower($s[0]);
					
					$checked = ($h->$n==1) ? 'checked="checked"' : "";
					echo "<input type='checkbox' name='states[]' value='$n' $checked><label for='stateChs'>{$s[0]}</label><br />";  
				}
			?>
			 
		</p>
		
		<p style="text-align:center;"><input type="submit" id="newHolidaySubmit" value="Save"> </p>
	</form>
	
<script type="text/javascript">

	$('.date_picker').datepicker({dateFormat: 'dd/mm/yy'});
	$('.date_picker').keyup( function(event) { $(this).val('');} );


</script>