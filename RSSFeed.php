<?php

$rssfeed = array(
    'oica' => 'http://oica.upd.edu.ph/feed',
    'ovcaa' => 'http://ovcca.upd.edu.ph/category/news-and-events/feed/',
    'ovcca' => 'http://ovcca.upd.edu.ph/category/news-and-events/feed/',
    'up' => 'https://www.up.edu.ph/index.php/feed/',
    'ilc' => 'https://ilc.upd.edu.ph/feed',
    'upd' => 'https://upd.edu.ph/category/announcements/feed/'
);

function getItemsvalue($url){
    $xml = new DOMDocument();
    $xml->load($url);
    $data = $xml->getElementsByTagName('item');
    $rssdata = array();
    foreach($data as $attrib){
        $title = $attrib->getElementsByTagName('title')->item(0)->nodeValue;
        $link = $attrib->getElementsByTagName('link')->item(0)->nodeValue;
        $desc = $attrib->getElementsByTagName('description')->item(0)->nodeValue;
        $rssdata [] = array('title'=>$title, 'link'=>$link, 'desc'=>$desc);
    }
    return $rssdata;
}
$rssreq = $_POST['rssreq'];
$rssfeedarr = array_keys($rssfeed);
foreach($rssfeedarr as $rss){
    if($rssreq == $rss){
        var_dump(json_encode(getItemsvalue($rssfeed[$rss]), true));
    }
}
