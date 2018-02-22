<?php
if (!defined('NFK_LIVE')) die(); 

$template->load_template('mod_profile/profile_overall');

// GTW: if_show_ip
$template->assign_variables(Array(
	'GTW_LOGIC' => ($xdata['access'] >= 3) ? true : false,
	'PLAYER_IP' => $plr['lastIP'],
));
$show_ip = $template->build('if_show_ip') or die('error building: profile\if_show_ip');

// GTW: if_me_in_clan
$template->assign_variables(Array('GTW_LOGIC'=>($plr['clanID']) ? true : false));
$if_me_in_clan = $template->build('if_me_in_clan') or die('error building: profile\if_me_in_clan');

// GTW: if_my_profile
$template->assign_variables(Array(
	'GTW_LOGIC' => ($plr['userID'] == $xdata['id']) ? true : false,
	'CLAN_OPTIONS' => $if_me_in_clan,
));
$if_my_profile = $template->build('if_my_profile') or die('error building: profile\if_my_profile');

// GTW: if_in_clan
if ($plr['clanID']) {
	$clan = $db->select('clanName','clanList',"WHERE clanID=$plr[clanID]");
	$clan = $clan[0];
	$MARKERS_IF = Array(
		'GTW_LOGIC' => true,
		'CLAN_ID' => $plr['clanID'],
		'CLAN_NAME' => $clan['clanName'],
	);
} else $MARKERS_IF = Array('GTW_LOGIC'=> false);
$template->assign_variables($MARKERS_IF);
$if_in_clan = $template->build('if_in_clan') or die('error building: profile\if_in_clan');
// Stats Table >
// DM Stats
$ladderTable = $db->prefix.'_ladderDM';
$statsDM = $db->select('*, AltStat_Players.DmReiting as score',"$ladderTable",
					"INNER JOIN AltStat_Players ON AltStat_Players.PlayerId = $ladderTable.playerID
					 WHERE $ladderTable.playerID = $plr[playerID]",false);
$statsDM = $statsDM[0];
if ($statsDM['games']) {
	$res = $db->call("sp_getLadderPlace($statsDM[score],'DM')");
	$statsDM['place'] = $res[0]['place']+1;
} else $statsDM['place']='-';
if ($statsDM['lastGame']) {
	$dm_ago = timeAgo(strtotime($statsDM['lastGame']),$CUR_LANG).' '.$dict->data['ago'];
} else $dm_ago = $dict->data['n/a'];

// TDM Stats
$ladderTable = $db->prefix.'_ladderTDM';
$statsTDM = $db->select('*, AltStat_Players.TdmReiting as score',"$ladderTable",
					"INNER JOIN AltStat_Players ON AltStat_Players.PlayerId = $ladderTable.playerID
					 WHERE $ladderTable.playerID = $plr[playerID]",false);
$statsTDM = $statsTDM[0];
if ($statsTDM['games']) {
	$res = $db->call("sp_getLadderPlace($statsTDM[score],'TDM')");
	$statsTDM['place'] = $res[0]['place']+1;
} else $statsTDM['place']='-';
if ($statsTDM['lastGame']) {
	$tdm_ago = timeAgo(strtotime($statsTDM['lastGame']),$CUR_LANG).' '.$dict->data['ago'];
} else $tdm_ago = $dict->data['n/a'];

// CTF Stats
$ladderTable = $db->prefix.'_ladderCTF';
$statsCTF = $db->select('*, AltStat_Players.CtfReiting as score',"$ladderTable",
					"INNER JOIN AltStat_Players ON AltStat_Players.PlayerId = $ladderTable.playerID
					 WHERE $ladderTable.playerID = $plr[playerID]",false);
$statsCTF = $statsCTF[0];
if ($statsCTF['games']) {
	$res = $db->call("sp_getLadderPlace($statsCTF[score],'CTF')");
	$statsCTF['place'] = $res[0]['place']+1;
} else $statsCTF['place']='-';
if ($statsCTF['lastGame']) {
	$ctf_ago = timeAgo(strtotime($statsCTF['lastGame']),$CUR_LANG).' '.$dict->data['ago'];
} else $ctf_ago = $dict->data['n/a'];

// DOM Stats
/*$ladderTable = $db->prefix.'_ladderDOM';
$statsDOM = $db->select('*, AltStat_Players.DomReiting as score',"$ladderTable",
					"INNER JOIN AltStat_Players ON AltStat_Players.PlayerId = $ladderTable.playerID
					 WHERE $ladderTable.playerID = $plr[playerID]",false);
$statsDOM = $statsDOM[0];
if ($statsDOM['games']) {
	$res = $db->call("sp_getLadderPlace($statsDOM[score],'DOM')");
	$statsDOM['place'] = $res[0]['place']+1;
} else $statsDOM['place']='-';
if ($statsDOM['lastGame']) {
	$dom_ago = timeAgo(strtotime($statsDOM['lastGame']),$CUR_LANG).' '.$dict->data['ago'];
} else $dom_ago = $dict->data['n/a'];*/

// DUEL Stats
$ladderTable = $db->prefix.'_ladderDUEL';
$statsDUEL = $db->select('*',"$ladderTable","WHERE $ladderTable.playerID = $plr[playerID]",false);
$statsDUEL = $statsDUEL[0];
if ($statsDUEL['games']) {
	$res = $db->call("sp_getLadderPlace($statsDUEL[score],'DUEL')");
	$statsDUEL['place'] = $res[0]['place']+1;
} else $statsDUEL['place']='-';
if ($statsDUEL['lastGame']) {
	$duel_ago = timeAgo(strtotime($statsDUEL['lastGame']),$CUR_LANG).' '.$dict->data['ago'];
} else $duel_ago = $dict->data['n/a'];
// Stats Table <

$trow = 'frags'; 
$statsTOTAL[$trow] = $statsDM[$trow]+$statsTDM[$trow]+$statsDUEL[$trow]+$statsCTF[$trow];
$trow = 'deaths'; 
$statsTOTAL[$trow] = $statsDM[$trow]+$statsTDM[$trow]+$statsDUEL[$trow]+$statsCTF[$trow];
$trow = 'score'; 
$statsTOTAL[$trow] = $statsDUEL[$trow]+$statsDM[$trow]+$statsCTF[$trow]+$statsTDM[$trow];
 //$statsDM[$trow]+$statsTDM[$trow]+$statsDUEL[$trow]+$statsCTF[$trow];
$trow = 'wins'; 
$statsTOTAL[$trow] = $statsDM[$trow]+$statsTDM[$trow]+$statsDUEL[$trow]+$statsCTF[$trow];
$trow = 'losses'; 
$statsTOTAL[$trow] = $statsDM[$trow]+$statsTDM[$trow]+$statsDUEL[$trow]+$statsCTF[$trow];
$trow = 'games'; 
$statsTOTAL[$trow] = $statsDM[$trow]+$statsTDM[$trow]+$statsDUEL[$trow]+$statsCTF[$trow];
$trow = 'time'; 
$statsTOTAL[$trow] = $statsDM[$trow]+$statsTDM[$trow]+$statsDUEL[$trow]+$statsCTF[$trow];

$res = $db->select('*','playerStats',"WHERE playerID=$plr[playerID]");
$statsALL = $res[0];

$fwpn['gaun'] 	= $statsALL['gaun_hits']/0.04; 
$fwpn['mach'] 	= $statsALL['mach_fire']/0.2;
$fwpn['shot'] 	= $statsALL['shot_fire']/0.02; 
$fwpn['gren'] 	= $statsALL['gren_fire']/0.022;
$fwpn['rocket'] = $statsALL['rocket_fire']/0.025; 
$fwpn['shaft'] 	= $statsALL['shaft_fire']/10;
$fwpn['plasma'] = $statsALL['plasma_fire']/0.2; 
$fwpn['rail'] 	= $statsALL['rail_fire']/0.011; 
$weapsum = array_sum($fwpn); 
arsort($fwpn,SORT_NUMERIC);
foreach ($fwpn as $key => $value) {
	$i++;
	$fawwpn[$i] = $key;
	if ($i>=3) break; 
}

$matchData = $db->prefix.'_matchData';
$matchList = $db->prefix.'_matchList';
$res = $db->select('','',"
					SELECT duelslist.matchID,  
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
						LIMIT 5
						) AS duelslist
					INNER JOIN $matchData ON duelslist.matchID = $matchData.matchID
					WHERE $matchData.playerID <> $plr[playerID]
					ORDER BY duelslist.matchID DESC");
	$player1 = getIcons($plr,false,false,false);				
foreach ($res as $row) {
	$player2 = getPlayerName($row['playerID2'],true,true);
	$MARKERS = Array
	(
		'PLAYER1_NAME'				=> ($row['win1'] == 1) ? "<b>$player1</b>" : $player1,	
		'PLAYER2_NAME'				=> ($row['win2'] == 1) ? "<b>$player2</b>" : $player2,	
		'PLAYER2_ID'				=> $row['playerID2'],	
		'FRAGS'						=> ($matchID == $row['matchID']) ? '<b>['.$row['frags1'].':'.$row['frags2'].']</b>' : '['.$row['frags1'].':'.$row['frags2'].']',
		'MAP_NAME'					=> $row['map'],
		'MATCH_ID'					=> $row['matchID'],
		'DEMO_DLS'					=> $row['dlnum'],
		'DEMO_LINK'					=> ($row['demo']<>'') ? "<a href='/demo/$row[matchID]'>".$dict->data['download']." ($row[dlnum])</a>" : '',
		'MATCH_DATE'				=> $row['dateTime'],
	);
	$template->assign_variables($MARKERS);
	$last5_duels .= $template->build('last_duel') or die('error building: profile\last_duel');
}

$page_title = "$plr[name] - ".$dict->data['player_profile'];

// Build Main
$MARKERS = Array (		
	'DM_PLACE' => getPlaceIco($statsDM['place']),
	'DM_SCORE' => $statsDM['score'],
	'DM_WINS' => $statsDM['wins'],
	'DM_LOSSES' => $statsDM['losses'],
	'DM_WIN_RATE' => ($statsDM['games'] != 0) ? (round($statsDM['wins']*100/$statsDM['games']) ): (0),
	'DM_FRAGS' => $statsDM['frags'],
	'DM_DEATHS' => $statsDM['deaths'],
	'DM_FRAG_RATE' => ($statsDM['deaths'] != 0) ? (round($statsDM['frags'] / $statsDM['deaths'],2)) : (0),
	'DM_GAMES' => $statsDM['games'],
	'DM_AGO' => $dm_ago,
	'DM_PLAYED' => $statsDM['time'],

	'TDM_PLACE' => getPlaceIco($statsTDM['place']),
	'TDM_SCORE' => $statsTDM['score'],
	'TDM_WINS' => $statsTDM['wins'],
	'TDM_LOSSES' => $statsTDM['losses'],
	'TDM_WIN_RATE' => ($statsTDM['games'] != 0) ? (round($statsTDM['wins']*100/$statsTDM['games']) ): (0),
	'TDM_FRAGS' => $statsTDM['frags'],
	'TDM_DEATHS' => $statsTDM['deaths'],
	'TDM_FRAG_RATE' => ($statsTDM['deaths'] != 0) ? (round($statsTDM['frags'] / $statsTDM['deaths'],2)) : (0),
	'TDM_GAMES' => $statsTDM['games'],
	'TDM_AGO' => $tdm_ago,
	'TDM_PLAYED' => $statsTDM['time'],

	'CTF_PLACE' => getPlaceIco($statsCTF['place']),
	'CTF_SCORE' => $statsCTF['score'],
	'CTF_WINS' => $statsCTF['wins'],
	'CTF_LOSSES' => $statsCTF['losses'],
	'CTF_WIN_RATE' => ($statsCTF['games'] != 0) ? (round($statsCTF['wins']*100/$statsCTF['games']) ): (0),
	'CTF_FRAGS' => $statsCTF['frags'],
	'CTF_DEATHS' => $statsCTF['deaths'],
	'CTF_FRAG_RATE' => ($statsCTF['deaths'] != 0) ? (round($statsCTF['frags'] / $statsCTF['deaths'],2)) : (0),
	'CTF_GAMES' => $statsCTF['games'],
	'CTF_AGO' => $ctf_ago,
	'CTF_PLAYED' => $statsCTF['time'],
/*
	'DOM_PLACE' => $statsDOM['place'],
	'DOM_SCORE' => $statsDOM['score'],
	'DOM_WINS' => $statsDOM['wins'],
	'DOM_LOSSES' => $statsDOM['losses'],
	'DOM_WIN_RATE' => ($statsDOM['games'] != 0) ? (round($statsDOM['wins']*100/$statsDOM['games']) ): (0),
	'DOM_FRAGS' => $statsDOM['frags'],
	'DOM_DEATHS' => $statsDOM['deaths'],
	'DOM_FRAG_RATE' => ($statsDOM['deaths'] != 0) ? (round($statsDOM['frags'] / $statsDOM['deaths'],2)) : (0),
	'DOM_GAMES' => $statsDOM['games'],
	'DOM_AGO' => $dom_ago,
	'DOM_PLAYED' => $statsDOM['time'],
*/
	'DUEL_PLACE' => getPlaceIco($statsDUEL['place']),
	'DUEL_SCORE' => $statsDUEL['score'],
	'DUEL_WINS' => $statsDUEL['wins'],
	'DUEL_LOSSES' => $statsDUEL['losses'],
	'DUEL_WIN_RATE' => ($statsDUEL['games'] != 0) ? (round($statsDUEL['wins']*100/$statsDUEL['games']) ): (0),
	'DUEL_FRAGS' => $statsDUEL['frags'],
	'DUEL_DEATHS' => $statsDUEL['deaths'],
	'DUEL_FRAG_RATE' => ($statsDUEL['deaths'] != 0) ? (round($statsDUEL['frags'] / $statsDUEL['deaths'],2)) : (0),
	'DUEL_GAMES' => $statsDUEL['games'],
	'DUEL_AGO' => $duel_ago,
	'DUEL_PLAYED' => $statsDUEL['time'],
	'DUEL_RANK' => $statsDUEL['rank'],

	'TOTAL_SCORE' => $statsTOTAL['score'],
	'TOTAL_WINS' => $statsTOTAL['wins'],
	'TOTAL_LOSSES' => $statsTOTAL['losses'],
	'TOTAL_WIN_RATE' => ($statsTOTAL['games'] != 0) ? (round($statsTOTAL['wins']*100/$statsTOTAL['games']) ): (0),
	'TOTAL_FRAGS' => $statsTOTAL['frags'],
	'TOTAL_DEATHS' => $statsTOTAL['deaths'],
	'TOTAL_FRAG_RATE' => ($statsTOTAL['deaths'] != 0) ? (round($statsTOTAL['frags'] / $statsTOTAL['deaths'],2)) : (0),
	'TOTAL_GAMES' => $statsTOTAL['games'],
	'TOTAL_AGO' => $statsTOTAL['lastGames'],
	'TOTAL_PLAYED' => sec2HourDays($statsTOTAL['time']),

	'ALL_WINS' => $statsALL['wins'],
	'ALL_LOSSES' => $statsALL['losses'],
	'ALL_WIN_RATE' => ($statsALL['games'] != 0) ? (round($statsALL['wins']*100/($statsALL['games']/*+$statsALL['losses']*/)) ): (0),
	'ALL_FRAGS' => $statsALL['frags'],
	'ALL_DEATHS' => $statsALL['deaths'],
	'ALL_FRAG_RATE' => ($statsALL['deaths'] != 0) ? (round($statsALL['frags'] / $statsALL['deaths'],2)) : (0),
	'ALL_GAMES' => $statsALL['games'],

	'EXCEL_NUM' => $statsALL['excellents'],
	'HUMIL_NUM' => $statsALL['humiliations'],
	'IMPRES_NUM' => $statsALL['impressives'],
	'AWARDS_NUM' => $statsALL['excellents']+$statsALL['humiliations']+$statsALL['impressives'],

	'GAUN_HITS' => $statsALL['gaun_hits'],
	'GAUN_KILLS' => $statsALL['gaun_kills'],
	'GAUN_USE' => ($weapsum != 0) ? round(($fwpn['gaun']*100)/$weapsum,1) : (0),

	'MACH_HITS' => $statsALL['mach_hits'],
	'MACH_FIRE' => $statsALL['mach_fire'],
	'MACH_KILLS' => $statsALL['mach_kills'],
	'MACH_ACC' => ($statsALL['mach_fire'] != 0) ? ( round($statsALL['mach_hits'] * 100 / $statsALL['mach_fire']) ) : (0),
	'MACH_USE' => ($weapsum != 0) ? round(($fwpn['mach']*100)/$weapsum): (0),

	'SHOT_HITS' => $statsALL['shot_hits'],
	'SHOT_FIRE' => $statsALL['shot_fire'],
	'SHOT_KILLS' => $statsALL['shot_kills'],
	'SHOT_ACC' => ($statsALL['shot_fire'] != 0) ? ( round($statsALL['shot_hits'] * 100 / $statsALL['shot_fire']) ) : (0),
	'SHOT_USE' => ($weapsum != 0) ? round(($fwpn['shot']*100)/$weapsum) : (0),

	'GREN_HITS' => $statsALL['gren_hits'],
	'GREN_FIRE' => $statsALL['gren_fire'],
	'GREN_KILLS' => $statsALL['gren_kills'],
	'GREN_ACC' => ($statsALL['gren_fire'] != 0) ? ( round($statsALL['gren_hits'] * 100 / $statsALL['gren_fire']) ) : (0),
	'GREN_USE' => ($weapsum != 0) ? round(($fwpn['gren']*100)/$weapsum) : (0),

	'ROCKET_HITS' => $statsALL['rocket_hits'],
	'ROCKET_FIRE' => $statsALL['rocket_fire'],
	'ROCKET_KILLS' => $statsALL['rocket_kills'],
	'ROCKET_ACC' => ($statsALL['rocket_fire'] != 0) ? ( round($statsALL['rocket_hits'] * 100 / $statsALL['rocket_fire']) ) : (0),
	'ROCKET_USE' => ($weapsum != 0) ? round(($fwpn['rocket']*100)/$weapsum) : (0),

	'SHAFT_HITS' => $statsALL['shaft_hits'],
	'SHAFT_FIRE' => $statsALL['shaft_fire'],
	'SHAFT_KILLS' => $statsALL['shaft_kills'],
	'SHAFT_ACC' => ($statsALL['shaft_fire'] != 0) ? ( round($statsALL['shaft_hits'] * 100 / $statsALL['shaft_fire']) ) : (0),
	'SHAFT_USE' => ($weapsum != 0) ? round(($fwpn['shaft']*100)/$weapsum,1) : (0),

	'PLASMA_HITS' => $statsALL['plasma_hits'],
	'PLASMA_FIRE' => $statsALL['plasma_fire'],
	'PLASMA_KILLS' => $statsALL['plasma_kills'],
	'PLASMA_ACC' => ($statsALL['plasma_fire'] != 0) ? ( round($statsALL['plasma_hits'] * 100 / $statsALL['plasma_fire']) ) : (0),
	'PLASMA_USE' => ($weapsum != 0) ? round(($fwpn['plasma']*100)/$weapsum) : (0),

	'RAIL_HITS' => $statsALL['rail_hits'],
	'RAIL_FIRE' => $statsALL['rail_fire'],
	'RAIL_KILLS' => $statsALL['rail_kills'],
	'RAIL_ACC' => ($statsALL['rail_fire'] != 0) ? ( round($statsALL['rail_hits'] * 100 / $statsALL['rail_fire']) ) : (0),
	'RAIL_USE' => ($weapsum != 0) ? round(($fwpn['rail']*100)/$weapsum) : (0),

	'BFG_HITS' => $statsALL['bfg_hits'],
	'BFG_FIRE' => $statsALL['bfg_fire'],
	'BFG_KILLS' => $statsALL['bfg_kills'],
	'BFG_ACC' => ($statsALL['bfg_fire'] != 0) ? ( round($statsALL['bfg_hits'] * 100 / $statsALL['bfg_fire']) ) : (0),
	'BFG_USE' => 0,

	'HITS' => $statsALL['hits'],
	'SHOTS' => $statsALL['shots'],
	'ACC' => ($statsALL['shots'] != 0) ? (round($statsALL['hits']*100/$statsALL['shots']) ): (0),

	'FAV_WPN1' => $fawwpn[1],
	'FAV_WPN2' => $fawwpn[2],
	'FAV_WPN3' => $fawwpn[3],

	'G_USER_OPTIONS' => $if_my_profile,
	'G_LAST5_DUELS' => $last5_duels,
	'G_SHOW_IP' => $show_ip,
	'IF_IN_CLAN' => $if_in_clan,

	'PLAYER_NAME' => htmlspecialchars($statsALL['name']),
	'PLAYER_ID' => $statsALL['playerID'],

	'L_USER_OPTIONS' 		=> $dict->data['user_options'],
	'L_ADDRES'				=> $dict->data['addres'],
	'L_CHANGE_NICK_NAME'	=> $dict->data['change_nick_name'],
	'L_CHANGE'				=> $dict->data['change'],
	'L_DONT_FORGET_CHANGE'	=> $dict->data['dont_forget_change'],
	'L_LEAVE_CLAN'			=> $dict->data['leave_clan'],
	'L_PLAYER_STATISTICS'	=> $dict->data['player_statistics'],
	'L_SEASON'				=> $dict->data['season'],
	'L_LOADING'				=> $dict->data['loading'],
	'L_RANK'				=> $dict->data['rank'],
	'L_WINS'				=> $dict->data['wins'],
	'L_LOSSES'				=> $dict->data['losses'],
	'L_LOSSES_C'			=> $dict->data['losses_c'],
	'L_WIN_RATE'			=> $dict->data['win_rate'],
	'L_WIN_RATE_C'			=> $dict->data['win_rate_ñ'],
	'L_GAMES'				=> $dict->data['games'],
	'L_LASTGAME'			=> $dict->data['last_game'],
	'L_TIME_PLAYED'			=> $dict->data['time_played'],
	'L_ALL_STATS'			=> $dict->data['all_stats'],
	'L_SHOTS'				=> $dict->data['shots'],
	'L_HITS'				=> $dict->data['hits'],
	'L_ACCURACY'			=> $dict->data['accuracy'],
	'L_USE'					=> $dict->data['weap_use'],
	'L_FAV_WEAPONS'			=> $dict->data['fav_weapons'],
	'L_LAST5_DUELS'			=> $dict->data['last5_duels'],
	'L_MAP'					=> $dict->data['map'],
	'L_GAMETYPE'			=> $dict->data['game_type'],
	'L_GAMETIME'			=> $dict->data['game_time'],
	'L_DATE'				=> $dict->data['date'],
	'L_DOWNLOAD_DEMO'		=> $dict->data['download_demo'],
	'L_AGO'					=> $dict->data['ago'],	
	'L_FRAGS'				=> $dict->data['frags'],
	'L_DEATHS'				=> $dict->data['deaths'],
	'L_FRAG_RATE'			=> $dict->data['frag_rate'],
	'L_RESULT'				=> $dict->data['result'],
	'L_NAME'				=> $dict->data['name'],
	'L_SCORE'				=> $dict->data['score'],
	'L_TIME'				=> $dict->data['time'],
	'L_ACCURACY'			=> $dict->data['accuracy'],
	'L_WEAP_USE'			=> $dict->data['weap_use'],
	'L_TOTAL'				=> $dict->data['total'],
	'L_WEAP_STATS'			=> $dict->data['weap_stats'],
	'L_FULL_DUEL_LIST'		=> $dict->data['full_duel_list'],
	'L_PLACE'				=> $dict->data['place'],
	'L_PLAYED'				=> $dict->data['played'],
	'L_CLAN'				=> $dict->data['clan'],
	'L_CHANGE_PASSWORD'		=> $dict->data['change_pass'],
	'L_POINTS'				=> $dict->data['points'],
);
$template->assign_variables($MARKERS);
$TMPL_overall .= $template->build('overall') or die('error building: profile\overall');
?>