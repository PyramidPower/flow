<?php 
// $Id: actions.tpl.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia
?>
<?foreach ($actions as $h => $ar): ?>
<div class="handler-box">
    <div class="handler-title">
        <a href="<?=$webroot?>/<?=$h?>"><?=$h?></a>
    </div>
    <div class="action-list">
    <ul>
    <?foreach ($ar as $a): ?>
        <li><a href="<?=$webroot?>/<?=$h?>/<?=$a?>"><?=$a?></a></li>
    <?endforeach;?>
    </ul>
    </div>
</div>
<?endforeach;?>