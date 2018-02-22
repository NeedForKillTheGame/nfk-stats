<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2011 coolant
// Item:	Nodes
// Version:	0.1.1	24.07.2011
/////////////////////////////////////////////

if (!defined("NFK_LIVE")) die();

if (is_numeric($PARAMSTR[2])) {$player1 = $PARAMSTR[2];} else {die('WARNING: External Player Error');}
if (is_numeric($PARAMSTR[3])) {$player2 = $PARAMSTR[3]; $twoPlayers = true;} else { $twoPlayers = false; }

$plr1 = $player->fetchId($player1) or header('Location: '.$PHP_SELF);
if ($twoPlayers == true) $plr2 = $player->fetchId($player2) or header('Location: '.$PHP_SELF);

$template->load_template('mod_duelslist');

$p_count = count($PARAMSTR);
$cur_page = ($PARAMSTR[$p_count-2] == "page") ? $PARAMSTR[$p_count-1] : 1;
if ( !is_numeric($cur_page) ) $cur_page = 1;

$matchData = $db->prefix."_matchData";
$matchList = $db->prefix."_matchList";
if ($twoPlayers) {

	$CUR_ADDRES .= $PARAMSTR[2]."/".$PARAMSTR[3]."/";


	$res = $db->select("","","SELECT SQL_CALC_FOUND_ROWS 
				map, demo, dlnum, dateTime, 
				playerID1, frags1, win1, dlist1.matchID, 
				playerID2, frags2, win2, dlist2.matchID
		FROM (
			SELECT 
				$matchList.matchID,
				$matchList.map, 
				$matchList.demo,
				$matchList.dlnum,
				$matchList.dateTime, 
				$matchData.playerID AS playerID1, 
				$matchData.frags as frags1, 
				$matchData.win AS win1
			FROM $matchList
			INNER JOIN $matchData ON $matchList.matchID = $matchData.matchID
			WHERE $matchData.playerID = $plr1[playerID] AND $matchList.gameType = 'DUEL'
			ORDER BY $matchList.matchID DESC
		) AS dlist1, (
			SELECT 
				$matchList.matchID,
				$matchData.playerID AS playerID2, 
				$matchData.frags as frags2, 
				$matchData.win AS win2
			FROM $matchList
			INNER JOIN $matchData ON $matchList.matchID = $matchData.matchID
			WHERE $matchData.playerID = $plr2[playerID] AND $matchList.gameType = 'DUEL'
			ORDER BY $matchList.matchID DESC
		) AS dlist2
		WHERE dlist1.matchID = dlist2.matchID
		LIMIT ".(($cur_page - 1)*$CFG['items_per_page']).", ".($CFG['items_per_page']));
	$total = $db->select("FOUND_ROWS() as rows","","");
	$total = $total[0]['rows'];
	$pages_count = ceil($total / $CFG['items_per_page']);

} else {

	$CUR_ADDRES .= $PARAMSTR[2]."/";

	$res = $db->select("","","
						SELECT  duelslist.matchID,  
							$matchData.frags AS frags2, 
							$matchData.playerID AS playerID2, 
							$matchData.win AS win2, 
							duelslist.map, 
							duelslist.demo,
							duelslist.dlnum,
							duelslist.dateTime,
							duelslist.frags1, 
							duelslist.playerID1, 
							duelslist.win1
						FROM (
							SELECT $matchList.matchID, 
								$matchList.map, 
								$matchList.demo,
								$matchList.dlnum,
								$matchList.dateTime, 
								$matchData.playerID AS playerID1, 
								$matchData.frags as frags1, 
								$matchData.win AS win1
							FROM $matchList
							INNER JOIN $matchData ON $matchList.matchID = $matchData.matchID
							WHERE $matchData.playerID = $plr1[playerID] AND $matchList.gameType = 'DUEL'
							ORDER BY $matchList.matchID DESC
							LIMIT ".(($cur_page - 1)*$CFG['items_per_page']).", ".($CFG['items_per_page'])."
							) AS duelslist
						INNER JOIN $matchData ON duelslist.matchID = $matchData.matchID
						WHERE $matchData.playerID <> $plr1[playerID]
						ORDER BY duelslist.matchID DESC");


	$calc_rows = $db->select("SQL_CALC_FOUND_ROWS $matchList.matchID","matchList",
			"INNER JOIN $matchData ON $matchList.matchID = $matchData.matchID
			WHERE $matchData.playerID = $plr1[playerID] AND $matchList.gameType = 'DUEL'");
	$total = $db->select("FOUND_ROWS() as rows","","");
	$total = $total[0]['rows'];
	$pages_count = ceil($total / $CFG['items_per_page']);
}
					
foreach ($res as $row) {
	$player1 = getPlayerName($row['playerID1'],false,false,false);
	$player2 = getPlayerName($row['playerID2'],false,false,false);
	$MARKERS = Array
	(
		"PLAYER1_NAME"				=> ($row['win1'] == 1) ? "<b>$player1</b>" : $player1,	
		"PLAYER2_NAME"				=> ($row['win2'] == 1) ? "<b>$player2</b>" : $player2,	
		"PLAYER1_ID"				=> $row['playerID1'],	
		"PLAYER2_ID"				=> $row['playerID2'],	
		"FRAGS"						=> ($matchID == $row['matchID']) ? "<b>[".$row['frags1'].":".$row['frags2']."]</b>" : "[".$row['frags1'].":".$row['frags2']."]",
		"MAP_NAME"					=> $row['map'],
		"MATCH_ID"					=> $row['matchID'],
		"DEMO_DLS"					=> $row['dlnum'],
		"DEMO_LINK"					=> ($row['demo']<>"") ? "<a href='/demo/$row[matchID]'>".$dict->data['download']." ($row[dlnum])</a>" : "",
		"MATCH_DATE"				=> $row['dateTime'],
	);
	$template->assign_variables($MARKERS);
	$duels_list .= $template->build('duel') or die("error building: duelslist\duel");
}

if ( $pages_count > 1 ) {
	require_once("./mods/inc/pages.inc.php");  
}

if ($twoPlayers) { 
	$page_title = clearName($plr1['name'])." vs ".clearName($plr2['name']);
	$page_name = getIcons($plr1)." vs ".getIcons($plr2);
} else {
	$page_title = clearName($plr1['name'])." - ".$dict->data['player_duels'];
	$page_name = $page_title;
}

//
// Build Main
//
$MARKERS = Array
	(
		"PAGES"				=> $pages,
		"G_DUELS_LIST"		=> $duels_list,
		"THEME_ROOT"		=> $CONFIG_root."themes/".$CFG['theme'],
	);
	
$template->assign_variables($MARKERS);

$content_data = $template->build('main') or die("error building: duelslist\main");
?>