<?php
// $Id: auth.actions.php 937 2010-11-24 12:00:03Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

function profile_GET(Web &$w) {
	$p=$w->pathMatch("box");
	$user = $w->auth->user();
	$contact = $user->getContact();
	if ($user) {
		$w->ctx("title","Administration - Profile - ".$user->login);
	} else {
		$w->error("User does not exist.");
	}

	$lines = array();

	$lines[] = array("Change Password","section");
	$lines[] = array("Password","password","password","");
	$lines[] = array("Repeat Password","password","password2","");
	$lines[] = array("Contact Details","section");
	$lines[] = array("First Name","text","firstname",$contact ? $contact->firstname : "");
	$lines[] = array("Last Name","text","lastname",$contact ? $contact->lastname : "");
	$lines[] = array("Communication","section");
	$lines[] = array("Home Phone","text","homephone",$contact ? $contact->homephone : "");
	$lines[] = array("Work Phone","text","workphone",$contact ? $contact->workphone : "");
	$lines[] = array("Private Mobile","text","priv_mobile",$contact ? $contact->priv_mobile : "");
	$lines[] = array("Work Mobile","text","mobile",$contact ? $contact->mobile : "");
	$lines[] = array("Fax","text","fax",$contact ? $contact->fax : "");
	$lines[] = array("Email","text","email",$contact ? $contact->email : "");

	$f = Html::form($lines,$w->localUrl("/auth/profile"),"POST","Update");
	if ($p['box']) {
		$w->setLayout(null);
		$f = "<h2>Edit Profile</h2>".$f;
	}
	$w->out($f);
}

function profile_POST(Web &$w) {
	$w->pathMatch("id");
	$errors = $w->validate(array(
	array("homephone","^[0-9+\- ]*$","Not a valid home phone number"),
	array("workphone","^[0-9+\- ]*$","Not a valid work phone number"),
	array("mobile","^[0-9+\- ]*$","Not a valid  mobile phone number"),
	array("priv_mobile","^[0-9+\- ]*$","Not a valid  mobile phone number"),
	array("fax","^[0-9+\- ]*$","Not a valid fax number"),
	));

	if ($_REQUEST['password'] && (($_REQUEST['password'] != $_REQUEST['password2']))) {
		$errors[]="Passwords don't match";
	}
	$user = $w->auth->user();

	if (!$user) {
		$errors[]="Not Logged In";
	}

	if (sizeof($errors) != 0) {
		$w->error(implode("<br/>\n",$errors),"/auth/profile");
	}

	$user->fill($_REQUEST);
	if ($_REQUEST['password']) {
		$user->setPassword($_REQUEST['password']);
	} else {
		$user->password = null;
	}
	$user->update();

	$contact = $user->getContact();
	if ($contact) {
		$contact->fill($_REQUEST);
		$contact->private_to_user_id= null;
		$contact->update();
	}

	$w->msg("Profile updated.");
}

function login_GET(Web &$w) {
	$w->setLayout(null);
}

function login_POST(Web &$w) {
	if ($_POST['login'] && $_POST['password']) {
		$client_timezone = $_POST['user_timezone'];
		$user = $w->auth->login($_POST['login'],$_POST['password'],$client_timezone);
		if ($user) {
			if ($_SESSION['orig_path'] != "auth/login") {
				$url = $_SESSION['orig_path'];
				$w->logDebug("original path: ".$url);
				unset($_SESSION['orig_path']);
				$w->redirect($w->localUrl($url));
			} else {
				$w->redirect($w->localUrl());
			}
		}
 		 else {
			$w->error("Login or Password incorrect","/auth/login");
		}
	} else {
		$w->error("Please enter your login and password","/auth/login");
	}
}

function logout_GET(Web &$w) {
	if ($w->auth->loggedIn()) {// Unset all of the session variables.
		$_SESSION = array();

		// If it's desired to kill the session, also delete the session cookie.
		// Note: This will destroy the session, and not just the session data!
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
			$params["path"], $params["domain"],
			$params["secure"], $params["httponly"]
			);
		}

		// Finally, destroy the session.
		session_destroy();
	}
	$w->redirect($w->localUrl("/auth/login"));
}
?>
