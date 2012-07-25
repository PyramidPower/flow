<?php
// $Id: search.actions.php 660 2010-10-07 02:18:41Z carsten@pyramidpower.com.au $
// (c) 2010 Pyramid Power, Australia
//
// When testing this on windows, please make sure you have the following line
// in your \Windows\System32\drivers\etc\hosts file:
//
// 127.0.0.1   localhost
//
// Also make sure you have started the searchd server:
//
// On windows:
// \sphinx\bin\searchd -c <flow source>\sphinx\conf\windows.sphinx.conf
//
// On linux:
// searchd -c <flow source>/sphinx/conf/linux.sphinx.conf

function index_ALL(Web &$w) {
    $select = Html::select();
}

function results_GET(Web &$w) {
    $q = $w->request('q'); // query

    $idx = $w->request('idx'); // index
    //$p= $w->request('p') ? $w->request('p') : 0; // page number
    //$ps = 20; // page size (after filter)

    if (!$q || strlen($q) < 3) {
        $w->out("Please enter at least 3 characters for searching.");
    } else {
        include_once "sphinx/sphinxapi.php";
        $max = 1000;
        $limit = 1000;
        //$offset = $w->request('of') ? $w->request('of') : 0;

        if (!$idx) {
            $limit = 5;
            $max = 5;
        }

        $cl = new SphinxClient();
        $cl->SetServer('localhost', 9312);
        $cl->SetMatchMode( SPH_MATCH_EXTENDED  );
        $cl->SetLimits(0, $limit, $max);
        $allidx = $w->service('Search')->getSearchIndexes();
        if ($idx) {
	        //$cl->SetLimits($p * $ps, ($ps * 2), $max);
        	$cl->AddQuery($q,$idx);
        } else {
            foreach ($allidx as $idx) {
                $cl->AddQuery($q,$idx[1]);
            }
        }
        $results = $cl->RunQueries();
        $w->ctx('results',$results);
        $w->ctx('allidx',$allidx);
        //$w->ctx('page',$p);
        //$w->ctx('page_size',$ps);
        $w->ctx('title',"Search results for '".$w->request('q')."'");
    }

}

?>
