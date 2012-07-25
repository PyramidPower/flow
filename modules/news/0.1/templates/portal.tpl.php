<?php
	$strnews = "";
	
    $news = $w->service('News')->getLatest();

	if ($news) {
		foreach ($news as $item) {
			if (!$item->isRead()) {
				$strnews .= "<div class=\"news-item\">" .
						   "<div class=\"news-header\">" .
							Html::img($webroot."/img/star.gif") . 
							"&nbsp;" . 
							Html::a($webroot."/news/view/".$item->id,$item->subject) . 
						   	"</div><div class=\"news-footer\">" .
						   	Html::a($webroot."/contact/view/".$item->getAuthor()->contact_id,$item->getAuthor()->getFullname()) . " at " . date('d/m/Y H:i',$item->dt_modified) .
						   	"</div><div class=\"news-teaser\">" .
						  	$item->teaser .
						   	Html::a($webroot."/news/view/".$item->id,"Read More ...") .
							"</div></div>";
						   	
				$i++;
				if ($i > 4) {
					$strnews = "<b>Showing first five News items</b><p>" . $strnews;
					break;	 
					}  	
				}
			}
	}
	else {
		$strnews = "<b>No News Today</b>";
	}

	if ($strnews == "")
		$strnews = "<b>No News Today</b>";
	
	return $strnews;
?>
