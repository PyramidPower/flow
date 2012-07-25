<?php 
// $Id: index.tpl.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

?>
<?if ($news):?>
    <?if ($w->service('News')->getUserNewsCountMarker()):?>
        <?=Html::b($w->localUrl("/news/allread"),"Mark all read","Are you sure to mark all messages as read?")?>
<p></p>
    <?endif;?>
    
    <?foreach ($news as $item):?>
    <div class="news-item">
    <div class="news-header">
        <?if (!$item->isRead()):?>
        <?=Html::img($webroot."/img/star.gif")?>
        <?endif;?>
        <?=Html::a($webroot."/news/view/".$item->id,$item->subject)?>
    </div>
    <div class="news-footer">
        <?=Html::a($webroot."/contact/view/".$item->getAuthor()->contact_id,$item->getAuthor()->getFullname())?> at <?=date('d/m/Y H:i',$item->dt_modified)?>
    </div>
    <div class="news-teaser">
                <?=$item->teaser?>
                <?=Html::a($webroot."/news/view/".$item->id,"Read More ...")?>
    </div>
</div>
    <?endforeach;?>
<?else:?>
<b>No News Today</b>
<?endif;?>