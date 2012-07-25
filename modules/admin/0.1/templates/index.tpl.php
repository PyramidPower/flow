<b>Users Currently Logged In</b>
<ul>
<?if ($currentUsers):foreach($currentUsers as $u):?>
<li>
<?=$u->getFullName()?>
</li>
<?endforeach;endif;?>
</ul>

<script language="javascript">
	$("#accordion").accordion({
		header: "h3",
		collapsible: true,
		autoHeight: false,
		activate: false,
		active: -1
		});
</script>
