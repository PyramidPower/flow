<?php 
// $Id: users.tpl.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia
?>
<!--
<div id="users-filter">
    <form>
        <table>
            <tr><td>Show Only Active Users</td><td><input type="checkbox" name="show_active"/></td></tr>
            <tr><td colspan="2"><input type="submit" value="Filter"/></td></tr>
        </table>
    </form>
</div>
-->
<?=Html::box($webroot."/admin/useradd/box","Add New User",true)?>
<?=$table?>
