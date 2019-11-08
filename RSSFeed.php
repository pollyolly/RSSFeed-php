<?php
include '../inc/messages.php';

$rssfeed = array(
	'ILC' => 'http://ilc.upd.edu.ph/feed',
	'OVCAA' => 'https://ovcaa.upd.edu.ph/feed/',
	'OUR' => '',
	'UL' => '',
	'OAT' => '',
	'OIL' => '',
	'NSTP' => '',
	'OFA' => '',
	'GEC' => '',
	'OVCCA' => 'http://ovcca.upd.edu.ph/category/news-and-events/feed/',
	'UP' => 'http://www.up.edu.ph/index.php/category/announcements/feed/',
	'UPD' => 'https://upd.edu.ph/category/announcements/feed/'
);

function cacheRSS($cacheFile, $url){
	$cacheTime = 3600*1; //1 hour
	$fileTime = @(time() - filemtime($cacheFile));
	$storedFile = file_get_contents($cacheFile);
	$urlFile = file_get_contents($url);
	if($storedFile == $urlFile) {
	    $rssContent = file_get_contents($cacheFile);
	} else {
		/*$rssContent = file_get_contents($url);*/
		$rssContent = httpRequest($url);
		$fileOpen = @fopen($cacheFile, 'w');
		if($fileOpen){
	        	fwrite($fileOpen, $rssContent);
	        	fclose($fileOpen);
		}
	}
	$xml = simplexml_load_string($rssContent);
	return $xml;
}

function getItemsvalue($url, $office){
	try{
		$cacheFile = '../cache/'.$office.'.rss';
		$rss = cacheRSS($cacheFile, $url);
	} catch(Exception $e) {
		return $e->getMessage();
	}
	$rssdata = array();
	foreach($rss->channel->item as $attrib){
		$title = htmlspecialchars($attrib->title);
		$link = htmlspecialchars($attrib->link);
		$desc = htmlspecialchars($attrib->description);
		$date = htmlspecialchars($attrib->pubDate);
		$rssdata [] = array('office'=>$office,'title'=>$title, 'link'=>$link, 'desc'=>$desc, 'date'=>date('Y/m/d', strtotime(substr($date, 0, 16))));
	}
	return $rssdata;
}

function httpRequest($url){
	if (extension_loaded('curl')) {
        	$curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_HEADER, FALSE);
                curl_setopt($curl, CURLOPT_TIMEOUT, 20);
                curl_setopt($curl, CURLOPT_ENCODING , '');
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE); // no echo, just return result
                if (!ini_get('open_basedir')) {
                	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE); // sometime is useful :)
                }
               	$result = curl_exec($curl);
                return curl_errno($curl) === 0 && curl_getinfo($curl, CURLINFO_HTTP_CODE) === 200 ? $result : FALSE;
        } else {
        	throw new FeedException('PHP extension CURL is not loaded.');
        }
}

$json = json_decode(file_get_contents('php://input'));
$rssreq = $json->rssreq;
$rssfeedarr = array_keys($rssfeed);
foreach($rssfeedarr as $rss){
	if($rssreq == $rss){
		$rssResponse = getItemsvalue($rssfeed[$rss], $rss);
		if($rssResponse === 'Cannot load feed.'){
			$message['HttpCode'] = '5002';
			$message['Response'] = array('Offices'=>array_keys($rssfeed),'RSS'=>[]);
			_failedRSSFeed($message);
		} else {
			$message['HttpCode'] = '5001';
			$message['Response'] = array('Offices'=>array_keys($rssfeed),'RSS'=>$rssResponse);
			_successRSSFeed($message);
		}
	}
}
