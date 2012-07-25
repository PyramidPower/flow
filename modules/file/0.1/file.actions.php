<?php
// $Id: file.actions.php 602 2010-09-28 05:51:08Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

function file_path_GET(Web &$w) {
	// make sure we secure from /../../etc/passwd attacks!!
    $filename = str_replace("..", "", FILE_ROOT.$w->getPath());
    $w->sendFile($filename);
}

function file_atfile_GET(Web &$w) {
    $p = $w->pathMatch("id");
    $id = str_replace(".jpg", "", $p['id']);
    $attachment = $w->service("File")->getAttachment($id);
    $w->sendFile(FILE_ROOT.$attachment->fullpath);
}

function file_atthumb_GET(Web &$w) {
    $p = $w->pathMatch("id",array("w",150),array("h",150));

    $id = str_replace(".jpg", "", $p['id']);
    $attachment = $w->service("File")->getAttachment($id);
    require_once 'phpthumb/ThumbLib.inc.php';
    $thumb = PhpThumbFactory::create(FILE_ROOT.$attachment->fullpath);
    $thumb->resize($p['w'], $p['h']);
    //$thumb->adaptiveResize($p['w'], $p['h']);
    $thumb->show();
    exit;
}

function file_atdel_GET(Web &$w) {
    $p = $w->pathMatch("id","url");
    $att = $w->service("File")->getAttachment($p['id']);
    if ($att) {
        $w->ctx('attach_id',$att->id);
        $w->ctx('attach_table',$att->parent_table);
        $w->ctx('attach_table_id',$att->parent_id);
        $w->ctx('attach_title',$att->title);
        $w->ctx('attach_description',$att->description);       
        $att->delete();
        $w->msg("Attachment deleted.","/".str_replace(" ","/",$p['url']));
    } else {
        $w->error("Attachment does not exist.","/".str_replace(" ","/",$p['url']));
    }
}

function file_thumb_GET(Web &$w) {
    $filename = str_replace("..", "", FILE_ROOT.$_REQUEST['path']);
    $w = $w->request("w",150);
    $h = $w->request("h",150);
    require_once 'phpthumb/ThumbLib.inc.php';
    $thumb = PhpThumbFactory::create($filename);
    $thumb->adaptiveResize($w, $h);
    $thumb->show();
    exit;
}

function file_attach_GET(Web &$w) {
    $w->setLayout(null);
    $p = $w->pathMatch("table","id","url");
    $object = $w->auth->getObject($p['table'],$p['id']);
    if (!$object) {
        $w->error("Nothing to attach to.");
    }
    $types = $w->File->getAttachmentTypesForObject($object);
    $w->ctx("types",$types);
}

function file_attach_POST(Web &$w) {
    $table = $_REQUEST['table'];
    $id = $_REQUEST['id'];
    $title = $_REQUEST['title'];
    $description = $_REQUEST['description'];
    $type_code = $_REQUEST['type_code'];
    
    $url = str_replace(" ", "/", $_REQUEST['url']);
    $object = $w->auth->getObject($table,$id);
    if (!$object) {
        $w->error("Nothing to attach to.",$url);
    }

    $aid = $w->service("File")->uploadAttachment("file",$object,$title,$description,$type_code);
    if ($aid) {
        $w->ctx('attach_id',$aid);
        $w->ctx('attach_table',$table);
        $w->ctx('attach_table_id',$id);
        $w->ctx('attach_title',$title);
        $w->ctx('attach_description',$description);
        $w->ctx('attach_type_code',$type_code);
        $w->msg("File attached.",$url);
    } else {
        $w->error("There was an error. Attachment could not be saved.",$url);
    }

}

function file_printview_GET(Web &$w) {
    $p = $w->pathMatch("table","id");
    $attachments = $w->service("File")->getAttachments($p['table'], $p['$id']);
    $w->ctx("attachments",$attachments);
    $w->setLayout(null);
}

?>
