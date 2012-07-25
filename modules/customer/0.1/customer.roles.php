<?php

function role_customer_allowed(&$w,$path) {
    $actions = array(
        "main/index",
        "main/login",
        "customer/index",
        "customer/editdetails",
        "customer/joblist",
        "customer/jobdetails"
    );
    return in_array($path, $actions);
}

?>
