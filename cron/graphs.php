<?php
error_reporting(E_ALL);
if (!defined("NFK_LIVE")) define("NFK_LIVE", true);

$cronPeriod = $_GET['period'];

require_once("../inc/config.inc.php");
require_once("../inc/functions.inc.php");
require_once("../inc/classes.inc.php");

/* CAT:Line chart */ 
/* pChart library inclusions */ 
include_once("../inc/pChart/class/pData.class.php"); 
include_once("../inc/pChart/class/pDraw.class.php"); 
include_once("../inc/pChart/class/pImage.class.php"); 

function getGraph($vars = '') {
	$graphData = $vars['graphData'];
	$graphH = isset($vars['graphH']) ? $vars['graphH'] : 220;
	$graphW = isset($vars['graphW']) ? $vars['graphW'] : 110;
	$captionText = isset($vars['captionText']) ? $vars['captionText'] : '';
	$fileName = isset($vars['fileName']) ? $vars['fileName'] : 'temp.png';
	$labelRotation = isset($vars['labelRotation']) ? $vars['labelRotation'] : 0;
	$transperent = isset($vars['transperent']) ? $vars['transperent'] : TRUE;
	$fontSize = isset($vars['fontSize']) ? $vars['fontSize'] : 7;
	$graphArea = isset($vars['graphArea']) ? $vars['graphArea'] : array(
		'x'=>16,'y'=>16,'h'=>-16,'w'=>-16
	);
	$legend = isset($vars['legend']) ? $vars['legend'] : false;
	if (!$graphData) return false;
	
	$myPicture = new pImage($graphH,$graphW,$graphData,$transperent); 
	$myPicture->Antialias = FALSE; 
	//$myPicture->drawRectangle(0,0,$graphH-1,$graphW-1,array("R"=>0,"G"=>0,"B"=>0));
	$myPicture->setFontProperties(array("FontName"=>"../inc/pChart/fonts/verdana.ttf","FontSize"=>$fontSize)); 
	$myPicture->drawText((int)$graphH/2,16,$captionText,array("FontSize"=>9,"Align"=>TEXT_ALIGN_BOTTOMMIDDLE)); 
	$myPicture->setGraphArea($graphArea['x'],$graphArea['y'],$graphH+$graphArea['h'],$graphW+$graphArea['w']); 
	$scaleSettings = array("TickAlpha"=>100,"Mode"=>SCALE_MODE_START0, "LabelRotation"=>$labelRotation,"XMargin"=>0,"YMargin"=>0,"Floating"=>TRUE,"GridR"=>200,"GridG"=>200,"GridB"=>200,"DrawSubTicks"=>TRUE,"CycleBackground"=>TRUE); 
	$myPicture->drawScale($scaleSettings); 
	$myPicture->Antialias = TRUE; 
	$myPicture->drawAreaChart(); 
	$myPicture->setShadow(TRUE,array("X"=>1,"Y"=>1,"R"=>0,"G"=>0,"B"=>0,"Alpha"=>10)); 
	$myPicture->drawLineChart(); 
	$myPicture->drawPlotChart(array("PlotBorder"=>TRUE,"PlotSize"=>2,"BorderSize"=>1,"Surrounding"=>-60,"BorderAlpha"=>25));
	if ($legend) {
		$myPicture->drawLegend(50,20,array("Style"=>LEGEND_NOBORDER,"Mode"=>LEGEND_VERTICAL));
	}
	//$myPicture->autoOutput("example.drawLineChart.simple.png"); 
	
	$myPicture->render($fileName); 
}

$db = new db();
$db->connect($CFG['db_host'], $CFG['db_login'], $CFG['db_pass'], $CFG['db_name'], $CFG['db_prefix']);
setlocale(LC_ALL, 'ru_RU.UTF-8'); // ru_RU.UTF-8 rus

if ($cronPeriod == 'graph-7days') {
	$statDays = 7;
	$res = $db->select("DATE(t.dateTime) AS dateTime, COUNT(*) as `count`
		FROM (
			SELECT * 
			FROM nfkLive_matchList m
			INNER JOIN nfkLive_matchData d USING(matchID)
			WHERE  DATE(m.dateTime) >= DATE_ADD(CURDATE(), INTERVAL -$statDays DAY)
			GROUP BY DATE(dateTime),d.playerID
		) AS t
		GROUP BY DATE(t.dateTime)","",""
	);	
	$stats = array();
	foreach($res as $row) {
		$stats[$row['dateTime']] = $row['count'];
	}
	$players = array();
	$daysLabel = array();
	for ($i = $statDays; $i >= 0; $i--) {
		$time = strtotime("-$i days");
		$day = date('Y-m-d',$time);
		$players[] = ($stats[$day])?$stats[$day]:0;
		$daysLabel[] = strftime('%e%a',$time); //  iconv("windows-1251", "UTF-8",strftime('%#d%a',$time));
	}
	$MyData = new pData();   
	$MyData->addPoints($players,"Игроки"); 
	$MyData->addPoints($daysLabel,"Labels"); 
	$MyData->setSerieDescription("Labels","Дни"); 
	$MyData->setAbscissa("Labels"); 
	getGraph(array(
		'graphData' => $MyData,
		'graphH' => 240,
		'graphW' => 110,
		'captionText' => 'Игроки на NFK Planet',
		'fileName' => '../images/graph-players-week.png',
		'graphArea' => array(
			'x'=>16, 'y'=>16, 'h'=>-16, 'w'=>-16
		),
	));
}

if ($cronPeriod == 'graph-62days') {
	$statDays = 62;
	$res = $db->select("*
		FROM (
			SELECT DATE(t.dateTime) AS dateTime, COUNT(*) AS `count_players`
			FROM (
				SELECT * 
				FROM nfkLive_matchList m
				INNER JOIN nfkLive_matchData d USING(matchID)
				WHERE  DATE(m.dateTime) >= DATE_ADD(CURDATE(), INTERVAL -$statDays DAY)
				GROUP BY DATE(dateTime),d.playerID
			) AS t
			GROUP BY DATE(t.dateTime)
		) AS t1
		INNER JOIN (
			SELECT DATE(dateTime) AS dateTime, COUNT(*) AS `count_matches`
			FROM nfkLive_matchList m
			WHERE  DATE(m.dateTime) >= DATE_ADD(CURDATE(), INTERVAL -$statDays DAY)
			GROUP BY DATE(dateTime)
		) AS t2 USING(dateTime)
		","",""
	);
	$statsPlayers = array();
	$statsMatches = array();
	foreach($res as $row) {
		$statsPlayers[$row['dateTime']] = $row['count_players'];
		$statsMatches[$row['dateTime']] = $row['count_matches'];
	}
	$players = array();
	$matches = array();
	$daysLabel = array();
	for ($i = $statDays; $i >= 1; $i--) {
		$time = strtotime("-$i days");
		$day = date('Y-m-d',$time);
		$players[] = ($statsPlayers[$day])?$statsPlayers[$day]:0;
		$matches[] = ($statsMatches[$day])?$statsMatches[$day]:0;
		$daysLabel[] = strftime('%m.%e %a',$time); //  iconv("windows-1251", "UTF-8",strftime('%m/%#d%a',$time));
	}
	$MyData = new pData();   
	$MyData->addPoints($matches,"Матчи"); 
	$MyData->addPoints($players,"Игроки"); 
	
	$MyData->addPoints($daysLabel,"Labels"); 
	$MyData->setSerieDescription("Labels","Дни"); 
	$MyData->setAbscissa("Labels"); 

	
	getGraph(array(
		'graphData' => $MyData,
		'graphH' => 1280,
		'graphW' => 200,
		'captionText' => 'Активность на NFK Planet',
		'labelRotation' => 45,
		'fileName' => '../images/graph-players-2months.png',
		'graphArea' => array(
			'x'=>35, 'y'=>16, 'h'=>-16, 'w'=>-40
		),
		'legend' => true,
	));
}
if ($cronPeriod == 'graph-year-month-matches' || $cronPeriod == 'graph-62days') {
	$res = $db->select("YEAR(m.dateTime) AS dateYear, MONTH(m.dateTime) AS dateMonth, COUNT(*) AS `count_matches`
            FROM nfkLive_matchList m
            GROUP BY YEAR(m.dateTime), MONTH(m.dateTime)","",""
    );
	$statsPlayers = array();
	$statsMatches = array();
	foreach($res as $row) {
        $key = $row['dateYear'] . '.' .$row['dateMonth'];
		$statsMatches[$key] = $row['count_matches'];
	}
	$matches = array();
	$daysLabel = array();
    foreach ($statsMatches as $yearMonth => $count) {
        $matches[] = $count;
        $daysLabel[] = $yearMonth;
    }
	$MyData = new pData();
	$MyData->addPoints($matches,"Матчи");
	$MyData->addPoints($daysLabel,"Labels");
	$MyData->setSerieDescription("Labels","Дни");
	$MyData->setAbscissa("Labels");
	getGraph(array(
		'graphData' => $MyData,
		'graphH' => 1280,
		'graphW' => 200,
		'captionText' => 'Активность на NFK Planet',
		'labelRotation' => 45,
		'fileName' => '../images/graph-month-year-matches.png',
		'graphArea' => array(
			'x'=>35, 'y'=>16, 'h'=>-16, 'w'=>-40
		),
		'legend' => true,
	));
}

if ($cronPeriod == 'graph-year-month-players' || $cronPeriod == 'graph-62days') {
	$res = $db->select("YEAR(t.dateTime) AS dateYear, MONTH(t.dateTime) AS dateMonth, COUNT(*) AS `count_players`
        FROM (
            SELECT *
            FROM nfkLive_matchList m
            INNER JOIN nfkLive_matchData d USING(matchID)
            GROUP BY YEAR(m.dateTime), MONTH(m.dateTime), d.playerID
        ) AS t
        GROUP BY YEAR(t.dateTime), MONTH(t.dateTime)","",""
    );
	$statsPlayers = array();
	foreach($res as $row) {
        $key = $row['dateYear'] . '.' .$row['dateMonth'];
		$statsPlayers[$key] = $row['count_players'];
	}
	$players = array();
	$daysLabel = array();
    foreach ($statsPlayers as $yearMonth => $count) {
        $players[] = $count;
        $daysLabel[] = $yearMonth;
    }
	$MyData = new pData();
	$MyData->addPoints($players,"Игроки");
	$MyData->addPoints($daysLabel,"Labels");
	$MyData->setSerieDescription("Labels","Дни");
	$MyData->setAbscissa("Labels");
	getGraph(array(
		'graphData' => $MyData,
		'graphH' => 1280,
		'graphW' => 200,
		'captionText' => 'Активность на NFK Planet',
		'labelRotation' => 45,
		'fileName' => '../images/graph-month-year-players.png',
		'graphArea' => array(
			'x'=>35, 'y'=>16, 'h'=>-16, 'w'=>-40
		),
		'legend' => true,
	));
}

if ($cronPeriod == 'graph-month') {
	die();
	$statDays = 62;
	$res = $db->select("DATE(t.dateTime) AS dateTime, COUNT(*) as `count`
		FROM (
			SELECT * 
			FROM nfkLive_matchList m
			INNER JOIN nfkLive_matchData d USING(matchID)
			WHERE  DATE(m.dateTime) >= DATE_ADD(CURDATE(), INTERVAL -$statDays DAY)
			GROUP BY DAY(dateTime),d.playerID
		) AS t
		GROUP BY DATE(t.dateTime)","",""
	);
	$stats = array();
	foreach($res as $row) {
		$stats[$row['dateTime']] = $row['count'];
	}
	$players = array();
	$daysLabel = array();
	for ($i = $statDays; $i >= 0; $i--) {
		$time = strtotime("-$i days");
		$day = date('Y-m-d',$time);
		$players[] = ($stats[$day])?$stats[$day]:0;
		$daysLabel[] =  strftime('%m/%e%a',$time);//  iconv("windows-1251", "UTF-8",strftime('%m/%#d%a',$time)); %e
	}
	$MyData = new pData();   
	$MyData->addPoints($players,"Игроки"); 
	$MyData->addPoints($daysLabel,"Labels"); 
	$MyData->setSerieDescription("Labels","Дни"); 
	$MyData->setAbscissa("Labels"); 
	getGraph(array(
		'graphData' => $MyData,
		'graphH' => 1280,
		'graphW' => 200,
		'captionText' => 'Игроки на NFK Planet',
		'labelRotation' => 45,
		'graphArea' => array(
			'x'=>35, 'y'=>16, 'h'=>-16, 'w'=>-40
		)
	));
}
