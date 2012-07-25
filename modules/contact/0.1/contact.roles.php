<?php
function role_contact_editor_allowed(Web &$w, $path) {
	return preg_match("/contact(-.*)?\//",$path);
}
