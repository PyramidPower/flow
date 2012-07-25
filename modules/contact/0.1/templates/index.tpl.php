<?php 
// $Id: index.tpl.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia
?>
<div class="tab-head">
    <?foreach (str_split("ABCDEFGHIJKLMNOPQRSTUVWXYZ") as $a):?>
    <a href="<?=$webroot?>/contact/index/<?=$type?>/<?=$a?>" class="<?=$a==$first?'active':''?>"><?=$a?></a>&nbsp;
    <?endforeach;?>
</div>
<table class="tablesorter">
    <thead>
        <tr>
            <th>Name</th>
            <th>Company</th>
            <th>Home Phone</th>
            <th>Work Phone</th>
            <th>Private Mobile</th>
            <th>Work Mobile</th>
            <th>Fax</th>
            <th>Email</th>
        </tr>
    </thead>
    <tbody>
        <?if ($contacts):?>
            <?foreach ($contacts as $c): if ($c->canView()):?>
        <tr>
            <td><a href="<?=$webroot?>/contact/view/<?=$c->id?>"><?=$c->getFullName()?></a></td>
            <td>
                <?if ($c->getPartner()):?>
                <a href="<?=$webroot?>/partner/view/<?=$c->getPartner()->id?>"><?=$c->getPartner()->name?></a>
                <?endif;?>
            </td>
            <td><?=$c->homephone?></td>
            <td><?=$c->workphone?></td>
            <td><?=$c->priv_mobile?></td>
            <td><?=$c->mobile?></td>
            <td><?=$c->fax?></td>
            <td><a href="mailto:<?=$c->email?>"><?=$c->email?></a></td>
        </tr>
            <?endif;endforeach;?>
        <?endif;?>
    </tbody>
</table>