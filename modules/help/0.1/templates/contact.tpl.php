
<?
$lines =  array(
array("Contact the Flow Helpdesk","section"),
array("Subject","text","subject"),
array("Message","section"),
array("","textarea","message",null,85,15),
);
print Html::form($lines,WEBROOT."/help/contact","POST","Send");

?>
