<?php
if (!defined("NFK_LIVE")) die();

$matchID = $PARAMSTR[2];
if (!is_numeric($matchID)) header("Location: $_SERVER[HTTP_REFERER]");
$match = $db->select("demo","matchList","WHERE matchID=$matchID");
$match = $match[0];

if (!$match['demo'] || !file_exists("demos/{$match['demo']}"))
	die("Demo file does not exist!");

$urlFile = urlencode($match['demo']);

// if 'nocount' parameter not passed then increase download counter
if ( !isset($_GET['nocount']) ) {
	$db->update("matchList","dlnum = dlnum+1","WHERE matchID=$matchID");
}

header("Content-type: application/ndm");
header("Location: /demos/$urlFile");

die();
