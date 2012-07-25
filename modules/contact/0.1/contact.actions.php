<?php
// $Id: contact.actions.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

function contact_navigation(&$w,$title = null) {
    if ($title) {
        $w->ctx("title",$title);
    }
    $nav = array();
    if ($w->auth->loggedIn()) {
        //$nav[]=Html::a($w->localUrl("/contact/index"),"List Contacts");
        $nav[]=Html::a($w->localUrl("/contact/new"),"New Contact");
    }

    $w->ctx("navigation", $nav);
}
function index_ALL(Web &$w) {
    contact_navigation($w,"Contacts");
    $w->pathMatch(array("type","employees"),array("first","A"));
    $f = $w->ctx("first");
    $w->ctx("first",$f ? $f :"A");
    $w->ctx("contacts",$w->service('Contact')->getContacts($w->ctx("type"),$w->ctx("first")));
}


function contact_new_GET(Web &$w) {
    contact_navigation($w,"Contacts - New Contact");

    $f = Html::form(array(
            array("Contact Details","section"),
            array("Title","select","title",null,lookupForSelect($w, "title")),
            array("First Name","text","firstname"),
            array("Last Name","text","lastname"),
            array("Other Name","text","othername"),
            array("Communication","section"),
            array("Home Phone","text","homephone"),
            array("Work Phone","text","workphone"),
            array("Work Mobile","text","mobile"),
            array("Private Mobile","text","priv_mobile"),
            array("Fax","text","fax"),
            array("Email","text","email"),
            array("Private","checkbox","private"),
            ),$w->localUrl("/contact/new"),"POST","Save");
    $w->out($f);
}

function contact_view_GET(Web &$w) {
    $w->pathMatch("id");
    $c = $w->service('Contact')->getContact($w->ctx('id'));
    contact_navigation($w,"Contacts - ".$c->getFullName());

    $fv = Html::form(array(
            array("Contact Details","section"),
            array("Title","static","title",$c->title),
            array("First Name","static","firstname",$c->firstname),
            array("Last Name","static","lastname",$c->lastname),
            array("Other Name","static","othername",$c->othername),
            array("Communication","section"),
            array("Home Phone","static","homephone",$c->homephone),
            array("Work Phone","static","workphone",$c->workphone),
            array("Work Mobile","static","mobile",$c->mobile),
            array("Private Mobile","static","priv_mobile",$c->priv_mobile),
            array("Fax","static","fax",$c->fax),
            array("Email","static","email",$c->email),
            array("Private","static","private",$c->private_to_user_id ? "Yes" : "No"),
    ));
    $w->ctx("viewform",$fv);

    if ($w->auth->user()->hasRole('contact_editor') || $c->private_to_user_id == $w->auth->user()->id) {
        $lines = array(
                array("Contact Details","section"),
                array("Title","select","title",$c->title,lookupForSelect($w, "title")),
                array("First Name","text","firstname",$c->firstname),
                array("Last Name","text","lastname",$c->lastname),
                array("Other Name","text","othername",$c->othername),
                array("Communication","section"),
                array("Home Phone","text","homephone",$c->homephone),
                array("Work Phone","text","workphone",$c->workphone),
                array("Work Mobile","text","mobile",$c->mobile),
                array("Private Mobile","text","priv_mobile",$c->priv_mobile),
                array("Fax","text","fax",$c->fax),
                array("Email","text","email",$c->email));
        if (!$c->getUser()) {
            $lines[] = array("Private","checkbox","private",$c->private_to_user_id);
        }
        $fe = Html::form($lines,$w->localUrl("/contact/update/".$c->id),"POST","Update");

        $w->ctx("editform",$fe);
    }
    $w->ctx("contact",$c);
}

function contact_update_POST(Web &$w) {
    $w->pathMatch("id");

    $contact = $w->auth->getObject('Contact',$w->ctx("id"));
    if ($contact) {
        $contact->fill($_REQUEST);
        if (!$contact->private_to_user_id) {
            $contact->private_to_user_id = null;
        }
        $contact->update();
    } else {
        $msg->error("Contact does not exist","/contact/index");
    }

    $w->msg("Contact ".$contact->getFullName()." updated.","/contact/index");
    
}


function contact_new_POST(Web &$w) {
    $errors = $w->validate(array(
            array("homephone","^[0-9+\- ]*$","Not a valid home phone number"),
            array("workphone","^[0-9+\- ]*$","Not a valid work phone number"),
            array("mobile","^[0-9+\- ]*$","Not a valid  mobile phone number"),
            array("priv_mobile","^[0-9+\- ]*$","Not a valid  mobile phone number"),
            array("fax","^[0-9+\- ]*$","Not a valid fax number"),
    ));
    if (sizeof($errors) != 0) {
        $w->error(implode("<br/>\n",$errors),"/contact/new");
    }
    $user = new Contact($w);
    $user->fill($_REQUEST);
    $user->dt_created = time();
    if ($w->request("private")) {
        $user->private_to_user_id = $w->auth->user()->id;
    }
    $user->insert();
    $w->msg("Contact ".$user->login." added","/contact/index/".ucfirst(substr($user->getFullName(),0,1)));
}

function contact_sendmessage_POST(Web &$w) {
    $receiver_id = $w->request("receiver_id");
    $subject = $w->request("subject");
    $message = $w->request("message");
    if ($receiver_id && $subject) {
        $w->service("Inbox")->addMessage($subject, $message, $receiver_id,$w->request("sms"));
    }
    $w->msg("Message Sent.","/contact/index");
}


/**
 * Delete a Contact
 * url: GET ../contact/delcontact/<id>
 * 
 * @param <type> $w
 */
function contact_delcontact_GET(Web &$w) {
    $p = $w->pathMatch("id");
    $contact = $w->service('Contact')->getContact($p['id']);
    if (!$contact) {
        $w->error("Contact does not exist.","/contact/index");
    }
    if (!$contact->canDelete($w->auth->user())) {
        $w->error("You can't delete this contact.","/contact/index");
    }
    $contact->delete();
    $w->msg("Contact deleted.","/contact/index");
}
?>
