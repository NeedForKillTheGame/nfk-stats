<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2009 ConnecT, 2011 coolant
// Module:	Home (server listing)
// Item:	
// Version:	0.1.8	14.07.2011
/////////////////////////////////////////////

if (!defined("NFK_LIVE")) die();

$template->load_template('mod_matchlist');

//
// Server List
//

$arr_gt = array("duel", "dm", "ctf", "tdm", "dom");
if (is_numeric($PARAMSTR[2])) {
	$playerID = $PARAMSTR[2];
	$CUR_ADDRES .= $PARAMSTR[2]."/";
	if (in_array($PARAMSTR[3],$arr_gt)) {
		$andGameType = "AND gameType = '$PARAMSTR[3]'";
		$CUR_ADDRES .= $PARAMSTR[3]."/";
		$gameType = $PARAMSTR[3];
	}
} else {
	if (in_array($PARAMSTR[2],$arr_gt)) {
		$whereGameType = "WHERE gameType = '$PARAMSTR[2]'";
		$CUR_ADDRES .= $PARAMSTR[2]."/";
		$gameType = $PARAMSTR[2];
	}
}


// Pages
$p_count = count($PARAMSTR);
$cur_page = ($PARAMSTR[$p_count-2] == "page") ? $PARAMSTR[$p_count-1] : 1;
if ( !is_numeric($cur_page) ) $cur_page = 1;

if ($playerID <> NULL) {
	$matchData = $db->prefix."_matchData";
	$matchList = $db->prefix."_matchList";
	$res = $db->select("","","SELECT SQL_CALC_FOUND_ROWS *
								FROM $matchData
								INNER JOIN $matchList ON $matchData.matchID = $matchList.matchID
								WHERE `playerID` ='$playerID' $andGameType ORDER BY `$matchData`.`matchID` DESC LIMIT ".(($cur_page - 1)*$CFG['items_per_page']).", ".($CFG['items_per_page']));
} else {
	$res = $db->select("SQL_CALC_FOUND_ROWS *","matchList","$whereGameType ORDER BY matchID DESC LIMIT ".(($cur_page - 1)*$CFG['items_per_page']).", ".($CFG['items_per_page']));
}
$total = $db->select("FOUND_ROWS() as 'rows'","","");
$total = $total[0]['rows'];
$pages_count = ceil($total / $CFG['items_per_page']);

// GTW: match 
foreach ($res as $row) {
	$MARKERS = Array
	(
		"SELF"					=> $PHP_SELF,
		"MATCH_ID"				=> $row['matchID'],
		"HOST_NAME"				=> $row['comments'],
		"HOST_NAME_AND_CMTS"	=> ($row['comments']==0) ? $row['hostName'] : $row['hostName']." (".$row['comments'].")",
		"MATCH_MAP"				=> $row['map'],
		"GAMETYPE"				=> GameType($row['gameType']),
		"GAMETYPE_SHORT"		=> $row['gameType'],
		"PLAYERS"				=> $row['players'],
		"MATCH_DATE_AGO"		=> ($CUR_LANG == 'ru') ? ago_rus(strtotime($row['dateTime'])) : ago_(strtotime($row['dateTime'])),
		"MATCH_DATE"			=> $row['dateTime'],
		"GAME_TIME"				=> floor($row['gameTime']/60).":".$row['gameTime'] % 60,
		"DEMO_LINK"				=> ($row['demo']<>"") ? "<a href='/demo/$row[matchID]'>".$dict->data['download']." ($row[dlnum])</a>" : "",
		"DEMO_DLS"				=> $row['dlnum'],
		"COMMENTS_NUM"			=> $row['comments'],
        'VIDEO_ICO'  => ($row['video']) ? '<img src="/images/video.gif" align="absBottom"> ' : null,
		"L_AGO"					=> $dict->data['ago'],
	);
	$template->assign_variables($MARKERS);
	$matchlist .= $template->build('match') or die("error building: matchlist\match");
}

if ($playerID <> NULL) {
	$page_title = "$plr1[name] - ".$dict->data['player_matches'];
	$page_name = $page_title;
} else {
	$page_title = $dict->data['matches'];
	$page_name = $page_title;
}

if ( $pages_count > 1 ) {
	require_once("./mods/inc/pages.inc.php");  
}

$dm = ($gameType == 'dm') ? "<b>DM</b>" : "DM";
$tdm = ($gameType == 'tdm') ? "<b>TDM</b>" : "TDM";
$ctf = ($gameType == 'ctf') ? "<b>CTF</b>" : "CTF";
$duel = ($gameType == 'duel') ? "<b>Duel</b>" : "Duel";
$dom = ($gameType == 'dom') ? "<b>DOM</b>" : "DOM";
$all = ($gameType == '') ? "<b>All</b>" : "All";

//
// Build Main
//
$MARKERS = Array
	(
		"G_MATCH_LIST"		=> $matchlist,
		"PAGES"				=> $pages,
		
		"M_DM"				=> $dm,
		"M_TDM"				=> $tdm,
		"M_CTF"				=> $ctf,
		"M_DUEL"			=> $duel,
		"M_DOM"				=> $dom,
		"M_ALL"				=> $all,
		
		"L_HOSTNAME"		=> $dict->data['host_name'],
		"L_MAP"				=> $dict->data['map'],
		"L_GAMETYPE"		=> $dict->data['game_type'],
		"L_GAMETIME"		=> $dict->data['game_time'],
		"L_PLAYERS"			=> $dict->data['players'],
		"L_DATE"			=> $dict->data['date'],
		"L_DEMO"			=> $dict->data['demo'],
	);
	
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: matchlist\main");

?>
