<?php
class HrService extends DbService {

}

class Hr_employee extends DbObject {
    var $user_id;
    var $department_id;
    var $dt_start;
    var $dt_end;
    var $is_active;
    var $is_deleted;

    var $address1;
    var $address2;
    var $suburb;
    var $postcode;
    var $state;
    var $country;

    var $home_phone;
    var $private_email;

    var $tfn_number;
    var $dt_birthdate;

    var $employment_type_id;

    var $role_id;
    var $supervisor_id;
}

class Hr_role extends DbObject {
    var $title;
    var $description;
}

class Hr_department extends DbObject {
    var $company_id;
    var $title;
    var $code;
    var $head_user_id;
    var $parent_id;
}

class Hr_company extends DbObject {

}

?>
