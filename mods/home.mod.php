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
require_once("inc/nfk_planet.inc.php");
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
        'VIDEO_ICO'  => ($row['video']) ? '<img src="/images/video.gif" align="absBottom"> ' : null,

		"L_AGO"					=> $dict->data['ago'],
	);
	$template->assign_variables($MARKERS);
	$recent_matches .= $template->build('recent_match') or die("error building: home\recent_match");
}
// Last News
$newsList = $db->select('*', 'news', 'ORDER BY news_id DESC LIMIT 2');
$contentNews = null;
foreach ($newsList as $news) {
    $template->assign_variables(array(
        'title' => $news['title'],
        'description' => $news['description'],
        'content' => $news['content'],
        'comments' => $news['comments'],
        'date' => $news['date'],
        'newsID' => $news['news_id'],
    ));
    $contentNews .= $template->build('news_row');
}
// Last Comments
$res = $db->select('*','comments','ORDER BY cmtID DESC LIMIT 3');
// GTW: last_comment 
$last_comments = "";
foreach ($res as $row) {

	if ($row['playerID']<>0) $plr = getPlayer($row['playerID']);
	$moduleID = $row['moduleID'];
	$materialID = $row['materialID'];
	if ($moduleID == 3) {
		$tour = $db->select('title, tourNum','tr_tourneys',"WHERE tourID = $materialID",false);
		$tour = $tour[0];
		$tourTitle = ($tour['tourNum']<>'0') ? $tour['title'].' #'.$tour['tourNum'] : $tour['title'];
		$mtrString = $tourTitle;
	} elseif ($moduleID == 4) {
        $mtrString = 'News #'.$materialID;
    } else $mtrString = '#'.$materialID;
	
	$addlen = mb_strlen("$mtrString $row[author]: ",'utf-8');
	if ($row['playerID']<>0) $addlen+=6;
	$maxlen = 60 - $addlen;
	$cmtline = str_replace("<br>"," ",$row['comment']);
	$cmtlen = mb_strlen($cmtline,'utf-8');
	if ($cmtlen > $maxlen) {
		$cmt =  mb_substr($cmtline,0,$maxlen,'utf-8').
				"<div id='dcmt$row[cmtID]' style='display:inline'>".
				'<a href="javascript://" onClick="ShowOrHide2(\'cmt'.
				$row['cmtID'].'\'); return false;"><b>...</b></a></div>'.
				"<div id='cmt$row[cmtID]' style='display:none'>".
				mb_substr($cmtline,$maxlen,mb_strlen($cmtline,'utf-8'),'utf-8').
				"</div>"; 
	} else {
		$cmt = $cmtline;
	}
	

	$MARKERS = Array(
		"MATERIAL_ID"		=> $materialID,
		'MOD_URL' 			=> $MODS_URL[$moduleID],
		'MATERIAL_NAME' 	=> $mtrString,//($moduleID == 3) ? $tourTitle : '#'.$materialID,
		"PLAYER_ID"			=> $row['playerID'],	
		"CMT_AUTHOR"		=> ($row['playerID']<>0) ? getIcons($plr):
														getIcons($row,false,false,false),
		"CMT_DATE"			=> $row['postTime'],
		"COMMENT"			=> $cmt,
	);
	$template->assign_variables($MARKERS);
	$last_comments .= $template->build('last_comment') or die("error building: home\comment");
}

// Турниры
$dateNow = date('Y-m-d H:i:s');
$res = $db->select('tourID, title, tourNum, status, regNum, checkNum, dateStart, dateCheckin, dateReg, winnerID','tr_tourneys',
				"WHERE status < 4 ORDER BY dateStart DESC LIMIT 2",false);
if (count($res) <> 0) {
	foreach ($res as $row) {
		if (date($row['dateReg'])>$dateNow) {
			$dateStr = 'Регистрация ' . _countTime(strtotime($row['dateReg']),$CUR_LANG);
		} else if (date($row['dateCheckin'])>$dateNow) {
			$dateStr = 'Чек-ин ' . _countTime(strtotime($row['dateCheckin']),$CUR_LANG);
		} else if (date($row['dateStart'])>$dateNow) {
			$dateStr = _countTime(strtotime($row['dateStart']),$CUR_LANG);
		} else {
			$dateStr = 'Started';
		}
		$dateReg = date($row['dateReg']);
		if ($row['status'] == 3) $dateStr = 'Завершен';

		$MARKERS = Array (
			'TITLE' => ($row['tourNum'] == 0)?$row['title']:$row['title'].' #'.$row['tourNum'],
			'DATE_START_F' => $dateStr,
			'DATE_START' => $row['dateStart'],
			'REG_NUM' => ($dateReg<=$dateNow) ? $row['regNum'] : '-',
			'CHECK_NUM' => ($dateReg<=$dateNow) ?$row['checkNum'] : '-',
			'TOUR_ID' => $row['tourID'],
			'WINNER' => ($row['winnerID']<>0)?getPlayerName($row['winnerID']):'&mdash;',
		);
		$template->assign_variables($MARKERS);
		$TOURNEYS .= $template->build('tourney') or die("error building: home\tourney");
	}
	$IF_MARKERS = Array (
		'GTW_LOGIC' => True,
		'G_TOURNEYS' => $TOURNEYS,
		'L_TITLE' => $dict->data['tour_title'],
		'L_DATE_START' => $dict->data['tour_date_start'],
		'L_REG_NUM' => $dict->data['tour_reg_num'],
		'L_WINNER' => $dict->data['tour_winner'],
	);
} else $IF_MARKERS = Array ('GTW_LOGIC' => False);
$template->assign_variables($IF_MARKERS);
$IF_TOUR = $template->build('if_tour') or die("error building: home\tourney");

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
// Top 8 TOUR
$res = $db->select('playerID, score FROM tr_ladder', '', 'ORDER BY score DESC LIMIT 8');
// GTW: top_tour
$place = 0;
foreach ($res as $row) {
	$place++;
	$MARKERS = array(
		"PLAYER_ID" => $row['playerID'],
		"PLAYER_NAME" => getPlayerName($row['playerID']),
		"SCORE" => $row['score'],
		"PLACE" => $place,
	);
	$template->assign_variables($MARKERS);
	$top8_tour .= $template->build('top_tour') or die("error building: home\top_tour");
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
		'G_IF_TOUR'			=> $IF_TOUR,
		"G_TOP_DUEL"		=> $top8_duel,
		"G_TOP_DM"			=> $top8_dm,
		"G_TOP_TDM"			=> $top8_tdm,
		"G_TOP_CTF"			=> $top8_ctf,
		"G_TOP_ALL"			=> $top8_all,
		"G_TOP_TOUR"		=> $top8_tour,
        'contentNews' => $contentNews,
		
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
		"L_SEASON"			=> $dict->data['season'],
		"CUR_SEASON"			=> CUR_SEASON,
		"L_TOUR_RATING"			=> $dict->data['tour_ladder'],
		"L_LAST_NEWS"			=> $dict->data['last_news'],
	);

$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: home\main");

?>
