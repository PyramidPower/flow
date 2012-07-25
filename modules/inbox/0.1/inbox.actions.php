<?php
// $Id: inbox.actions.php 436 2010-08-27 06:16:28Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia


function inbox_index_GET(Web &$w) {
	inbox_navigation($w,"");
	$p = $w->pathMatch('num');
	$num = $p['num'];
	$num ? $num : $num = 1;
	$new_total = $w->Inbox->getNewMessageCount($w->Auth->user()->id);
	$new_total ? $new_total = $new_total['COUNT(*)'] : "";
	$new = $w->Inbox->getMessages($num-1,40,$w->Auth->user()->id,1);
	$w->ctx('pgnum',$num);
	$w->ctx("newtotal",$new_total);
	$w->ctx("new",$new);
}

function inbox_read_GET(Web &$w){
	inbox_navigation($w,"Read Messages");
	$p = $w->pathMatch('num');
	$num = $p['num'];
	$num ? $num : $num = 1;
	$read = $w->Inbox->getMessages($num-1,40,$w->Auth->user()->id,0);
	$read_total = $w->Inbox->getReadMessageCount($w->Auth->user()->id);
	$read_total ? $read_total = $read_total['COUNT(*)'] : "";
	$w->ctx('pgnum',$num);
	$w->ctx("readtotal",$read_total);
	$w->ctx("read",$read);
}

function inbox_archive_ALL(Web &$w) {
	$p = $w->pathMatch("type","arr");
	$type = $p['type'];
	$check = explode(",",$p['arr']);
	if ($check[0] == "on"){
		unset($check[0]);
	}
	foreach($check as $message){
		$mess_obj = $w->Inbox->getMessage($message);
		$mess_obj->is_archived = 1;
		$mess_obj->dt_archived = time();
		$mess_obj->update();
	}
	$w->msg("Message(s) Archived","/inbox/".$type );
}

function inbox_showarchive_ALL(Web &$w){
	inbox_navigation($w,"Archive");

	$p = $w->pathMatch('num');
	$num = $p['num'];
	$num ? $num : $num = 1;
	$new_arch = $w->Inbox->getMessages($num-1,40,$w->Auth->user()->id,0,1);
	//$arch = $w->Inbox->getMessages($num-1,40,$w->Auth->user()->id,0,1);
	$arch_count = $w->Inbox->getArchCount($w->Auth->user()->id);
	//$read_total = $w->Inbox->getReadMessageCount($w->Auth->user()->id);
	//$read_total ? $read_total = $read_total['COUNT(*)'] : "";
	$w->ctx('pgnum',$num);
	$w->ctx("readtotal",$arch_count);
	$w->ctx("arch",$arch);
	$w->ctx("new_arch",$new_arch);
}

function inbox_view_GET(Web &$w) {
	inbox_navigation($w,"Message View");
	$p = $w->pathMatch("type","id");
	$msg = $w->Inbox->getMessage($p['id']);
	if (!$msg) {
		$w->error("No such message.");
	}
	if ($msg->user_id != $w->Auth->user()->id) {
		$w->error("No access.");
	}
	$msg->is_new = 0;
	$msg->dt_read = time();
	$msg->update();
	$w->ctx("message",$msg);
	$w->ctx("type",$p['type']);
}

function inbox_allread_GET(Web &$w) {
	$w->Inbox->markAllMessagesRead();
	$w->msg("All messages marked as read.","/inbox/index");
}

function inbox_send_GET(Web &$w) {
	//$w->setLayout(null);
	inbox_navigation($w,"Create Message");
}

function inbox_send_POST(Web &$w) {
	//	print_r($_REQUEST);
	
	$p = $w->pathMatch('id');
	if($p['id']){		// For reply function
		$mess = $w->Inbox->getMessage($p['id']);
		$w->Inbox->addMessage($w->request("subject"),$w->request("message"),$w->request("receiver_id"),null,$p['id']);
		$mess->has_parent = 1;
		$mess->update();
		//      print_r($new_message_id);
		//print $new_message_id;
		//$mess->parent_message_id = $new_message_id;
		//		$mess->sender_id = $w->auth->user()->id;

		//		$mess->user_id = $w->request('receiver_id');
		//		$mess->subject = $w->request("subject");
		//		$mess->message = $w->request("message");
		//$mess->update();
	} else {
		for ($i = 0; $i<100;$i++){			// To generate test data cause im lazy
			$receiver_id = $w->request("receiver_id");
			$subject = $w->request("subject")* rand(0,100000)/2;
			$message = $w->request("message");
			if ($receiver_id && $subject) {
				$w->Inbox->addMessage($subject, $message, $receiver_id);
			}
		}
	}
	$w->msg("Message Sent.","/inbox/index");
}

function inbox_delete_ALL(Web &$w){
	$p = $w->pathMatch("type","arr");
	$check = explode(",",$p['arr']);
	if ($check[0] == "on"){
		unset($check[0]);
	}
	foreach($check as $message){
		$mess_obj = $w->Inbox->getMessage($message);
		$mess_obj->is_deleted = 1;
		//		$mess_obj->dt_archived = time();
		$mess_obj->update();
	}
	$w->msg("Message(s) Deleted","/inbox/".$p['type']);
}

function inbox_trash_ALL(Web &$w){
	inbox_navigation($w,'Trash',$nav);
	$p = $w->pathMatch('num');
	$num = $p['num'];
	$num ? $num : $num = 1;
	$read_del = $w->Inbox->getMessages($num-1,40,$w->Auth->user()->id,0,0,1);
	//$new_del = $w->Inbox->getMessages(0,100,$w->Auth->user()->id,1,0,1);
	$del_count = $w->Inbox->getDelMessageCount($w->Auth->user()->id);
	$w->ctx('del_count',$del_count);
	$w->ctx('pgnum',$num);
	$w->ctx('readdel',$read_del);
	//$w->ctx('newdel',$new_del);
}

function inbox_deleteforever_ALL(Web &$w){
	$p = $w->pathMatch("arr");
	$check = explode(",",$p['arr']);
	if ($check[0] == "on"){
		unset($check[0]);
	}
	foreach($check as $message){
		$mess_obj = $w->Inbox->getMessage($message);
		$mess_obj->del_forever = 1;
		//		$mess_obj->dt_archived = time();
		$mess_obj->update();
	}
	$w->msg("Message(s) Deleted","/inbox/trash");
}

?>
