<?php

function auth_db_upgrade() {
    $upgrade=array();
    return $upgrade;
}

function auth_db_create() {
    return "
CREATE TABLE IF NOT EXISTS `user` (
  `id` bigint(20) NOT NULL auto_increment,
  `login` varchar(32) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_id` bigint(20) default NULL,
  `is_admin` tinyint(1) NOT NULL default '0',
  `is_active` tinyint(1) NOT NULL default '1',
  `is_deleted` tinyint(1) NOT NULL default '0',
  `dt_created` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `dt_lastlogin` datetime default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `user_role` (
  `id` bigint(20) NOT NULL auto_increment,
  `user_id` int(11) NOT NULL,
  `role` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `unique_role_per_user` (`user_id`,`role`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

";
}
?>
