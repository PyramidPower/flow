<?php

function role_attendance_admin_allowed(&$w,$path) {
    return preg_match("/attendance(-.*)?\//",$path);
}

function role_attendance_user_allowed(&$w,$path) {
    return preg_match("/attendance\//",$path);
}

function role_attendance_manager_allowed(&$w,$path) {
    return preg_match("/attendance-manager\//",$path);
}

