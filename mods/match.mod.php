<?php
if (!defined("NFK_LIVE")) die();

$template->load_template('mod_match');
$template2 = new skin();

$matchID = (($PARAMSTR[2] <> '') and (is_numeric($PARAMSTR[2]))) ? ($PARAMSTR[2]) : ('1');

//
// Score board
//
$match = $db->select("*","matchList","WHERE matchID=$matchID");
$match = $match[0];

if (is_teamGame($match['gameType'])) {
		$template2->load_template('mod_match/match.dm_duel');
	} else $template2->load_template('mod_match/match.team_game');
		
// такая фигня из-за АльтСстатов..
if ($match['gameType'] <> 'DUEL') {
	$AsScore = ", AltStat_GameRes.Result as score";
	$InnerJoin = "INNER JOIN `AltStat_GameRes` ON `".$db->prefix."_matchData`.`matchID` = AltStat_GameRes.MatchId";
	$WinOrder = "`win` DESC,";
	$AndPlayerID ="AND AltStat_GameRes.Playerid = nfkLive_matchData.PlayerID";
}
$plrnum = 1;

$players = $db->select("* $AsScore","matchData","$InnerJoin WHERE ".$db->prefix."_matchData.matchID=$matchID $AndPlayerID ORDER BY $WinOrder `frags` DESC, `deaths`");
// GTW: player_stats
// GTW: weapon_stats
foreach ($players as $plr) {
	$playerName = getPlayerName($plr['playerID']);
	
	$MARKERS = Array
		(
			"PLAYER_ID"			=> $plr['playerID'],
			"PLAYER_NAME"		=> $playerName,
			
			"FRAGS"				=> $plr['frags'],
			"DEATHS"			=> $plr['deaths'],
			"SUIS"				=> $plr['suisides'],
			"FRAG_RATE"			=> ($plr['deaths'] != 0) ? (round($plr['frags'] / $plr['deaths'],2)) : (0),
			
			"PLAYER_ICO"		=> "",
			
			"TEAM"				=> $plr['team'],
			"TEAM_LOWER"		=> StrToLower($plr['team']),
			"RESULT"			=> $dict->data[getMatchResult($plr['win'])],
			"SCORE"				=> getSign($plr['score']),
			"PING"				=> $plr['ping'],
			"PLAYED_TIME"		=> floor($plr['time']/60).":".$plr['time'] % 60,
			"PLAYER_IP"			=> $plr['IP'],
			"DMG_GIVEN"			=> $plr['dmggiven'],
			"DMG_RECIVED"		=> $plr['dmgrecvd'],
			"A_IMPRS"			=> $plr['impressives'],
			"A_EXELL"			=> $plr['excellents'],
			"A_HUMIL"			=> $plr['humiliations'],
			
		);	

	$fwpn['gaun'] 	= $plr['gaun_hits']/0.04; 
	$fwpn['mach'] 	= $plr['mach_fire']/0.2;
	$fwpn['shot'] 	= $plr['shot_fire']/0.02; 
	$fwpn['gren'] 	= $plr['gren_fire']/0.022;
	$fwpn['rocket'] = $plr['rocket_fire']/0.025; 
	$fwpn['shaft'] 	= $plr['shaft_fire']/10;
	$fwpn['plasma'] = $plr['plasma_fire']/0.2; 
	$fwpn['rail'] 	= $plr['rail_fire']/0.011; 
	$weapsum = array_sum($fwpn); 
	
	// NEED FIX IT!
	$plr['hits']= $plr['mach_hits']+$plr['shot_hits']+$plr['gren_hits']+$plr['rocket_hits']+$plr['shaft_hits']+$plr['rail_hits']+$plr['plasma_hits'];
	$plr['shots'] = $plr['mach_fire']+$plr['shot_fire']+$plr['gren_fire']+$plr['rocket_fire']+$plr['shaft_fire']+$plr['rail_fire']+$plr['plasma_fire'];
	//
	$MARKERS_WEAP = Array
		(
			"PLAYER_NAME"		=> $playerName,
		
			"GAUN_HITS"			=> $plr['gaun_hits'],
			"GAUN_KILLS"		=> $plr['gaun_kills'],
			"GAUN_USE"			=> ($weapsum != 0) ? round(($fwpn['gaun']*100)/$weapsum,1) : (0),
			
			"MACH_HITS"			=> $plr['mach_hits'],
			"MACH_FIRE"			=> $plr['mach_fire'],
			"MACH_KILLS"		=> $plr['mach_kills'],
			"MACH_ACC"			=> ($plr['mach_fire'] != 0) ? ( round($plr['mach_hits'] * 100 / $plr['mach_fire']) ) : (0),
			"MACH_USE"			=> ($weapsum != 0) ? round(($fwpn['mach']*100)/$weapsum): (0),
			
			"SHOT_HITS"			=> $plr['shot_hits'],
			"SHOT_FIRE"			=> $plr['shot_fire'],
			"SHOT_KILLS"		=> $plr['shot_kills'],
			"SHOT_ACC"			=> ($plr['shot_fire'] != 0) ? ( round($plr['shot_hits'] * 100 / $plr['shot_fire']) ) : (0),
			"SHOT_USE"			=> ($weapsum != 0) ? round(($fwpn['shot']*100)/$weapsum) : (0),
			
			"GREN_HITS"			=> $plr['gren_hits'],
			"GREN_FIRE"			=> $plr['gren_fire'],
			"GREN_KILLS"		=> $plr['gren_kills'],
			"GREN_ACC"			=> ($plr['gren_fire'] != 0) ? ( round($plr['gren_hits'] * 100 / $plr['gren_fire']) ) : (0),
			"GREN_USE"			=> ($weapsum != 0) ? round(($fwpn['gren']*100)/$weapsum) : (0),
			
			"ROCKET_HITS"		=> $plr['rocket_hits'],
			"ROCKET_FIRE"		=> $plr['rocket_fire'],
			"ROCKET_KILLS"		=> $plr['rocket_kills'],
			"ROCKET_ACC"		=> ($plr['rocket_fire'] != 0) ? ( round($plr['rocket_hits'] * 100 / $plr['rocket_fire']) ) : (0),
			"ROCKET_USE"		=> ($weapsum != 0) ? round(($fwpn['rocket']*100)/$weapsum) : (0),
			
			"SHAFT_HITS"		=> $plr['shaft_hits'],
			"SHAFT_FIRE"		=> $plr['shaft_fire'],
			"SHAFT_KILLS"		=> $plr['shaft_kills'],
			"SHAFT_ACC"			=> ($plr['shaft_fire'] != 0) ? ( round($plr['shaft_hits'] * 100 / $plr['shaft_fire']) ) : (0),
			"SHAFT_USE"			=> ($weapsum != 0) ? round(($fwpn['shaft']*100)/$weapsum,1) : (0),
			
			"PLASMA_HITS"		=> $plr['plasma_hits'],
			"PLASMA_FIRE"		=> $plr['plasma_fire'],
			"PLASMA_KILLS"		=> $plr['plasma_kills'],
			"PLASMA_ACC"		=> ($plr['plasma_fire'] != 0) ? ( round($plr['plasma_hits'] * 100 / $plr['plasma_fire']) ) : (0),
			"PLASMA_USE"		=> ($weapsum != 0) ? round(($fwpn['plasma']*100)/$weapsum) : (0),
			
			"RAIL_HITS"			=> $plr['rail_hits'],
			"RAIL_FIRE"			=> $plr['rail_fire'],
			"RAIL_KILLS"		=> $plr['rail_kills'],
			"RAIL_ACC"			=> ($plr['rail_fire'] != 0) ? ( round($plr['rail_hits'] * 100 / $plr['rail_fire']) ) : (0),
			"RAIL_USE"			=> ($weapsum != 0) ? round(($fwpn['rail']*100)/$weapsum) : (0),
			
			"BFG_HITS"			=> $plr['bfg_hits'],
			"BFG_FIRE"			=> $plr['bfg_fire'],
			"BFG_KILLS"			=> $plr['bfg_kills'],
			"BFG_ACC"			=> ($plr['bfg_fire'] != 0) ? ( round($plr['bfg_hits'] * 100 / $plr['bfg_fire']) ) : (0),
			"BFG_USE"			=> 0,			
			

			
			"HITS"				=> $plr['hits'],
			"SHOTS"				=> $plr['shots'],
			"ACC"				=>($plr['shots'] != 0) ? (round($plr['hits']*100/$plr['shots']) ): (0),
		);
		
		
	$MARKERS_MORE = Array
		(
			"PLAYER_NAME" 		=> $playerName,
			"DMGGIVEN"			=> $plr['dmggiven'],
			"DMGRECVD"			=> $plr['dmgrecvd'],
			"DMGRATE"			=> ($plr['dmgrecvd']>0)?round($plr['dmggiven']/$plr['dmgrecvd'],2):0,
			"IMPRESSIVES"		=> $plr['impressives'],
			"EXCELLENTS"		=> $plr['excellents'],
			"HUMILIATIONS"		=> $plr['humiliations'],
			"REDARMORS"			=> $plr['redArmors'],
			"YELLOWARMORS"		=> $plr['yellowArmors'],
			"MEGAHEALTHES"		=> $plr['megaHealthes'],
			"POWERUPS"			=> $plr['powerUps'],
			
			
		);	
		
	//$template->assign_variables($MARKERS);
	//$plr_stats .= $template->build('player_stats') or die("error building: match\player_stats");
	
	$template2->assign_variables($MARKERS);
	$plr_stats .= $template2->build('player_stats') or die("error building: match\player_stats");
	
	$template->assign_variables($MARKERS_WEAP);
	$weap_stats .= $template->build('weapon_stats') or die("error building: match\weapon_stats");	
	
	$template->assign_variables($MARKERS_MORE);
	$more_stats .= $template->build('more_stats') or die("error building: match\more_stats");
	
	if (($match['gameType'] == "DUEL") or ($match['gameType']=='CTF') or ($match['gameType']=='TDM')) {
		if ($plrnum <= 4) {
			$plrid[$plrnum] = $plr['playerID'];
			$plrname[$plrnum] = getPlayerName($plr['playerID'],true,false,false);
			$plrnum++;
		};
	}
}
$MARKERS = Array
		(
			"G_PLAYERS_STATS"	=> $plr_stats,
			
			"MATCH_REDSCORE"	=> $match['redScore'],
			"MATCH_BLUESCORE"	=> $match['blueScore'],
		);
$template2->assign_variables($MARKERS);
$score_board = $template2->build('main') or die("error building: match\score_board");		

//GTW: last_duel
if ($match['gameType'] == "DUEL") {
	$matchData = $db->prefix."_matchData";
	$matchList = $db->prefix."_matchList";
	$res = $db->select("",
						"",
					"SELECT map, demo, dlnum, dateTime, 
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
					WHERE $matchData.playerID = '$plrid[1]' AND $matchList.gameType = 'DUEL'
					ORDER BY $matchList.matchID DESC
				) AS dlist1, (
					SELECT 
						$matchList.matchID,
						$matchData.playerID AS playerID2, 
						$matchData.frags as frags2, 
						$matchData.win AS win2
					FROM $matchList
					INNER JOIN $matchData ON $matchList.matchID = $matchData.matchID
					WHERE $matchData.playerID = '$plrid[2]' AND $matchList.gameType = 'DUEL'
					ORDER BY $matchList.matchID DESC
				) AS dlist2
				WHERE dlist1.matchID = dlist2.matchID
				LIMIT 10");
	foreach ($res as $row) {
		$MARKERS = Array
		(
			"PLAYER1_NAME"				=> ($row['win1'] == 1) ? "<b>".$plrname[1]."</b>" : $plrname[1],	
			"PLAYER2_NAME"				=> ($row['win2'] == 1) ? "<b>".$plrname[2]."</b>" : $plrname[2],	
			"FRAGS"						=> ($matchID == $row['matchID']) ? "<b>[".$row['frags1'].":".$row['frags2']."]</b>" : "[".$row['frags1'].":".$row['frags2']."]",
			"MAP_NAME"					=> $row['map'],
			"MATCH_ID"					=> $row['matchID'],
			"DEMO_DLS"					=> $row['dlnum'],
			"MATCH_DATE"				=> $row['dateTime'],
			"DEMO_LINK"					=> ($row['demo']<>"") ? "<a href='/demo/$row[matchID]'>".$dict->data['download']." ($row[dlnum])</a>" : "",
		);
		$template->assign_variables($MARKERS);
		$last_duels .= $template->build('last_duel') or die("error building: match\last_duel");
	}
}
// GTW: if_gametype_duel
$MARKERS_IF = Array
	(
		"GTW_LOGIC"			=> ($match['gameType'] == "DUEL") ? (true) : (false),
		"G_LAST10_DUELS"	=> $last_duels,
		"PLAYER1_NAME"		=> $plrname[1],	
		"PLAYER2_NAME"		=> $plrname[2],	
		
		"PLAYER1_ID"		=> $plrid[1],	
		"PLAYER2_ID"		=> $plrid[2],	
		
		"L_LAST10_DUELS"	=> $dict->data['last_10_duels'],
	);

$template->assign_variables($MARKERS_IF);
$if_gt_duel = $template->build('if_gametype_duel') or die("error building: match\if_gametype_duel");
// GTW: if_videos
$G_VIDEO_LINKS = $G_VIDEOS = null;
if ($match['video']) {
    $urls = explode(' ', trim($match['video']));
    foreach ($urls as $key => $url) {
        $template->assign_variables(array('VIDEO_ID' => $key, 'VIDEO_NAME' => 'video' . ($key + 1)));
        $G_VIDEO_LINKS .= $template->build('video_link');
        $template->assign_variables(array('VIDEO_ID' => $key, 'VIDEO_URL' => $url));
        $G_VIDEOS .= $template->build('video');
    }
}
$template->assign_variables(array('GTW_LOGIC' => (bool)$match['video'],
    'G_VIDEO_LINKS' => $G_VIDEO_LINKS, 'G_VIDEOS' => $G_VIDEOS,
));
$if_videos = $template->build('if_videos');
// Подключение комментариев
$res = $db->select("*","comments","WHERE materialID = '$matchID' AND moduleID = 2 ORDER BY cmtID DESC");
// GTW: comment
$cmtnum = count($res);
foreach ($res as $row) {
	if ($row['playerID']<>0) $plr = getPlayer($row['playerID']);
	$MARKERS = Array
		(
			"CMT_AUTHOR"		=> ($row['playerID']<>0) ? getIcons($plr):getIcons($row,false,false,false),
			"CMT_DATE"			=> $row['postTime'],
			"COMMENT"			=> $row['comment'],
			"CMT_NUM"			=> $cmtnum--,
			"CMT_DELETE"		=> ($xdata['access']>=3) ? "<a href='/do/comment/delete/$row[cmtID]/$row[materialID]'><img src='$THEME_ROOT/images/delete_ico.gif' /></a>" : "",
		);
	$template->assign_variables($MARKERS);
	$match_comments .= $template->build('comment') or die("error building: match\comment");
}

// GTW: if_have_comments
$MARKERS_IF = Array
	(
		"GTW_LOGIC"			=> (count($res)>0) ? (true) : (false),
		"G_MATCH_COMMENTS"	=> $match_comments,
	);

$template->assign_variables($MARKERS_IF);
$if_have_comments = $template->build('if_have_comments') or die("error building: match\if_have_comments");

$MARKERS_IF = Array
	(
		"GTW_LOGIC"			=> ($xdata['playerID'] <> 0) ? (true) : (false),
		"NULL"				=> NULL,
	);
$template->assign_variables($MARKERS_IF);
$if_logged = $template->build('if_logged') or die("error building: match\if_logged");

// ban comments
$banned = false;
$ipLong = ip2long($_SERVER['REMOTE_ADDR']);
$ipLong = sprintf("%u", $ipLong);
$res = $db->select('*','bans',"WHERE  banLevel=2 AND (banMaskStart < '$ipLong' AND banMaskEnd > '$ipLong') AND (banEnd>NOW()) LIMIT 1");
if (count($res) > 0) {
	$banned = true;
	$ban = $res[0];
	$BAN_MSG = ("<div align='center'><b>You can not post comments! Ban expire at $ban[banEnd]<br>Reason: $ban[banReas]</b></div>");
}
$MARKERS_IF = Array
	(
		"GTW_LOGIC"			=> !$banned,
		"IF_LOGGED"			=> $if_logged,
		'MATERIAL_ID'		=> $match['matchID'],
		'BAN_MSG' => $BAN_MSG
	);
$template->assign_variables($MARKERS_IF);
$IF_CAN_CMT = $template->build('if_can_cmt') or die("error building: match\if_can_cmt");

//
// Build Main
//
$maxMID = $db->select("MAX(matchID) as max","matchList","");
$maxMID = $maxMID[0]['max'];

$page_title = "#$matchID - ".$dict->data['match_result'];
$page_name = "";

$videos = null;


$MARKERS = Array
	(
		"THEME_ROOT"		=> $CFG['root']."/themes/".$CFG['theme'],
		
		"MATCH_ID"			=> $match['matchID'],
		'MATERIAL_ID'		=> $match['matchID'],
		"HOST_NAME"			=> $match['hostName'],
		"MATCH_MAP"			=> $match['map'],
		"GAMETYPE"			=> GameType($match['gameType']),
		"GAMETYPE_SHORT"	=> $match['gameType'],
		"PLAYERS"			=> $match['players'],
		"MATCH_DATE_AGO"	=> ($CFG['language'] == 'ru') ? ago_rus(strtotime($match['dateTime'])) : ago_(strtotime($match['dateTime'])),
		"MATCH_DATE"		=> $match['dateTime'],
		"GAME_TIME"			=> floor($match['gameTime']/60).":".$match['gameTime'] % 60,
		"DEMO_LINK"			=> ($match['demo']<>"") ? "<b><a href='/demo/$match[matchID]'>".$dict->data['download']." ".$dict->data['demo']." ($match[dlnum])</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='http://nfk.harpywar.com/demoviewer/?demourl=https://stats.needforkill.ru/demo/$match[matchID]' target='_blank' STYLE='COLOR: CRIMSON'>Online Demo Viewer</a><sup><i>beta</i></sup></b>" : "",
		"DEMO_DLS"			=> $match['dlnum'],
		"COMMENTS_NUM"		=> $match['comments'],
		'MODULE_ID' 		=> 2,
		
		"G_SCORE_BOARD"		=> $score_board,
		"G_WEAPONS_STATS"	=> $weap_stats,
		"G_MORE_STATS"		=> $more_stats,
		"IF_GT_DUEL"		=> $if_gt_duel,
		"IF_GT_2x2"		=> $IF_GT_2x2,
		"IF_HAVE_COMMENT"	=> $if_have_comments,
		"IF_VIDEOS"	=> $if_videos,

		'IF_CAN_CMT' => $IF_CAN_CMT,
		
		"NEXT_MATCH"		=> ($maxMID>$matchID) ? ("<a href='/match/".($matchID+1)."' title=''><</a>") : "",
		"PREV_MATCH"		=> ($matchID>1) ? "<a href='/match/".($matchID-1)."' title=''>></a>" : "",
	
		"L_HOSTNAME"		=> $dict->data['host_name'],
		"L_MAP"				=> $dict->data['map'],
		"L_GAMETYPE"		=> $dict->data['game_type'],
		"L_GAMETIME"		=> $dict->data['game_time'],
		"L_PLAYERS"			=> $dict->data['players'],
		"L_DATE"			=> $dict->data['date'],
		"L_DOWNLOAD_DEMO"	=> $dict->data['download_demo'],
		"L_MATCH"			=> $dict->data['match'],
		"L_AGO"				=> $dict->data['ago'],	
		"L_FRAGS"			=> $dict->data['frags'],
		"L_DEATHS"			=> $dict->data['deaths'],
		"L_FRAG_RATE"		=> $dict->data['frag_rate'],
		"L_RESULT"			=> $dict->data['result'],
		"L_PING"			=> $dict->data['ping'],
		"L_TEAM"			=> $dict->data['team'],
		"L_NAME"			=> $dict->data['name'],
		"L_SCORE"			=> $dict->data['score'],
		"L_TIME"			=> $dict->data['time'],
		"L_ACCURACY"		=> $dict->data['accuracy'],
		"L_WEAP_USE"		=> $dict->data['weap_use'],
		"L_PLAYER"			=> $dict->data['player'],
		"L_WEAPON"			=> $dict->data['weapon'],
		"L_TOTAL"			=> $dict->data['total'],
		"L_WEAP_STATS"		=> $dict->data['weap_stats'],
		"L_FULL_DUEL_LIST"	=> $dict->data['full_duel_list'],
		"L_PLRS_DUEL_LIST"	=> $dict->data['players_duel_list'],
		"L_ADD_COMMENT"		=> $dict->data['add_comment'],
		"L_ADD"				=> $dict->data['add'],
		"L_DMGGIVEN"		=> $dict->data['dmggiven'],
		"L_DMGRECVD"		=> $dict->data['dmgrecvd'],
		"L_ALL_STATS"		=> $dict->data['overal_stats'],
		"L_DMGRATE"			=> $dict->data['dmgrate'],
		"L_YELLOWARMORS"			=> $dict->data['yellowarmor'],
		"L_REDARMORS"			=> $dict->data['redarmor'],
		"L_MEGAHEALTHES"			=> $dict->data['megahealth'],
		"L_POWERUPS"			=> $dict->data['powerup'],
		"L_IMPRESSIVES"			=> $dict->data['impressive'],
		"L_EXCELLENTS"			=> $dict->data['excellent'],
		"L_HUMILIATIONS"			=> $dict->data['humiliation'],
	);
	
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: match\main");

?>