<?php

function role_task_admin_allowed(&$w,$path) {
    return preg_match("/task(-.*)?\//",$path);
}

function role_task_user_allowed(&$w,$path) {
    return preg_match("/task\//",$path);
}

function role_task_group_allowed(&$w,$path) {
    return preg_match("/task-group\//",$path);
}


