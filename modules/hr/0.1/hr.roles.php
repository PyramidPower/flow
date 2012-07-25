<?php

function role_hr_employee_allowed(&$w,$path) {
    return startsWith($path,"hr/");
}

function role_hr_admin_allowed(&$w,$path) {
    return startsWith($path,"hr/");
}

function role_hr_manager_allowed(&$w,$path) {
    return startsWith($path,"hr/");
}

?>
