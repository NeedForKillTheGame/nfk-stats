<?php
if (!defined("NFK_LIVE")) define("NFK_LIVE", true);

// Configuration
require_once("inc/config.inc.php");
// Functions
require_once("inc/functions.inc.php");
// Classes
require_once("inc/classes.inc.php");
// db connect
$db = new db();
$db->connect(
    $CFG['db_host'],
    $CFG['db_login'],
    $CFG['db_pass'],
    $CFG['db_name'],
    $CFG['db_prefix']
);
$SITE = <<<HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link type="text/css" rel="StyleSheet" href="themes/default/css/style.css" />
	<title>NFK - Карты</title>
</head>
<body>
<div align="center">
<span class="headmenu"><a href="http://needforkill.ru/">NEEDFORKILL.RU</a></span> <span class="headmenu"><a href="http://stats.needforkill.ru/">STATS.NEEDFORKILL.RU</a></span>
<br />
<br />
Топ 100 карт
<table id="tbl" border="0" cellspacing="1">
	<thead>
		<tr>
			<th>Место</th>
			<th>Карта</th>
			<th>Матчей</th>
		</tr>
	</thead> 
HTML;
$res = $db->select('count(map) as Count, map','matchList','GROUP BY map ORDER BY Count DESC LIMIT 100');
if ($res == NULL) die('error');
$i = 0;
foreach ($res as $row) {
	$i++;
	$SITE .= <<<HTML
		<tr bgcolor="#C7CCD9">
			<td>$i</td>
			<td><span class='newsautor'>$row[map]</span></td>
			<td><span class='newsnumcom'>$row[Count]</span></td>
		</tr>
HTML;
}

$SITE .= <<<HTML
</table>
</div>
</body>
</html>
HTML;

print $SITE;
?>