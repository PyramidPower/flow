<?php

?>

<script type="text/javascript">
    var current_tab = 1;
    function switchTab(num){
        if (num == current_tab) return;
        $('#tab-'+current_tab).hide();
        $('#tab-link-'+current_tab).removeClass("active");
        $('#tab-'+num).show().addClass("active");
        $('#tab-link-'+num).addClass("active");
        current_tab = num;
    }
</script>
<div class="tabs">
	<div class="tab-head">
		<a id="tab-link-1" href="#" class="active"	onclick="switchTab(1);">Task Dashboard</a>
		<a href="/task/tasklist">Task List</a>
	</div>
	<div class="tab-body">
		<div id="tab-1">
			Please review your Task List below by clicking on the Task Group titles. Figures show <b>Your Tasks / All Group Tasks</b>.
			<p>
			<?php echo $grouptasks; ?>
			<p>
		</div>
	</div>
</div>

<script language="javascript">
<?php 
	if ($_REQUEST['tab'] && (!empty($_REQUEST['tab']))) {
		echo "	switchTab(" . $_REQUEST['tab'] . ");";
	}
?>

	$("#accordion").accordion({
		header: "h3",
		collapsible: true,
		autoHeight: false,
		activate: false,
		active: -1
		});
	
	$.ajaxSetup ({
	    cache: false
		});

	var task_url = "/task/taskAjaxSelectbyTaskGroup?id="; 
	$("select[id='task_group_id'] option").click(function() {
		$.getJSON(
			task_url + $(this).val(),
			function(result) {
				$('#task_type').parent().html(result[0]);
				$('#priority').parent().html(result[1]);
				$('#first_assignee_id').parent().html(result[2]);
				}
			);
		}
	);
</script>
