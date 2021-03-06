<?php 
// $Id: useredit.tpl.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia
?>
<?if ($user):?>
<?if ($box):?><h1>Edit User</h1><?endif;?>
<?php
    $contact = $user->getContact();
    $form['User Details'][]=array(
            array("Login","text","login",$user->login),
            array("Admin","checkbox","is_admin",$user->is_admin),
            array("Active","checkbox","is_active",$user->is_active));

    $form['User Details'][]=array(
            array("Password","password","password"),
            array("Repeat Password","password","password2"));

    $form['Contact Details'][]=array(
            array("First Name","text","firstname",$contact ? $contact->firstname : ""),
            array("Last Name","text","lastname",$contact ? $contact->lastname : ""));
    $form['Contact Details'][]=array(
            array("Title","select","title",$contact ? $contact->title : "",lookupForSelect($w, "title")),
            array("Email","text","email",$contact ? $contact->email : ""));
            
	$groupUsers = $user->isInGroups();
    
    if ($groupUsers)
    {
    	foreach ($groupUsers as $groupUser)
    	{
    		$group = $groupUser->getGroup();
    		
    		$groups[] = " - ".Html::a("/admin/moreInfo/".$group->id, $group->login);
    	}
    }
    else
    {
    	$groups = array();
    }
    $form['User Groups'][] = array(array("Group Title","static","groupName",implode("<br/>", $groups)));

    print Html::multiColForm($form,$w->localUrl("/admin/useredit/".$w->ctx("id")),"POST","Save");
?>

<?else:?>
<div class="error">User with ID <?=$id?> does not exist.</div>
<?endif;?>

<script type="text/javascript">
	$(".form-section").attr("width","");
</script>