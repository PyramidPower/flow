<?php
function pages_comment_GET(Web &$w)
{
	$id = $w->pathMatch("page_id","comment_id");
	
	$comment = $w->Page->getCommentItem($id['comment_id']);
	
	$dt_modified = $comment ? $comment->dt_created : $w->Page->getPage($w,$id['page_id'])->dt_created;
	
	$form = $w->Page->commentTemplate($id['page_id'], $id['comment_id'], $comment->comment, $dt_modified);
												  
	$w->out(Html::multiColForm($form,$w->localUrl("/pages/comment"),"POST"));
	
	$w->setLayout(null);
}

function pages_comment_POST(Web &$w)
{
	$page_comment = new Page_comment($w);
	$page_comment->page_id = $_REQUEST['page_id'];
	$page_comment->parent_id = $_REQUEST['parent_id'];
	$page_comment->comment = $_REQUEST['comment'];
	$page_comment->quote = $_REQUEST['quote'];
	$page_comment->dt_modified = $_REQUEST['dt_modified'];
	$page_comment->author_id = $w->auth->_user->id;	
	$page_comment->insert();
	
    $w->msg("New Comment Added!","/pages/index/level/".$_REQUEST['page_id']."#2");
}
?>