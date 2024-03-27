<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2011 coolant
// Module:	Ladder
// Item:	
// Version:	0.1.0	18.07.2011
/////////////////////////////////////////////

if (!defined("NFK_LIVE")) die();

$ladderPage = $PARAMSTR[2];

$template->load_template('mod_ladder');

$arr_gt = array("duel", "dm", "ctf", "tdm", "dom", "all");
if (in_array($PARAMSTR[2],$arr_gt)) {
	$ladderTbl = $db->prefix."_ladder".strToUpper($PARAMSTR[2]);
	$altStats = "AltStat_Players.".ucfirst($PARAMSTR[2])."Reiting";
	$gType = strToUpper($PARAMSTR[2]);
	$CUR_ADDRES .= $PARAMSTR[2]."/";
} else {
	$ladderTbl = $db->prefix."_ladderDM";
	$altStats = "AltStat_Players.DmReiting";
	$gType = "DM";
	$CUR_ADDRES .= "dm/";
}
$p_count = count($PARAMSTR);
$cur_page = ($PARAMSTR[$p_count-2] == "page") ? $PARAMSTR[$p_count-1] : 1;
if ( !is_numeric($cur_page) ) $cur_page = 1;

if ($PARAMSTR[2]=="duel") {
$res = $db->select("SQL_CALC_FOUND_ROWS *","ladderDUEL","WHERE `games`<>0 AND score <> -1 ORDER BY score DESC, frags DESC 
					LIMIT ".(($cur_page - 1)*$CFG['items_per_page']).", ".($CFG['items_per_page']));
} else {
	if ($PARAMSTR[2]=="all") {
		$res = $db->select("","",
							"SELECT SQL_CALC_FOUND_ROWS name,".$db->prefix."_playerStats.playerID, frags, wins, losses, AltStat_Players.AllRating as score
							FROM ".$db->prefix."_playerStats
							INNER JOIN AltStat_Players ON AltStat_Players.Playerid = ".$db->prefix."_playerStats.playerID
							ORDER BY score DESC LIMIT ".(($cur_page - 1)*$CFG['items_per_page']).", ".($CFG['items_per_page']));
	} else {
		$res = $db->select("SQL_CALC_FOUND_ROWS $ladderTbl.playerID, frags, deaths, games, wins, losses, lastGame, $altStats as score","",
					"FROM $ladderTbl INNER JOIN AltStat_Players ON AltStat_Players.Playerid = $ladderTbl.playerID
					WHERE `games` <> 0 ORDER BY score DESC LIMIT ".(($cur_page - 1)*$CFG['items_per_page']).", ".($CFG['items_per_page']));
	}
}


					
$total = $db->select("FOUND_ROWS() as 'rows'","","");
$total = $total[0]['rows'];
$pages_count = ceil($total / $CFG['items_per_page']);

$place = (($cur_page - 1)*$CFG['items_per_page']);
foreach ($res as $row) {
	$MARKERS = Array
		(
			"PLAYER_ID"			=> $row['playerID'],
			"PLAYER_NAME"		=> getPlayerName($row['playerID']),
			
			"FRAGS"				=> $row['frags'],
			"DEATHS"			=> $row['deaths'],
			
			"GAMES"				=> $row['games'],
			"WINS"				=> $row['wins'],
			"LOSSES"			=> $row['losses'],
			"WIN_RATE"			=> ($row['wins'] != 0) ? (round($row['wins']*100/($row['wins']+$row['losses'])) ): ("0"),

			"SCORE"				=> $row['score'],
			"DUEL_RANK"			=> ($gType == "DUEL") ? "<img src='/$CFG[root]themes/$CFG[theme]/images/ranks/d$row[rank].jpg'> " : "",
			
			"PLACE"				=> ++$place,
			
			"LAST_GAME"			=> $row['lastGame'],
			"LAST_GAME_AGO"		=> ago_(strtotime($row['lastGame'])),
			"PLAYED_TIME"		=> $row['time'],
		);
	$template->assign_variables($MARKERS);
	$ladder .= $template->build('player') or die("error building: ladder\player");
}


$page_title = $dict->data['ladder'].": ".$gType;
$page_name = $page_title;


if ( $pages_count > 1 ) {
	require_once("./mods/inc/pages.inc.php");  
}

//
// Build Main
//
$MARKERS = Array
	(
		"G_LADDER"			=> $ladder,

		"PAGES"				=> $pages,
		
		"L_WINS"			=> $dict->data['wins'],
		"L_LOSSES"			=> $dict->data['losses'],
		"L_WIN_RATE"		=> $dict->data['win_rate'],
		"L_GAMES"			=> $dict->data['games'],
		"L_FRAGS"			=> $dict->data['frags'],
		"L_DEATHS"			=> $dict->data['deaths'],
		"L_NAME"			=> $dict->data['name'],
		"L_SCORE"			=> $dict->data['score'],
	);
	
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: ladder\main");

?>
