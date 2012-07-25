<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<div id="his" style="width:900px; height:500px;">
	<table class="form" width="100%">
		<?php if ($his):?>
       		<tr><td class="section" colspan="2"><strong><?=$his->subject?></strong></td></tr>
       		<tr><td colspan="2"><p><?=$his->body?></p></td></tr>
		<?php endif;?>
	</table>
</div>