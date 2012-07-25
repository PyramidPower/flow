<?php
class Page_parser extends DbObject
{
	var $id; //page ID;
	var $command; //command of the operation;
	var $options; //array of options;
	var $functionName; //handler function's name;
	
	function parseCommand($functionName,$message,$pattern,$id)
	{	
		$this->id = $id;
		
		$this->functionName = $functionName;
		
		return preg_replace_callback($pattern,array(&$this,'callBack'),$message);
	}
	
	private function callBack($match)
	{
		$commands = explode("|",$match[1]);
		
		$this->command = $commands[0];
		
		if (count($commands) > 1)
		{
			$this->options = array();
			
			for ($i=1;$i<count($commands);$i++)
			{
				$this->options[] = $commands[$i];
			}
		}
		else 
		{
			$this->options = array();
		}
		
		$handler = "handler_".$this->functionName;
		
		if (method_exists("Page_parser",$handler))
		{
			$results = $this->$handler($this->id);
		}
		
		return $results ? $results : $match[0];
	}
	
	private function handler_onView($id)
	{	
		$handler = "onView_".$this->command;
						
		if (method_exists("Page_parser",$handler))
		{
			return $this->$handler($id);
		}
		else 
		{
			return null;
		}
	}
	
	private function handler_onSave($id)
	{
		$handler = "onSave_".$this->command;
		
		if (method_exists("Page_parser",$handler))
		{	
			return $this->$handler($id);
		}
		else 
		{
			return null;
		}
	}
	
	private function onView_img($id,$name=null,$width=250,$height=250,$border=0)
	{
		for ($i=0;$i<count($this->options);$i++)
		{
			$keyval = explode("=",$this->options[$i]);
			$key = $keyval[0];
			$$key = $keyval[1];
		}
		
		if ($name)
		{
			$attachment = $this->getObject("Attachment",array('parent_id'=>$id,'filename'=>$name,'is_deleted'=>0));
			
			if ($attachment)
			{	
				return "<img src=\"$webroot/file/atthumb/$attachment->id/$width/$height\" border=\"$border\"/>";
			}
			else 
			{
				return null;
			}
		}
	}
	
	private function onView_thumb($id,$name=null,$width=250,$height=250)
	{
		for ($i=0;$i<count($this->options);$i++)
		{
			$keyval = explode("=",$this->options[$i]);
			$key = $keyval[0];
			$$key = $keyval[1];
		}
		
		if ($name)
		{
			$attachment = $this->getObject("Attachment",array('parent_id'=>$id,'filename'=>$name,'is_deleted'=>0));
			
			if ($attachment)
			{
				return "<a href=\"$webroot/file/atthumb/$attachment->id/800/600/a.jpg\" rel=\"gallery\"><img src=\"$webroot/file/atthumb/$attachment->id/$width/$height\" border=\"0\"/></a>";
			}
			else 
			{
				return null;
			}
		}
	}
	
	private function onView_gallery($id,$column=null,$width=150,$height=150,$border=5,$margin=5,$padding=5)
	{	
		for ($i=0;$i<count($this->options);$i++)
		{
			$keyval = explode("=",$this->options[$i]);
			$key = $keyval[0];
			$$key = $keyval[1];
		}
		
		if ($column)
		{
			$rows = $this->_db->get("attachment")->where(array('parent_id'=>$id,'is_deleted'=>0))->limit('0',$column)->fetch_all();
			
			$attachments = $this->fillObjects("Attachment", $rows);
		}
		else 
		{
			$attachments = $this->getObjects("Attachment",array('parent_id'=>$id,'is_deleted'=>0));
		}
		
		if ($attachments)
		{	
			foreach ($attachments as $attachment)
			{
				if ($attachment->isImage())
				{
					$count++;
					
					$gallery .= "<a href=\"$webroot/file/atthumb/$attachment->id/800/600/a.jpg\" rel=\"gallery\"><img src=\"$webroot/file/atthumb/$attachment->id/$width/$height\" border=\"$border\" style=\"margin:".$margin."px; border-color: black;\"/></a>";
				}
			}
			
			$gallery .= "</span>";
			
			$style = "<span style=\" -moz-border-radius: 6px; 
				  	  			     -moz-box-shadow: 0 0 14px #123;
				  	  			     display: -moz-inline-stack;
				  	  			     display: inline-block;
						  		     border: 2px solid black;  
						  		     padding: ".$padding."px; 
						  		     height: ".($width+$padding+$margin+$border*2)."px;
						  		     width: ".($count*($width+$padding+$margin+$border*2))."px;\">";
			
			$gallery = $style.$gallery;
			
			return $gallery;
		}
		else 
		{
			return null;
		}
	}
	
	private function onView_movie($id,$player="player",$name=null,$href=null,$style="display:block;width:425px;height:300px;")
	{					
		for ($i=0;$i<count($this->options);$i++)
		{
			$keyval = explode("=",$this->options[$i]);
			$key = $keyval[0];
			$$key = $keyval[1];
		}
			
		$start = "<span style=\" -moz-border-radius: 6px; 
			  	  			     -moz-box-shadow: 0 0 14px #123;
			  	  			     display: -moz-inline-stack;
			  	  			     display: inline-block;
         			  		     border: 2px solid black;\">";
		$end .= "</span>";
		
		if ($name)
		{
			$attachment = $this->getObject("Attachment",array('parent_id'=>$id,'filename'=>$name,'is_deleted'=>0));

			if ($attachment)
			{
				$video = $start."<a href=\"$webroot/file/atfile/$attachment->id\" style=\"$style\" id=\"$player\"></a>".$end;
				
				$video .= "<script language=\"JavaScript\">flowplayer(\"$player\", \"$webroot/js/flowplayer/flowplayer-3.2.5.swf\", {clip: {autoPlay:false, autoBuffering:true}});</script>";
				
				return $video;
			}
			else 
			{
				return null;
			}
		}
		elseif ($href)
		{
			$pattern = '[>(.+)</a>]';
			
			preg_match($pattern,$href,$result);
			
			if ($result[1])
			{
				$href = $result[1];
			}

			$video = $start."<a href=\"$href\" style=\"$style\" id=\"$player\"></a>".$end;
						
			$video .= "<script language=\"JavaScript\">flowplayer(\"$player\", \"$webroot/js/flowplayer/flowplayer-3.2.5.swf\", {clip: {autoPlay:false, autoBuffering:true}});</script>";

			return $video;
		}
	}
	
	private function onSave_now($id,$format=null)
	{
		for ($i=0;$i<count($this->options);$i++)
		{
			$keyval = explode("=",$this->options[$i]);
			$key = $keyval[0];
			$$key = $keyval[1];
		}
		
		if ($format)
		{
			return date($format);
		}
		else
		{
			return date("Y-m-d H:i:s");
		}
	}
}
