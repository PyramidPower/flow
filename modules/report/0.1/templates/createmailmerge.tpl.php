<?php


?>

<div class="tabs">
	<div class="tab-head">
		<a href="/report/listmailmerge">Mail Merge List</a>
		<a class=active href="/report/createmailmerge/">Create Mail Merge</a>
	</div>
	<div class="tab-body">
		<div>
			<table cellpadding=2 cellspacing=2 border=0>
			<tr valign=top>
			<td>
			<?php  echo $createmm; ?>
			<?php echo $feedurl; ?>
			</td>
			<td><span id="feedtext"><?php echo $feedtext; ?></span></td>
            </tr>
            </table>
		</div>
	</div>
</div>

<script language="javascript">
	$.ajaxSetup ({
	    cache: false
		});

    CKEDITOR.replace( 'mmbody' ,
    	    {
    	        toolbar : 'Full'
    	    });

    var feed_url = "/report/feedAjaxGetReportText?id="; 
	$("select[id='rid'] option").click(function() {
		$.getJSON(
			feed_url + $(this).val(),
			function(result) {
				$('#feedtext').html(result);
			}
			);
		}
	);
</script>

