<?php


?>

<div class="tabs">
	<div class="tab-head">
		<a class=active href="#">View Mail Merge</a>
	</div>
	<div class="tab-body">
		<div>
			<?php echo $editmm; ?>
			<p>
		</div>
	</div>
</div>

<script type='text/javascript'>
    CKEDITOR.replace( 'mmbody' ,
    {
        toolbar : 'Full'
    });
</script>
