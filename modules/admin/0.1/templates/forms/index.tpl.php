<?php


print '<p>'.$newFormButton."</p>";

print $formsTable;
?>
<div id="pdfaccordion">
<div>
<h3><a href="#">PDF Fields for Operations</a></h3>
<div>
<?php print $fieldsTable;?>
</div></div>
<div>
<h3><a href="#">PDF Fields for Sales</a></h3>
<div>
<?php print $fieldsTableSales;?>
</div></div>
</div>

<script language="javascript">
	$("#pdfaccordion").accordion({
		header: "h3",
		collapsible: true,
		autoHeight: false,
		activate: false,
		active: -1
		});
</script>
