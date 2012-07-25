<?php

?>

<div class="tabs">
	<div class="tab-head">
		<a href="/report/listmailmerge">Mail Merge List</a>
		<a href="/report/viewrun/<?php echo $mmid; ?>">Mail Merge Runs</a>
		<a class=active >Run Details</a>
	</div>
	<div class="tab-body">
		<div>
			<?php echo $recipientlist; ?>
		</div>
	</div>
</div>
