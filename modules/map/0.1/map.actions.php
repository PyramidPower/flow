<?php 
// $Id: map.actions.php 414 2010-08-20 04:37:22Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia


function map_address_GET(Web &$w){
    include_once "lib/phpgmaps/GoogleMap.php";
    include_once "lib/phpgmaps/JSMin.php";

    // prepare the google map
    $MAP_OBJECT = new GoogleMapAPI();
    $MAP_OBJECT->_minify_js = TRUE;
    $address = $_GET['address'].", ".$_GET['suburb'].", ".$_GET['state'].", ".$_GET['postcode'].", ".$_GET['country'];
    $MAP_OBJECT->addMarkerByAddress($address,"Address", $address);
    $header .= $MAP_OBJECT->getHeaderJS();
    $header .= $MAP_OBJECT->getMapJS();
    $header .= $MAP_OBJECT->getOnLoad();
    $w->ctx('header',$header);
    $w->ctx('width',$_GET['w']?$_GET['w']:800);
    $w->ctx('height',$_GET['h']?$_GET['h']:700);
}

?>
