<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<style>
/*
	.tab-inside
	{
		display: block;
		margin: 10px auto 20px; 
		padding: 10px; 
		background-color: #FFF; 
		border-radius: 6px; 
		border:2px solid Brown; 
		-moz-border-radius: 6px; 
		-moz-box-shadow: 0 0 14px #123; 
		-webkit-border-radius: 6px; 
		-webkit-box-shadow: 0 0 14px #123; 
		box-shadow: 0 0 14px #123;
	}
	*/

	.addressHeader 
	{
		background:#F3F0E7;
		font-family:arial;
		font-size:12px;
		font-weight:bold;
		border:1px solid #C8BA92;
		padding:5px;
		width:500;
	}
	
	.addressBody 
	{
		background:#FFFFFF;
		font-family:arial;
		font-size:12px;
		border-left:1px solid #C8BA92;
		border-right:1px solid #C8BA92;
		border-bottom:1px solid #C8BA92;
		padding:5px;
		width:500;
	}
</style>

<script src="<?=$webroot?>/js/flowplayer/flowplayer-3.2.4.min.js"></script>
<script type="text/javascript" src="<?=$webroot?>/js/Date.format.js"></script>

<div class="tabs">
    <div class="tab-head">
    	<?php if (count($_SESSION['root']) == 1):?>
	    	<a id="tab-link-1" href="#1" onclick="switchTab(1);">Home</a>
    	<?php else:?>
	    	<a id="tab-link-1" href="#1" onclick="switchTab(1);">Content</a>
    	    <a id="tab-link-2" href="#2" onclick="switchTab(2);"><?=$com_size ? $com_size : "Comments (0)"?></a>
        	<a id="tab-link-3" href="#3" onclick="switchTab(3);"><?=$att_size ? $att_size : "Attachments (0)"?></a>
	    	<a id="tab-link-4" href="#4" onclick="switchTab(4);">Page History</a>
		    <?php if ($w->auth->user()->hasRole("pages_invitation") || $w->Page->getPageRole($w,$page->id) == "pages_editor"):?>
	        	<a id="tab-link-5" href="#5" onclick="switchTab(5);">Invitation History</a>
	        <?php endif;?>
	    	<a id="tab-link-6" href="#6" onclick="switchTab(6);">Administration</a>
    	<?php endif;?>
    </div>
    <div class="tab-body">
    	<?php if ($w->auth->user()->hasAnyRole(array("pages_view","pages_viewpartial"))):?>
    		<p style="font-size: 8pt;">
    			<?php foreach ($_SESSION['thumbView'] as $page_id=>$page_title)
    			{
    				if ($flag == true)
    				{
    					unset($_SESSION['thumbView'][$page_id]);
    					continue;
    				}
    				echo "&gt;&nbsp;".Html::a("/pages/index/level/".$page_id, $page_title)."&nbsp;";
    				
    				if ($page && $page->id == $page_id)
    					$flag = true;
    			}
    			?>
	    	</p>
<!-- Page content tab -->
	    	<div id="tab-1" style="display:none">
				<?php if ($page) :?>
	    			<div class="tab-inside">
            			<?=$createButton?>
						<?php if ($w->auth->user()->hasRole("pages_edit") || $w->Page->getPageRole($w,$page->id) == "pages_editor"):?>
							<?=Html::b(WEBROOT."/pages/edit/$page->id","Edit Page")?>&nbsp;
						<?php endif;?>
						<?php if ($w->auth->user()->hasRole("pages_delete") || $w->Page->getPageRole($w,$page->id) == "pages_editor" || $w->auth->user()->id == $page->owner_id):?>
							<?=Html::b(WEBROOT."/pages/delete/$page->id","Delete Page", "Are you sure you want to delete this page?")?>
						<?php endif;?>
						<p></p>
						<div class="news-item">
						    <div class="news-header">
						        <?=$page->subject?>
						    </div>
						    <div class="news-footer">
						    	<?=Html::a($webroot."/contact/view/".$page->getCreator()->contact_id,$page->getCreator()->getFullName())?> created at <?=date('d/m/Y H:i',$page->dt_created)?>
						    </div>
						   	<div class="news-body">
						   		<?=$page->body?>
						    </div>
						</div>
					</div>
        			<?php if ($pageLists):?>
			    		<div class="tab-inside">
			    			<fieldset>
			    				<legend><strong>More Pages</strong></legend>
			    				<?php foreach ($pageLists as $pageList)
			    				{
			    					echo "<p>".Html::a("/pages/index/level/".$pageList->id,$pageList->subject)."</p>";
			    				}
			    				?>
			    			</fieldset>
	    				</div>
					<?php endif;?>
				<?php else :?>
    				<div class="tab-inside">
        				<p><?=$createButton?></p>
        			</div>
        			<?php if ($pageLists):?>
			    		<div class="tab-inside">
			    			<fieldset>
			    				<legend><strong>More Pages</strong></legend>
			    				<?php foreach ($pageLists as $pageList)
			    				{
			    					echo "<p>".Html::a("/pages/index/level/".$pageList->id,$pageList->subject)."</p>";
			    				}
			    				?>
			    			</fieldset>
	    				</div>
					<?php endif;?>
				<?php endif;?>
	    	</div>
<!-- Page comment tab -->
			<div id="tab-2" style="display:none">
				<table class="form" width="30%">
					<?php
						if ($comments)
						{
							echo "<p>".Html::box(WEBROOT."/pages/comment/$page->id","Comment Page", true)."</p>";
							
							foreach ($comments as $comment)
							{
								$to = $comment->parent_id == 0 ? $comment->w->_context['page']->getCreator()->getFullName() : $w->Page->getCommentItem($comment->parent_id)->getAuthor()->getFullName();
					?>
		       					<tr><td class="section" colspan="2"><strong>To: "<?=$to?> at <?=date('d/m/Y H:i',$comment->dt_modified)?>"</strong></td></tr>
	       	            		<tr>
	       	            			<td colspan="2">
	       	            				<?php if($comment->quote):?>
	                                      	<p><strong>Quote: "<?=$comment->quote?>"</strong></p>
	       	            				<?php endif;?>
	       	            				<p><?=$comment->comment?></p>
	       	            				<p class="createdby">
	       	            					Post by: <?=$comment->getAuthor()->getFullName()?> at 
	       	            					<?=date('d/m/Y H:i',$comment->dt_created)?> 
	                   						<?=Html::box("/pages/comment/$comment->page_id/$comment->id","Reply",true)?>
											<?php if ($w->auth->user()->hasRole("pages_delete") || $w->Page->getPageRole($w,$page->id) == "pages_editor" || $w->auth->user()->id == $page->owner_id):?>
	                   							<?=Html::b("/pages/delete/$comment->page_id/$comment->id","Delete","Are you sure you want to delete this comment?")?>
											<?php endif;?>
	       	            				</p>
	       	            			</td>
	       	            		</tr>
					<?php
							}
						}
						elseif ($page) 
						{
							echo "<p>".Html::box(WEBROOT."/pages/comment/$page->id","Comment Page", true)."</p>";
							
							echo "<p>No comment for this page yet!</p>";
						}
					?>
	       		</table>
			</div>
<!-- Page attachments tab -->
			<div id="tab-3" style="display:none">
		        <?php if ($w->auth->user()->hasRole('pages_delete') || $userRole == "editor"):?>
		        	<?php if ($page):?>
						<p><?=Html::box($webroot."/file/attach/page/".$page->id."/pages+index+level+$page->id","Attach File",true)?></p>
					<?php endif;?>
	        	<?endif;?>
				<p><?=$attachments?></p>
			</div>
<!-- Page history tab -->
			<div id="tab-4" style="display:none">
				<p><?=$history?></p>
			</div>
<!-- Page invitation history tab -->
			<div id="tab-5" style="display:none">
				<div><?=$invHistory?></div>
			</div>
<!-- Page administration tab -->
			<div id="tab-6" style="display:none">
				<p><?=$adminForm?></p>
			</div>
			<p></p>
		<?php endif;?>
    </div>
</div>

<script type="text/javascript">
	$("a[rel='gallery']").colorbox();

	function flow_acp_sendTo(){};

	function flow_acp_changeTo(){};

	$(this).scroll(function(){	 
		$("#scrollingDiv").stop().animate({"marginTop": ($(window).scrollTop() + 11) + "px"}, "slow" );
	});
</script>

<script type="text/javascript" src="<?=WEBROOT?>/js/warehouses/warehouses.js">switchTab(<?=$_REQUEST['tab']?$_REQUEST['tab']:current_tab?>);</script>