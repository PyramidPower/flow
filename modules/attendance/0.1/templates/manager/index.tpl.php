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
		<a id="tab-link-1" href="#" class="active"	onclick="switchTab(1);">Employees</a>
	</div>
	<div class="tab-body">
		<div id="tab-1">
			<?php echo Html::box("/attendance-manager/addemployees/"," Add Employees ",true) ?>
			<p>
			<?php echo $users; ?>
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