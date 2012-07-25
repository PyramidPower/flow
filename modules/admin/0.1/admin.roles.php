<?php
function role_forms_admin_allowed(&$w,$path) {
	    return preg_match("/admin\-forms\/(admin|index)/",$path);
}

function role_forms_fill_allowed(&$w,$path) {
	    return preg_match("/admin\-forms\/fillPdfForm/",$path);
}