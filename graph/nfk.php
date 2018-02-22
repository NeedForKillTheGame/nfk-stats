<?php
if (!defined("NFK_LIVE")) define("NFK_LIVE", true);

require_once("../inc/config.inc.php");
require_once("../inc/functions.inc.php");
require_once("../inc/classes.inc.php");
// db connect
$db = new db();
$db->connect(
    $CFG['db_host'],
    $CFG['db_login'],
    $CFG['db_pass'],
    $CFG['db_name'],
    $CFG['db_prefix']
);

require_once("pChart/pData.class");
require_once("pChart/pChart.class");

function createGraph($DataSet,$title,$series,$fileName,$gH = 1200,$gW = 500,$gA = 45) {
	$Test = new pChart($gH,$gW);
	$Test->setFontProperties("Fonts/tahoma.ttf",8);
	$Test->setGraphArea(60,30,1180,450);
	$Test->drawFilledRoundedRectangle(7,7,1200,500,5,240,240,240);
	$Test->drawRoundedRectangle(5,5,1200,500,5,230,230,230);
	$Test->drawGraphArea(255,255,255,TRUE);
	$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,$gA,2);
	$Test->drawGrid(4,TRUE,230,230,230,50);
	$Test->setFontProperties("Fonts/tahoma.ttf",6);
	$Test->drawTreshold(0,143,55,72,TRUE,TRUE);
	$Test->drawArea($DataSet->GetData(),$series,"Serie2",239,238,227,50);
	$Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
	$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);
	$Test->setFontProperties("Fonts/tahoma.ttf",8);
	$Test->drawLegend(65,35,$DataSet->GetDataDescription(),250,250,250);
	$Test->setFontProperties("Fonts/tahoma.ttf",10);
	$Test->drawTitle(60,22,$title,50,50,50,1200);
	$Test->Render($fileName);
}
function createGraphMini($DataSet,$title,$series,$fileName,$gH = 220,$gW = 110,$gA = 0) {
	$Test = new pChart($gH,$gW);
	$Test->setFontProperties("Fonts/tahoma.ttf",8);
	$Test->setGraphArea(22,20,$gH-20,$gW-20);
	//$Test->drawFilledRoundedRectangle(7,7,1200,500,5,240,240,240);
	//$Test->drawRoundedRectangle(5,5,1200,500,5,230,230,230);
	$Test->drawGraphArea(250,250,250,TRUE); // 
	$Test->drawScale($DataSet->GetData(),$DataSet->GetDataDescription(),SCALE_NORMAL,150,150,150,TRUE,$gA,2);
	$Test->drawGrid(1,TRUE,230,230,230,50);
	$Test->setFontProperties("Fonts/tahoma.ttf",6);
	$Test->drawTreshold(0,143,55,72,TRUE,TRUE);
	$Test->drawArea($DataSet->GetData(),$series,"Serie2",239,238,227,50);
	$Test->drawLineGraph($DataSet->GetData(),$DataSet->GetDataDescription());
	$Test->drawPlotGraph($DataSet->GetData(),$DataSet->GetDataDescription(),3,2,255,255,255);
	$Test->setFontProperties("Fonts/tahoma.ttf",8);
	//$Test->drawLegend(0,0,$DataSet->GetDataDescription(),250,250,250);
	$Test->setFontProperties("Fonts/tahoma.ttf",10);
	$Test->drawTitle(0,15,$title,50,50,50,$gH);
	return $Test->Render($fileName);
}
/*
//1
$title = 'Матчей на NFK Planet';
$series = 'Кол-во матчей';
$res = $db->select("COUNT(*) as `count`, YEAR(m.dateTime) AS `year`, MONTH(m.dateTime) as `month`
FROM nfkLive_matchList m
GROUP BY YEAR(m.dateTime), MONTH(m.dateTime)","","");
$matches = array();
foreach($res as $key => $row) {
	$matches[$row["year"].'.'.$row["month"]] = $row;
}
array_pop($matches);
$DataSet = new pData;
foreach($matches as $date => $match) {
	$DataSet->AddPoint($date,"Date"); 
	$DataSet->AddPoint($match["count"],$series);
}
$DataSet->AddSerie($series);
$DataSet->SetAbsciseLabelSerie("Date");
createGraph($DataSet,$title,$series,'nfk6');

//2
$title = 'Игроков на NFK Planet';
$series = 'Кол-во игроков';
$res = $db->select("YEAR(dateTime) AS `year`, MONTH(dateTime) AS `month`, COUNT(*) AS `count`
FROM (
	SELECT * 
	FROM nfkLive_matchList m
	INNER JOIN nfkLive_matchData d USING(matchID)
	GROUP BY YEAR(dateTime), MONTH(dateTime), d.playerID
) AS t
GROUP BY YEAR(dateTime), MONTH(dateTime)","","");
$players = array();
foreach($res as $key => $row) {
	$players[$row["year"].'.'.$row["month"]] = $row;
}
array_pop($players);
$DataSet = new pData;
foreach($players as $date => $plrs) {
	$DataSet->AddPoint($date,"Date"); 
	$DataSet->AddPoint($plrs["count"],$series);
}
$DataSet->AddSerie($series);
$DataSet->SetAbsciseLabelSerie("Date");
createGraph($DataSet,$title,$series,'nfk7');
*/
//3

$fileName = 'nfkstats-players-week.png';
//if (date('Y-m-d H',filemtime(dirname(__FILE__).$filename)) <> date('Y-m-d H')) {
	$title = 'Игроков на NFK Planet';
	$series = 'Кол-во игроков';
	$res = $db->select("DAY(t.dateTime) AS `day`, t.dateTime, COUNT(*) as `count`
		FROM (
			SELECT * 
			FROM nfkLive_matchList m
			INNER JOIN nfkLive_matchData d USING(matchID)
			WHERE  DATE(m.dateTime) >= DATE_ADD(CURDATE(), INTERVAL -7 DAY)
			GROUP BY DAY(dateTime),d.playerID
		) AS t
		GROUP BY DAY(dateTime)","",""
	);
	
	$players = array();
	foreach($res as $key => $row) {
		$players[$row["day"]] = $row;
	}
	//array_pop($players);
	$DataSet = new pData;
	setlocale(LC_ALL, 'ru_RU.UTF-8');
	foreach($players as $date => $plrs) {
		$time = strtotime($plrs["dateTime"]);
		$dateF = strftime('%e%a',$time);
		$DataSet->AddPoint($dateF,"Date"); 
		$DataSet->AddPoint($plrs["count"],$series);
	}
	$DataSet->AddSerie($series);
	$DataSet->SetAbsciseLabelSerie("Date");
	createGraphMini($DataSet,$title,$series,$fileName);
//}



/*
$res = $db->select("COUNT(*) as `count`, YEAR(m.dateTime) AS `month`, MONTH(m.dateTime) as `week`, gameType AS gt
FROM nfkLive_matchList m
WHERE
GROUP BY YEAR(m.dateTime), MONTH(m.dateTime), gameType","","");
*/
/*
$res = $db->select(" COUNT(*) as `count`, DAY(m.dateTime) AS `day`, gameType AS gt
FROM nfkLive_matchList m
WHERE  DATE(m.dateTime) >= DATE_ADD(CURDATE(), INTERVAL -7 DAY)
GROUP BY DAY(m.dateTime)","","");
*/
/*
$res = $db->select(" YEAR(dateTime) AS `year`, MONTH(dateTime) AS `month`, COUNT(*) AS `count`
FROM (
	SELECT * 
	FROM nfkLive_matchList m
	INNER JOIN nfkLive_matchData d USING(matchID)
	GROUP BY YEAR(dateTime), MONTH(dateTime), d.playerID
) AS t
GROUP BY YEAR(dateTime), MONTH(dateTime)","","");

// Dataset definition 
$DataSet = new pData;
foreach($res as $key => $match) {
	$DataSet->AddPoint($match["count"],'Кол-во игроков');
	$DataSet->AddPoint("$match[year].$match[month]","Date"); 
}*/
/*
$matches = array();
foreach($res as $key => $row) {
	$matches[$row["month"].'.'.$row["week"]][$row["gt"]] = $row;
}
foreach($matches as $date => $gameTypes) {
	$DataSet->AddPoint($date,"Date"); 
	foreach($gameTypes as $gameType => $match) {
		$DataSet->AddPoint($match["count"],$gameType);
	}
} */

/*
$DataSet->AddSerie("DUEL");
$DataSet->AddSerie("CTF");
$DataSet->AddSerie("RAIL");
$DataSet->AddSerie("DOM");
$DataSet->AddSerie("TDM");
$DataSet->AddSerie("DM");
$DataSet->AddSerie("PRAC");*/
//$DataSet->AddSerie("Кол-во матчей");
/*$DataSet->AddSerie("Кол-во игроков");

$DataSet->SetAbsciseLabelSerie("Date");*/
// Initialise the graph

?>