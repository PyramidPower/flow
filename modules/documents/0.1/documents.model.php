<?php
// $Id: documents.model.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia

class DocumentService extends DbService {
    function getDocumentStore() {
        // according to configuration
        // create a documen store
    }
}

abstract class DocumentStore {
    
	function createDocument($type,$folder,$name,$mimetype,$metadata);

    function moveFolder($this,$folder);
    
    function copyFolder($this,$folder,$name,$title);
    
    function saveFolder($this);
    
    function deleteFolder($this);

}

class FileStore extends DocumentStore {
	
}

class S3Store extends DocumentStore {
	
}

class DbStore extends DocumentStore {
	
}

abstract class DocumentObject {
    
	
	var $id;
	var $store;
    
    
    var $name;
    var $title;
    var $description;
    var $metadata;
    
    var $dt_created;
    var $dt_modified;
    var $_md_fetched = false;

    function & getStore() {
        return $this->store;
    }

    function  __construct(&$store,$name,$metadata) {
        $this->store = $store;
        $this->name = $name;
        $this->metadata = $metadata;
    }

//    function getId() {
//        return $this->id;
//    }
//    function setId($id){
//        $this->id = $id;
//    }
//
//    function getTimestampCreated() {
//        return $this->timestampCreated;
//    }
//    function setTimestampCreated($ts){
//        $this->timestampCreated = $ts;
//    }
//
//    function getTimestampModified() {
//        return $this->timestampModified;
//    }
//    function setTimestampModified($ts) {
//        $this->timestampModified=$ts;
//    }
//
//    function getName() {
//        return $this->name;
//    }
//    function setName($name) {
//        $this->name = $name;
//    }
//
//    function getTitle() {
//        return $this->title;
//    }
//    function setTitle($title) {
//        $this->title = $title;
//    }
//
//    function getDescription() {
//        return $this->description;
//    }
//    function setDescription($d){
//        $this->description = $d;
//    }

    function getMetadata() {
        if ($this->metadata === null && !$this->_md_fetched) {
            $this->metadata = $this->getStore()->getFolderMetadata();
            $this->_md_fetched = true;
        }
        return $this->metadata;
    }

    function setMetadata($md) {
        $this->metadata = $md;
    }

    function metaSet($key,$value) {
        if ($key === null) return;
        // make sure md is loaded
        $this->getMetadata();
        // write mode
        $this->metadata[$key] = $value;
    }

    function metaGet($key) {
        if ($key === null) return null;
        // make sure md is loaded
        $this->getMetadata();
        if (array_key_exists($key, $this->metadata)) {
            return $this->metadata[$key];
        } else {
            return null;
        }
    }

    function metaUnset($key) {
        if ($key === null) return;
        // make sure md is loaded
        $this->getMetadata();
        unset ($this->metadata[$key]);
    }

    function isDocument();

    function isFolder();

}

class Folder extends DocumentObject {

    function & getParent() {
        return $this->getStore()->getFolderParent($this);
    }

    function & getAllParents() {
        return $this->getStore()->getAllFolderParents($this);
    }

    function move(&$folder) {
        $this->getStore()->moveFolder($this,$folder);
    }

    function & copy (&$folder,$name=null,$title=null) {
        $this->getStore()->copyFolder($this,$folder,$name,$title);
    }

    function save() {
        $this->getStore()->saveFolder($this);
    }

    function delete() {
        $this->getStore()->deleteFolder($this);
    }

    function isDocument() {
        return false;
    }

    function isFolder() {
        return true;
    }
}

class Document extends DocumentObject {
    var $tags;
    var $mimetype;

    var $_tags_fetched;

    function getContent() {
        return $this->getStore()->getDocumentContent($this);
    }
    
    function setContent($c) {
        $this->getStore()->setDocumentContent($c);
    }

    function getMimetype(){
        return $this->mimetype;
    }
    
    function setMimetype($m) {
        $this->mimetype = $m;
    }

    function getTags(){
        if ($this->tags === null && !$this->_tags_fetched) {
            $this->tags = $this->getStore()->getDocumentTags($this);
        }
        return $this->tags;
    }
    
    function setTags($tags);
    function hasTag($tag);

    function & getFolder() {
        return $this->getStore()->getDocumentFolder($this);
    }

    function move(&$folder) {
        $this->getStore()->moveDocument($this,$folder);
    }

    function & copy (&$folder,$name=null,$title=null) {
        return $this->getStore()->copyDocument($this,$folder,$name,$title);
    }

    function save() {
        $this->getStore()->saveDocument($this);
    }

    function delete() {
        $this->getStore()->deleteDocument($this);
    }

    function isDocument() {
        return true;
    }

    function isFolder() {
        return false;
    }

}
?>
