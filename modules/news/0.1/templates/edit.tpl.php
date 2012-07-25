<?php 
// $Id: edit.tpl.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

$form["News Item"] = array(
        array(array("Subject","text","news_subject",$item->subject,90)),
		array(array("View by Group","multiSelect","group",$grp,$usr)),
        array(array("Teaser","textarea","news_teaser",$item->teaser,80,30)),
        array(array("Body","textarea","news_body",$item->body,null,80,30)),
        );
        
$f = Html::multiColForm($form,$w->localUrl("/news/edit/".$item->id),"POST"," Save ");
        
print $f;
?>
<script type='text/javascript'>
    CKEDITOR.replace( 'news_teaser' ,
    {
        toolbar : 'Basic'
    });
    CKEDITOR.replace( 'news_body' ,
    {
        toolbar : 'Basic'
    });
</script>
