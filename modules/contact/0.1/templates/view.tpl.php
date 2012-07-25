<?php 
// $Id: view.tpl.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia
?>
<script type="text/javascript">
    var current_tab = 1;
    function switchTab(num){
        if (num == current_tab) return;
        $('#tab-'+current_tab).hide()
        $('#tab-link-'+current_tab).removeClass("active");
        $('#tab-'+num).show().addClass("active");
        $('#tab-link-'+num).addClass("active");
        current_tab = num;
    }
</script>
<div class="tabs">
    <div class="tab-head">
        <a href="<?=$webroot?>/contact/index/<?=ucfirst(substr($contact->getFullName(),0,1))?>">Back</a>
        <a id="tab-link-1" href="#" class="active" onclick="switchTab(1);">View</a>
        <?if ($contact->getUser() || $contact->email):?>
        <a id="tab-link-2" href="#" onclick="switchTab(2);">Message</a>
        <?endif;?>
        <?if ($editform):?>
        <a id="tab-link-3" href="#" onclick="switchTab(3);">Edit</a>
        <?endif;?>
    </div>
    <div class="tab-body">
        <div id="tab-1">
            <?=$viewform?><br/>
        </div>
        <?if ($contact->getUser() || $contact->email):?>
        <div id="tab-2" style="display: none;">

                <?
                print Html::form(array(
                        array("Send a message","section"),
                        array("Subject","text","subject"),
                        array("Message","section"),
                        array("","hidden","receiver_id",$contact->getUser() ? $contact->getUser()->id:''),
                        array("","hidden","contact_id",$contact->id),
                        array("","textarea","message",null,50,5),
                        ),WEBROOT."/contact/sendmessage","POST","Send");
                ?>

        </div>
        <?endif;?>
        <?if ($editform):?>
        <div id="tab-3" style="display: none;">
            <?if ($contact->canDelete($w->auth->user())):?>
                <?=Html::b("$webroot/contact/delcontact/$id","Delete","Do you want to delete this contact?")?>
            <?endif;?><p></p>                
            <?=$editform?><br/>

        </div>
        <?endif;?>
    </div>
</div>
