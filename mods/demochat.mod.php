<?php
if (!defined("NFK_LIVE")) die();

$matchID = $PARAMSTR[2];
if (!is_numeric($matchID)) header("Location: $_SERVER[HTTP_REFERER]");
$match = $db->select("demo","matchList","WHERE matchID=$matchID");
$match = $match[0];

if (!$match['demo'] || !file_exists("demos/{$match['demo']}"))
	die("Demo file does not exist!");

$urlFile = urlencode($match['demo']);

// fetch json with demo chat data 
$url = "http://nfk.harpywar.com:8080/demo?type=chat&url=" . urlencode("https://stats.needforkill.ru/demos/$urlFile");
if (($json = @file_get_contents($url)) ) {
	header("Content-type: application/json");
	echo $json;
} else {
	echo "Demo error";
}
die();