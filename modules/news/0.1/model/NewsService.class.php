<?php
class NewsService extends DbService {

	// does user have permission to view a news item
	function & checkGroupPermissions($news) {
		$lnews = array();

		// unless 'news admin' - who see's all - gotta check for news group permissions for each news item
		// function checks news list and single news item, ie. getObjects (array) Vs getObject (object)
		// need slightly difference treatment and return values

		if (is_array($news)) {
			foreach ($news as $new) {
				if ($this->checkNewsItemPermission($new)) {
					$lnews[$new->id] = $new;
				}
			}
		}
		elseif (is_a($news,"DbObject") && $this->checkNewsItemPermission($news)) {
			$lnews = $news;
		}
		// return list, object or nothing as appropriate
		return $lnews;
	}

	function checkNewsItemPermission($newsItem) {
		// if admin, you got the news
		if ($this->w->auth->user()->hasRole("news_admin")) {
			return true;
		}
		else {
			$groups = $this->getNewsGroups($newsItem->id);

			// if groups, is user in group?
			if ($groups) {
				foreach ($groups as $group) {
					// if group is active
					if ($group->is_active == "0") {
						// get User object for group
						$grp = $this->Auth->getUser($group->group_id);
						// is logged in user in this group
						if ($this->w->auth->user()->inGroup($grp)) {
							// if yes, return news item for display
							return true;
						}
					}
				}
			}
		}
		return false;
	}
	
	
	function & getLatest() {
		$results = $this->_db->get("news")->where("is_deleted",0)->and("is_archived",0)->order_by("dt_created")->fetch_all();
		$news = $this->fillObjects("News", $results);

		// check user against 'view by group' permissions
		if ($news)
		return $this->checkGroupPermissions($news);
	}

	function & getNewsItem($id) {
		$news =  $this->getObject("News", array("id"=>$id));

		// check user against 'view by group' permissions
		if ($news)
		return $this->checkGroupPermissions($news);
	}

	/**
	 * will count all news items which haven't been read by this user
	 */
	function getUserNewsCount() {
		$user_id = $this->w->auth->user()->id;
		$read_count = $this->_db->sql("select count(*) as count from news_read where user_id = $user_id")->fetch_element("count");
		$item_count = $this->_db->sql("select count(*) as count from news where is_deleted = 0 and is_archived = 0")->fetch_element("count");
		return $item_count - $read_count;
	}

	function getUserNewsCountMarker() {
		$count = $this->getUserNewsCount();
		if ($count) {
			return "(".$count.")";
		}
	}
	function markItemRead($id) {
		$user_id = $this->w->auth->user()->id;
		$isread = $this->_db->sql("select count(*) as count from news_read where user_id = $user_id and news_id = $id")->fetch_element("count");
		if (!$isread) {
			$newsread = new News_read($this->w);
			$newsread->user_id = $user_id;
			$newsread->news_id = $id;
			$newsread->dt_read = time();
			$newsread->insert();
		}
	}

	function markAllItemsRead() {
		$user_id = $this->w->auth->user()->id;
		$news = $this->getLatest();
		foreach($news as $n) {
			$this->markItemRead($n->id);
		}
	}

	// return news group given group ID and news item ID
	function getGroupByNews($grp,$news) {
		return $this->getObject("News_group", array("group_id"=>$grp,"news_id"=>$news));
	}

	// return all groups for a news item, given new item ID
	function getNewsGroups($news) {
		return $this->getObjects("News_group", array("news_id"=>$news));
	}
}
