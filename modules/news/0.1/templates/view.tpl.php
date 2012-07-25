<?php 
// $Id: view.tpl.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

?>
<?if (!$print):?>
<?=Html::b($webroot."/news/index","Back to News")?>&nbsp;
<?=Html::b($webroot."/news/view/".$item->id."/print","Print")?>

<?if ($w->auth->user()->hasRole("news_admin")):?>
    &nbsp;<?=Html::b($webroot."/news/edit/".$item->id,"Edit")?>
    &nbsp;<?=Html::b($webroot."/news/archive/".$item->id,"Archive","Do you really want to archive this news item?")?>
    &nbsp;<?=Html::b($webroot."/news/delete/".$item->id,"Delete","Do you really want to delete this news item?")?>
<?endif;?>
    <p></p>
<?endif;?>

<div class="news-item">
    <div class="news-header">
                <?=$item->subject?>
    </div>
    <div class="news-footer">
        <?=Html::a($webroot."/contact/view/".$item->getAuthor()->contact_id,$item->getAuthor()->getFullname())?> at <?=date('d/m/Y H:i',$item->dt_modified)?>
    </div>
    <div class="news-teaser">
                <?=$item->teaser?>
    </div>
    <div class="news-body">
                <?=$item->body?>
    </div>
</div>
