<?php

?>

<div class="tabs">
	<div class="tab-head">
		<a href="#" class="active">Report Dashboard</a>
	</div>
	<div class="tab-body">
		<div>
		   <form id="leadfilter" action="<?=$webroot."/report/index"?>" method="POST">
				<fieldset style="margin-top: 10px;">
					<legend>Search Reports</legend>
						<table cellpadding=2 cellspacing=2 border=0>
							<tr>
								<td align=right style="padding-left:20px;">Flow Modules</td><td><?php echo $flowmodules; ?></td>
								<td align=right style="padding-left:20px;">Category</td><td><?php echo $category; ?></td>
								<td align=right style="padding-left:20px;">Type</td><td><?php echo $type; ?></td>
								<td align=right><input type="submit" name="taskFilter" value=" Search Reports "/></td>
								<td align=left><button id="clrForm">Reset Filter</button></td>
							</tr>
						</table>
				</fieldset>
			</form>
		    <p>
			<?php echo $viewreports; ?>
		</div>
	</div>
</div>

<script language="javascript">
	var myFlag = true;
	var module_url = "/report/reportAjaxListModules";
	
	$(document).ready(function() {
		$.getJSON(
			module_url + $(this).val(),
			function(result) {
				$('#flow_module').parent().html(result);
				$("select#flow_module").val("<?php echo $reqFlowModule; ?>");
				$("select[id='flow_module']").trigger("change");
				}
			);
	});
	
	$.ajaxSetup ({
	    cache: false
		});

	$("#clrForm").click(function(e) {
		e.preventDefault();
		myFlag = false;
		$("select#flow_module").val("");
		$("select[id='flow_module']").trigger("change");
		}
	);
	
	var cat_url = "/report/reportAjaxModuletoCategory?id="; 
	$("select[id='flow_module']").live("change",function() {
		$.getJSON(
			cat_url + $(this).val(),
			function(result) {
				$('#category').parent().html(result);
				if (myFlag)
					$("select#category").val("<?php echo $reqCategory; ?>");
				$("select[id='category']").trigger("change");
				}
			);
		}
	);

	var type_url = "/report/reportAjaxCategorytoType?id="; 
	$("select[id='category']").live("change",function() {
		$.getJSON(
			type_url + $(this).val() + "_" + $("select[id='flow_module']").val(),
			function(result) {
				$('#type').parent().html(result);
				if (myFlag)
					$("select#type").val("<?php echo $reqType; ?>");
				}
			);
		}
	);

</script>
