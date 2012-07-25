<?php
class News_group extends DbObject {
	var $group_id;		// the group id
	var $news_id;		// the news item
	var $is_active;		// is group active flag

	function getDbTableName() {
		return "news_group";
	}
}
