<?php
//========== Module Configuration ===============
$modules['agenda'] = array(
    'version' => '0.1',
    'topmenu' => true,
    'audit' => true,
	'audit_blacklist' => array("index"),
);


$modules['admin'] = array(
    'version' => '0.1',
    'topmenu' => true,
    'audit' => true,
	'audit_blacklist' => array("index"),
);

$modules['help'] = array(
    'version' => '0.1',
    'topmenu' => false,
    'audit' => false,
);

$modules['auth'] = array(
    'version' => '0.1',
    'topmenu' => false,
    'audit' => true,
	'audit_blacklist' => array("index"),
);

$modules['contact'] = array(
    'version' => '0.1',
    'topmenu' => true,
    'audit' => true,
	'audit_blacklist' => array("index"),
);

$modules['search'] = array(
    'version' => '0.1',
    'topmenu' => false,
    'audit' => false,
);

$modules['main'] = array(
    'version' => '0.1',
    'topmenu' => false,
    'audit' => false,
);

$modules['news'] = array(
    'version' => '0.1',
    'topmenu' => true,
    'audit' => false,
);


$modules['file'] = array(
    'version' => '0.1',
    'fileroot' => "c:/flow/uploads",
    'topmenu' => false,
    'audit' => true,
	'audit_blacklist' => array("index"),
);


$modules['map'] = array(
    'version' => '0.1',
    'topmenu' => false,
    'audit' => false,
);
$modules['inbox'] = array(
    'version' => '0.1',
    'topmenu' => true,
    'audit' => false,
);

$modules['hr'] = array(
    'version' => '0.1',
    'topmenu' => false,
    'audit' => false,
	'audit_blacklist' => array("index"),
);

$modules['wiki'] = array(
    'version' => '0.1',
    'topmenu' => true,
    'audit' => true,
	'audit_blacklist' => array("index"),
);


$modules['calendar'] = array(
    'version' => '0.1',
    'topmenu' => false,
    'audit' => false,
	'audit_blacklist' => array("index"),
);

$modules['pages'] = array(
    'version' => '0.1',
    'topmenu' => true,
    'audit' => true,
	'audit_blacklist' => array("index"),
);

$modules['address'] = array(
    'version' => '0.1',
    'topmenu' => false,
    'audit' => false,
);


$modules['task'] = array(
    'version' => '0.1',
    'topmenu' => true,
    'audit' => true,
	'audit_blacklist' => array("index"),
);

$modules['report'] = array(
    'version' => '0.1',
    'topmenu' => true,
    'audit' => true,
	'audit_blacklist' => array("index"),
);


