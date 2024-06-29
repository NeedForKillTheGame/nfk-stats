<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2009 ConnecT, 2011 coolant
// Module:	Home
// Item:	
// Version:	0.1.8	14.07.2011
/////////////////////////////////////////////

if (!defined("NFK_LIVE")) die();

// ы
$fileName = 'mods/inc/dateflag.txt';
$lastEdit = date ('d', filemtime($fileName));
$nowDay   = date('d');

if ($lastEdit <> $nowDay) {
    touch($fileName);
	require_once("inc/service.inc.php");
}
//

//if ($_SESSION['me'] != "") $me->assign($_SESSION['me']);
//$xdata = $_SESSION['me'];


$template->load_template('mod_home');

// NFK Planet Servers

//$res = $db->select('*','serverList','where ttl >= NOW()');
/*require_once("inc/nfk_planet.inc.php");
$servers = nfkpl_getServers();
//$playersCount = 0;
//preg_replace('/\W/', '', $a);

// GTW: planet_server 

foreach ($servers as $key => $server) {
	$hostname = clearName(stripTags($server['Hostname']));
	$hostlink = urlencode(stripTags($server['Hostname']));
	$MARKERS = Array
	(
		"HOST_NAME"			=> $hostname,
		"HOST_LINK"			=> $hostlink,
		"MAP_NAME"			=> $server['Map'],
		"GAMETYPE"			=> $server['Gametype'],
		"SERVER_IP"			=> $server['IP'],
		"SERVER_PORT"		=> $server['Port'],
		"PLAYERSCOUNT"		=> $server['Players'],
		"PLAYERSMAX"		=> $server['Maxplayers'],
		//"COUNTRY_CC3"		=> strtolower( ip2country('cc3',$row['serverIP']) ),
	);
	$template->assign_variables($MARKERS);
	$nfk_planet .= $template->build('planet_server') or die("error building: home\planet_server");
}
*/
// Recent Matches
$res = $db->select('*','matchList','ORDER BY matchID DESC LIMIT 3');
// GTW: recent_match 
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
		
		"L_AGO"					=> $dict->data['ago'],
	);
	$template->assign_variables($MARKERS);
	$recent_matches .= $template->build('recent_match') or die("error building: home\recent_match");
}

// Last Comments
$res = $db->select('*','matchComments','ORDER BY cmtID DESC LIMIT 3');
// GTW: last_comment 
$last_comments = "";
foreach ($res as $row) {

	$maxpos = strlen("#$row[matchID] $row[author]: ");
	if ($row['playerID']<>0) $maxpos+=6;
	$pos = 60 - $maxpos;
	if (strlen($row['comment'])>$pos) {
		$cmt = str_replace("<br>"," ",mb_substr($row['comment'],0,$pos,'utf-8'))."<div id='dcmt$row[cmtID]' style='display:inline'>".'<a href="javascript://" onClick="ShowOrHide2(\'cmt'.$row['cmtID'].'\'); return false;"><b>...</b></a></div>'."<div id='cmt$row[cmtID]' style='display:none'>".mb_substr($row['comment'],$pos,strlen($row['comment']),'utf-8')."</div>"; 
	} else $cmt = str_replace("<br>"," ",$row['comment']);

	if ($row['playerID']<>0) $plr = $player->fetchId($row['playerID']);
	$MARKERS = Array
	(
		"SELF"				=> $PHP_SELF,
		"MATCH_ID"			=> $row['matchID'],
		"COMMENT_AUTOR"		=>  ($row['playerID']<>0) ? getIcons($plr):
														getIcons($row,false,false,false),
		"COMMENT"			=> $cmt,
	);
	$template->assign_variables($MARKERS);
	$last_comments .= $template->build('last_comment') or die("error building: home\last_comment");
}

// Top 8 DUEL
$res = $db->select("playerID, score, rank","ladderDUEL","WHERE `games` <> 0 ORDER BY score DESC LIMIT 8");
// GTW: top_duel 
$place = 0;
foreach ($res as $row) {
	$place++;
	$MARKERS = Array
		(
			"SELF"				=> $PHP_SELF,
			"PLAYER_ID"			=> $row['playerID'],
			"PLAYER_NAME"		=> getPlayerName($row['playerID']),
			
			"SCORE"				=> $row['score'],
			"RANK"				=> $row['rank'],
			"PLACE"				=> $place,
			
			"L_POINTS"			=> $dict->data['points'],
			"THEME_ROOT"		=> $CFG['root']."/themes/".$CFG['theme'],
		);
	$template->assign_variables($MARKERS);
	$top8_duel .= $template->build('top_duel') or die("error building: home\top_duel");
}

// Top 8 DM
$res = $db->select($db->prefix."_ladderDM.playerID, AltStat_Players.DmReiting as score","ladderDM",
					"INNER JOIN AltStat_Players ON AltStat_Players.Playerid = ".$db->prefix."_ladderDM.playerID
					WHERE `games` <> 0 ORDER BY score DESC LIMIT 8");
// GTW: top_dm
$place = 0;
foreach ($res as $row) {
	$place++;
	$MARKERS = Array
		(
			"SELF"				=> $PHP_SELF,
			"PLAYER_ID"			=> $row['playerID'],
			"PLAYER_NAME"		=> getPlayerName($row['playerID']),

			"SCORE"				=> $row['score'],
			"PLACE"				=> $place,
		);
	$template->assign_variables($MARKERS);
	$top8_dm .= $template->build('top_dm') or die("error building: home\top_dm");
}

// Top 8 TDM
$res = $db->select($db->prefix."_ladderTDM.playerID, AltStat_Players.TdmReiting as score","ladderTDM",
					"INNER JOIN AltStat_Players ON AltStat_Players.Playerid = ".$db->prefix."_ladderTDM.playerID
					WHERE `games` <> 0 ORDER BY score DESC LIMIT 8");
// GTW: top_tdm
$place = 0;
foreach ($res as $row) {
	$place++;
	$MARKERS = Array
		(
			"SELF"				=> $PHP_SELF,
			"PLAYER_ID"			=> $row['playerID'],
			"PLAYER_NAME"		=> getPlayerName($row['playerID']),

			"SCORE"				=> $row['score'],
			"PLACE"				=> $place,
		);
	$template->assign_variables($MARKERS);
	$top8_tdm .= $template->build('top_tdm') or die("error building: home\top_tdm");
}

// Top 8 CTF
$res = $db->select($db->prefix."_ladderCTF.playerID, AltStat_Players.CtfReiting as score","ladderCTF",
					"INNER JOIN AltStat_Players ON AltStat_Players.Playerid = ".$db->prefix."_ladderCTF.playerID
					WHERE `games` <> 0 ORDER BY score DESC LIMIT 8");
// GTW: top_ctf
$place = 0;
foreach ($res as $row) {
	$place++;
	$MARKERS = Array
		(
			"SELF"				=> $PHP_SELF,
			"PLAYER_ID"			=> $row['playerID'],
			"PLAYER_NAME"		=> getPlayerName($row['playerID']),

			"SCORE"				=> $row['score'],
			"PLACE"				=> $place,
		);
	$template->assign_variables($MARKERS);
	$top8_ctf .= $template->build('top_ctf') or die("error building: home\top_ctf");
}

// Top 8 ALL
$res = $db->select($db->prefix."_playerStats.playerID, AltStat_Players.AllRating as score","playerStats",
					"INNER JOIN AltStat_Players ON AltStat_Players.Playerid = ".$db->prefix."_playerStats.playerID
					ORDER BY score DESC LIMIT 8");
// GTW: top_all
$place = 0;
foreach ($res as $row) {
	$place++;
	$MARKERS = Array
		(
			"SELF"				=> $PHP_SELF,
			"PLAYER_ID"			=> $row['playerID'],
			"PLAYER_NAME"		=> getPlayerName($row['playerID']),

			"SCORE"				=> $row['score'],
			"PLACE"				=> $place,
		);
	$template->assign_variables($MARKERS);
	$top8_all .= $template->build('top_all') or die("error building: home\top_all");
}

//
// Build Main
//
$MARKERS = Array
	(
		"SELF"				=> $PHP_SELF,
	//	"MY_ID"				=> $xdata['id'],
		"THEME_ROOT"		=> $CFG['root']."/themes/".$CFG['theme'],
		
		"G_NFK_PLANET"		=> $nfk_planet,
		"G_RECENT_MATCHES"	=> $recent_matches,
		"G_LAST_COMMENTS"	=> $last_comments,
		"G_TOP_DUEL"		=> $top8_duel,
		"G_TOP_DM"			=> $top8_dm,
		"G_TOP_TDM"			=> $top8_tdm,
		"G_TOP_CTF"			=> $top8_ctf,
		"G_TOP_ALL"			=> $top8_all,
		
		"L_RECENT_MATCHES"	=> $dict->data['recent_matches'],
		"L_HOSTNAME"		=> $dict->data['host_name'],
		"L_PORT"			=> $dict->data['port'],
		"L_MAP"				=> $dict->data['map'],
		"L_GAMETYPE"		=> $dict->data['game_type'],
		"L_GAMETIME"		=> $dict->data['game_time'],
		"L_PLAYERS"			=> $dict->data['players'],
		"L_DATE"			=> $dict->data['date'],
		"L_DEMO"			=> $dict->data['demo'],
		"L_LAST_COMMENTS"	=> $dict->data['last_comments'],
		"L_TOP8PLAYERS"		=> $dict->data['top_8_players'],
		"L_RANK"			=> $dict->data['rank'],
		"L_NAME"			=> $dict->data['name'],
		"L_SCORE"			=> $dict->data['score'],
		"L_OVERALL_RATING"	=> $dict->data['overall_rating'],
	);

$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: home\main");

?>
