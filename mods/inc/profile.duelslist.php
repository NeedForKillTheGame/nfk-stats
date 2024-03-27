<?php
if (!defined("NFK_LIVE")) die(); 

$template->load_template('mod_profile/profile_duelslist');

$CUR_ADDRES .= $PARAMSTR[3]."/";

$matchList = $db->prefix."_matchList";
$matchData = $db->prefix."_matchData";

$p_count = count($PARAMSTR);
$cur_page = ($PARAMSTR[$p_count-2] == "page") ? $PARAMSTR[$p_count-1] : 1;
if ( !is_numeric($cur_page) ) $cur_page = 1;

$duelslist = $db->select("","","
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
						WHERE $matchData.playerID = $plr[playerID] AND $matchList.gameType = 'DUEL'
						ORDER BY $matchList.matchID DESC
						LIMIT ".(($cur_page - 1)*$CFG['items_per_page']).", ".($CFG['items_per_page'])."
						) AS duelslist
					INNER JOIN $matchData ON duelslist.matchID = $matchData.matchID
					WHERE $matchData.playerID <> $plr[playerID]
					ORDER BY duelslist.matchID DESC");
$calc_rows = $db->select("SQL_CALC_FOUND_ROWS $matchList.matchID","matchList",
		"INNER JOIN $matchData ON $matchList.matchID = $matchData.matchID
		WHERE $matchData.playerID = $plr[playerID] AND $matchList.gameType = 'DUEL'");
		
$total = $db->select("FOUND_ROWS() as 'rows'","","");
$total = $total[0]['rows'];
$pages_count = ceil($total / $CFG['items_per_page']);
		
// GTW: duel
	$player1 = getIcons($plr,false,false,false);	
foreach ($duelslist as $row) {
	$player2 = getPlayerName($row['playerID2'],true,true);
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
		"MATCH_DATE"				=> $row['dateTime'],
		"DEMO_LINK"					=> ($row['demo']<>"") ? "<a href='/demo/$row[matchID]'>".$dict->data['download']." ($row[dlnum])</a>" : "",
	);
	$template->assign_variables($MARKERS);
	$duels_list .= $template->build('duel') or die("error building: profile\duel");
}

if ( $pages_count > 1 ) {
	require_once("./mods/inc/pages.inc.php");  
}

$page_title = "$plr[name] - ".$dict->data['player_duels'];

//
// Build Main
//
$MARKERS = Array
	(
		"G_DUELS_LIST"		=> $duels_list,
		"PAGES"				=> $pages,
	);
	
$template->assign_variables($MARKERS);
$TMPL_duelslist .= $template->build('duelslist') or die("error building: profile\duelslist");

?>
