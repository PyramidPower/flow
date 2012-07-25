<?php

/**
 * Table structure of `page` 
 **/
//CREATE TABLE IF NOT EXISTS page(
//id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
//subject TEXT,
//teaser TEXT,
//body LONGTEXT,
//owner_id BIGINT,
//dt_created DATETIME,
//creator_id BIGINT,
//dt_modified DATETIME,
//modifier_id BIGINT,
//allow_comments TINYINT(1),
//moderate_comments TINYINT(1),
//inherit_permissions_page_id BIGINT,
//is_parent TINYINT(4)
//)

/**
 * Table structure of `page_comment` 
 **/
//CREATE TABLE IF NOT EXISTS page_comment(
//id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
//page_id BIGINT,
//parent_id BIGINT,
//author_id BIGINT,
//dt_created DATETIME,
//dt_modified DATETIME,
//comment TEXT,
//quote TEXT
//)

/**
 * Table structure of `page_history` 
 **/
//CREATE TABLE IF NOT EXISTS page_history(
//id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
//page_id BIGINT,
//author_id BIGINT,
//dt_created DATETIME,
//subject TEXT,
//teaser TEXT,
//body LONGTEXT
//)

/**
 * Table structure of `page_select` 
 **/
//CREATE TABLE IF NOT EXISTS page_select(
//id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
//type TEXT,
//value TEXT
//)

/**
 * Table structure of `page_user` 
 **/
//CREATE TABLE IF NOT EXISTS page_user(
//id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
//page_id BIGINT,
//user_id BIGINT
//)