<?php
// $Id: add.tpl.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

$f = Html::form(array(
        array("News Item","section"),
        array("Subject","text","news_subject",null,90),
        array("Teaser","textarea","news_teaser",null,80,30),
        array("Body","textarea","news_body",null,80,30),
        ),$w->localUrl("/news/add"),"POST","Save");
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
