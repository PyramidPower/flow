<style>
<!--
form {
	width: 500px;
}
-->
</style>
<?php
// $Id: send.tpl.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia
$p = $w->pathMatch("id");
$messageid = $p['id'];

//if ($messageid){
//	$message = $w->Inbox->getMessage($messageid);
//	print_r($message);
//	$lines =  array(
//	array("","section"),
//	array("To","autocomplete","receiver_id",$message->sender_id,$w->auth->getUsers()),
//	array("Subject","text","subject",$message->subject),
//	array("","section"),
//	array("","textarea","message","<br/><hr/>".($message->getMessage() ? $message->getMessage() : "No message body."),120,10),
//	);
//	unset($_SESSION['message']);
//	print Html::form($lines,WEBROOT."/inbox/send/$message->id","POST","Send");
//} else {


if ($messageid){
	$message = $w->Inbox->getMessage($messageid);
	$parent_id = $message->parent_message_id;
	//print $parent_id;
	if ($parent_id){
		print "<div class='tab-body' style='float:right; width: 500px; margin-right:200px; margin-bottom: 20px; padding: 10px;'>";
		print "<b><u> Previous Messages </u></b><br/><hr/>";
		$counter = 1;
		print "<div style='padding:3px; background-color: ".$bgcolor."';'> Message sent by: <i>" . $w->Auth->getUser($message->sender_id)->getFullname() . "</i>  on: <i>" . $message->getDate("dt_created","d/m/Y H:i") . "</i><br/>";
		print $message->getMessage();
		print "</div>";
		while (!$parent_id == 0 || !$parent_id == null){
			if ($counter % 2 != 0){
				$bgcolor = "#ddd";
			} else {
				$bgcolor = "white";
			}
			$parent_message = $w->Inbox->getMessage($parent_id);
			print "<div style='padding:3px; background-color: ".$bgcolor."';'> Message sent by: <i>" . $w->Auth->getUser($parent_message->sender_id)->getFullname() . "</i>  on: <i>" . $parent_message->getDate("dt_created","d/m/Y H:i") . "</i><br/>";
			print $parent_message->getMessage();
			print "</div>";
			$parent_id = $parent_message->parent_message_id ? $parent_message->parent_message_id : null;
			$counter++;
		}
		print "</div>";
	}
	//	print_r($message);
	$lines =  array(
	array("","section"),
	array("To","autocomplete","receiver_id",$message->user_id,$w->auth->getUsers()),
	array("Subject","text","subject",$message->subject),
	array("","section"),
	array("","textarea","message",null,120,10),
	);
	print Html::form($lines,WEBROOT."/inbox/send/".$messageid,"POST","Send");
	//  print_r($message);
	//	print_r($message);exit();
	//$parent_arr = $message->getParentMessage();

	if ($message_arr){
		foreach($message_arr as $mes){
			print_r($mes);
		}
	}
} else {
	$lines =  array(
	array("","section"),
	array("To","autocomplete","receiver_id",null,$w->auth->getUsers()),
	array("Subject","text","subject"),
	array("","section"),
	array("","textarea","message",null,120,10),
	);
	print Html::form($lines,WEBROOT."/inbox/send","POST","Send");
}

?>
<script type='text/javascript'>
    function flow_acp_receiver_id() {}
    
    CKEDITOR.replace( 'message' ,
    {
        toolbar : 'Basic'
    });
</script>
