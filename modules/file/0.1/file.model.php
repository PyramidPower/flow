<?php
// $Id: file.model.php 1006 2010-12-06 04:00:56Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

class FileService extends DbService {
    function getImg($path) {
        $file = FILE_ROOT."/".$path;
        if (!file_exists($file))
            return null;

        list($width, $height, $type, $attr)  = getimagesize($file);

        $tag = "<img src='".WEBROOT."/file/path/".$path."' border='0' ".$attr." />";
        return $tag;
    }

    function getThumbImg($path) {
        $file = FILE_ROOT."/".$path;
        if (!file_exists($file))
            return $path." does not exist.";

        list($width, $height, $type, $attr)  = getimagesize($file);

        $tag = "<img src='".WEBROOT."/file/thumb/".$path."' border='0' ".$attr." />";
        return $tag;
    }

    function isImage($path) {
        list($width, $height, $type, $attr)  = getimagesize(str_replace("'","\\'",FILE_ROOT."/".$path));
        return $attr !== null;
    }

    function getDownloadUrl($path) {
        return WEBROOT."/file/path/".$path;
    }


    function & getAttachments($objectOrTable,$id=null) {
        if (is_a($objectOrTable, "DbObject")) {
            $table = $objectOrTable->getDbTableName();
            $id = $objectOrTable->id;
        } elseif (is_scalar($objectOrTable)) {
            $table = $objectOrTable;
        }
        if ($table && $id) {
            $rows = $this->_db->get("attachment")->where("parent_table",$table)->and("parent_id",$id)->and("is_deleted",0)->fetch_all();
            return $this->fillObjects("Attachment", $rows);
        }
        return null;
    }

    function & getAttachment($id) {
        return $this->getObject("Attachment", $id);
    }

    /**
     * moves an uploaded file from the temp location
     * to
     *
     *  /files/attachments/<attachTable>/<year>/<month>/<day>/<attachId>/<filename>
     *
     * @param <type> $filename
     * @param <type> $attachTable
     * @param <type> $attachId
     * @param <type> $title
     * @param <type> $description
     * @return the id of the attachment object or null
     */
    function uploadAttachment($requestkey,$parentObject,$title=null,$description=null,$type_code=null) {
        if (!is_a($parentObject, "DbObject")) {
            $this->w->error("Parent not found.");
        }
        // we could check if the attach id actually exists
        // but will leave this for later
        $uploaddir = FILE_ROOT. 'attachments/'.$parentObject->getDbTableName().'/'.date('Y/m/d').'/'.$parentObject->id.'/';
        if (!file_exists($uploaddir)) {
            mkdir($uploaddir,0770,true);
        }
        $rpl_nil = array("..","'",'"',",","\\","/");
        $rpl_ws = array(" ","&","+","$","?","|","%","@","#","(",")","{","}","[","]",",",";",":");
        $filename = str_replace($rpl_nil, "", basename($_FILES[$requestkey]['name']));
        $filename = str_replace($rpl_ws, "_", $filename);
        $uploadfile = $uploaddir . $filename;

        if (move_uploaded_file($_FILES[$requestkey]['tmp_name'], $uploadfile)) {
            $att = new Attachment($this->w);
            $att->filename = $filename;
            $att->fullpath = str_replace(FILE_ROOT, "", $uploadfile);
            $att->parent_table = $parentObject->getDbTableName();
            $att->parent_id = $parentObject->id;
            $att->title = $title;
            $att->description = $description;
            $att->type_code = $type_code;
            $att->insert();
            return $att->id;
        } else {
            $this->w->error("Possible file upload attack.");
        }
        return null;
    }
    
    function getAttachmentTypesForObject($o) {
    	return $this->getObjects("AttachmentType",array("table_name"=>$o->getDbTableName(), "is_active"=>'1'));
    }
    
    function getImageAttachmentTemplateForObject($object,$backUrl) {
    	$attachments = $this->getAttachments($object);
    	$template = "";
    	foreach($attachments as $att) {
			if ($att->isImage()) {				
				$template .= '			
				<div class="attachment">
				<div class="thumb"><a
					href="'.$webroot.'/file/atthumb/'.$att->id.'/800/600/a.jpg"
					rel="gallery"><img
					src="'.$webroot.'/file/atthumb/'.$att->id.'/250/250" border="0" /></a><br/>'.$att->description.'
				</div>
				
				<div class="actions">'.Html::a($webroot."/file/atdel/".$att->id."/".$backUrl."+".$object->id,"Delete",null,null,"Do you want to delete this attachment?")
				.' '.Html::a($webroot."/file/atfile/".$att->id."/".$att->filename,"Download").'
				</div>
				</div>';
			}
    	}
    	return $template;
    }
    
}

class AttachmentType extends DbObject {
	var $id;
	var $table_name;
	var $code;
	var $title;
	var $is_active;
	
	function getDbTableName() {
		return "attachment_type";
	}
	
	/**
	 * returns the title to be displayed in select boxes
	 * @see web.lib/DbObject::getSelectOptionTitle()
	 */
	function getSelectOptionTitle() {
		return $this->title;
	}

	/**
	 * return the value used in select boxes
	 * @see web.lib/DbObject::getSelectOptionValue()
	 */
	function getSelectOptionValue() {
		return $this->code;
	}

}

class Attachment extends DbObject {
    var $parent_table;
    var $parent_id;

    var $dt_created; // datetime
    var $dt_modified; // datetime
    var $modifier_user_id; // bigint

    var $filename; // varchar(255)
    var $mimetype; // varchar(255)

    var $title; // varchar(255)
    var $description; // text

    var $fullpath; // varchar(255)
    var $is_deleted; // tinyint 0/1

    var $type_code; // this is a type of attachment, eg. Receipt of Deposit, PO Variation, Sitephoto, etc.
    
    function insert() {
        $this->dt_modified = time();
        $this->mimetype = $this->w->getMimetype(FILE_ROOT."/".$this->fullpath);
        $this->modifier_user_id = $this->w->auth->user()->id;
        $this->fullpath = str_replace(FILE_ROOT, "", $this->fullpath);
        $this->is_deleted = 0;
        parent::insert();
    }

    function & getParent() {
        return $this->getObject($this->attach_table, $this->attach_id);
    }

    /**
     * will return true if this attachment
     * is an image
     */
    function isImage() {
        return $this->File->isImage($this->fullpath);
    }

    /**
     * Returns a HTML <img> tag for this attachment
     * only if this attachment is an image,
     * else 
     */
    function getImg() {
    	if ($this->isImage()) {
        	return $this->File->getImg($this->fullpath);
    	} else {
    		
    	}
    }
    /**
     * if image, create image thumbnail
     * if any other file send an icon for this mimetype
     */
    function getThumbnailUrl() {
        if ($this->isImage()) {
            return WEBROOT."/file/thumb/".$this->fullpath;
        } else {
            return WEBROOT."/img/document.jpg";
        }
    }

    /**
     * 
     * Returns html code for a thumbnail link to download this attachment
     */
    function getThumb() {
        $buf = "<a href='".$this->File->getDownloadUrl($this->fullpath)."'>";
        $buf .="<img src='".$this->getThumbnailUrl()."' border='0'/></a>";
        return $buf;
    }
    
    function getDownloadUrl() {
    	return $this->File->getDownloadUrl($this->fullpath);
    }
    
    function getCodeTypeTitle()
    {
    	$t = $this->w->auth->getObject('AttachmentType', array('code'=>$this->type_code,'table_name'=>$this->parent_table));
    	
    	if($t)
    	{
    		return $t->title;
    	}
    	else
    	{
    		return null;
    	}
    }

}


class PdfForm extends DbObject
{
	var $title;
	var $module;   	// module name
	var $type;     	// type of form; use Operations->getPdfTypes() 
	var $classname;	// 
	
	var $dt_created;
	var $creator_id;
	var $dt_modified;
	var $modifier_id;
	var $is_deleted;
	
	function getDbTableName() {
		return "pdf_form";
	}
}

?>
