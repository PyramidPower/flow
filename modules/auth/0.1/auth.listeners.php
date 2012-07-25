<?php
// $Id: auth.listeners.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

function auth_listener_POST_ACTION(&$w) {
    /*
     * Create a news item every time a new user gets created!
    */
    if ($w->currentHandler() == "admin"
            && $w->currentAction() == "useradd"
            && $w->currentRequestMethod() == "POST"
            && $w->ctx("user")
            && !$w->ctx("error")) {


        $user = $w->ctx("user");
        $subject = "New user created: ".$user->login;
        $contact = $user->getContact();
        $message ="<br/><b>Details</b><br/>\n";
        $message.=$w->internalLink("View User Details","admin","userview","/".$user->id);
        if ($contact) {
            $message .= "First Name: ".$contact->firstname."<br/>\n";
            $message .= "Last Name: ".$contact->lastname."<br/>\n";
            $message .= "Email: ".$contact->email."<br/>\n";
        }
        $w->service("Inbox")->notifyRoleUsers("administrator",$subject,$message);
    }
}

?>
