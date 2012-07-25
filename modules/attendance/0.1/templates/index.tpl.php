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
		<a id="tab-link-1" href="#" class="active"	onclick="switchTab(1);">Attendance Dashboard</a>
		<a id="tab-link-2" href="#" onclick="switchTab(2);">Time Sheet</a>
	</div>
	<div class="tab-body">
		<div id="tab-1">
			<?php echo $user; ?>
		</div>
		<div id="tab-2" style="display: none;">
			<?php echo $weeknav; ?>
			<p>
			<?php echo $strweek; ?>
			<p>
			<?php echo $timesheet; ?>
		</div>
	</div>
</div>

<script language="javascript">
<?php 
if ($_REQUEST['tab'] && (!empty($_REQUEST['tab']))) {
	echo "	switchTab(" . $_REQUEST['tab'] . ");";
}
?>
</script>