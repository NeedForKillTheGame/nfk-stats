<?php
if (!defined("NFK_LIVE")) die();

$matchID = $PARAMSTR[2];
if (!is_numeric($matchID)) header("Location: $_SERVER[HTTP_REFERER]");
$match = $db->select("demo","matchList","WHERE matchID=$matchID");
$match = $match[0];
$urlFile = urlencode($match['demo']);
$db->update("matchList","dlnum = dlnum+1","WHERE matchID=$matchID");
header("Content-type: application/ndm");
header("Location: /demos/$urlFile");
?>