<?php
if (!defined("NFK_LIVE")) die(); 

$template->load_template('mod_profile/profile_matches');

$CUR_ADDRES .= $PARAMSTR[3]."/";

$arr_gt = array("duel", "dm", "ctf", "tdm", "dom");
if (in_array($PARAMSTR[4],$arr_gt)) {
	$andGameType = "AND gameType = '$PARAMSTR[4]'";
	$gameType = $PARAMSTR[4];
	$CUR_ADDRES .= $PARAMSTR[4]."/";
}

$TmatchList = $db->prefix."_matchList";
$TmatchData = $db->prefix."_matchData";

$p_count = count($PARAMSTR);
$cur_page = ($PARAMSTR[$p_count-2] == "page") ? $PARAMSTR[$p_count-1] : 1;
if ( !is_numeric($cur_page) ) $cur_page = 1;
	
$matches = $db->select("SQL_CALC_FOUND_ROWS 
				ml.matchID , ml.hostName , ml.map , 
				ml.gameType , ml.players , ml.dateTime , 
				ml.gameTime , ml.demo , ml.dlnum , 
				ml.comments , md.playerID , md.win , 
				md.score , md.time "
			,"matchList ml","
				INNER JOIN $TmatchData md USING(matchID) 
				
				WHERE md.playerID = '$plr[playerID]' $andGameType
				ORDER BY ml.matchID DESC 
				LIMIT ".(($cur_page - 1)*$CFG['items_per_page']).", ".($CFG['items_per_page']));
$total = $db->select("FOUND_ROWS() as rows","","");
$total = $total[0]['rows'];
$pages_count = ceil($total / $CFG['items_per_page']);
		
// GTW: match
foreach ($matches as $match) {
	$MARKERS = Array
		(
			"MATCH_ID"				=> $match['matchID'],
			"HOST_NAME"				=> $match['comments'],
			"HOST_NAME_AND_CMTS"	=> ($match['comments']==0) ? $match['hostName'] : $match['hostName']." (".$match['comments'].")",
			"MATCH_MAP"				=> $match['map'],
			"GAMETYPE"				=> GameType($match['gameType']),
			"GAMETYPE_SHORT"		=> $match['gameType'],
			"PLAYERS"				=> $match['players'],
			"MATCH_DATE_AGO"		=> ($CFG['language'] == 'ru') ? ago_rus(strtotime($match['dateTime'])) : ago_(strtotime($match['dateTime'])),
			"MATCH_DATE"			=> $match['dateTime'],
			"GAME_TIME"				=> floor($match['gameTime']/60).":".$match['gameTime'] % 60,
			"DEMO_LINK"				=> ($match['demo']<>"") ? "<a href='/demo/$match[matchID]'>".$dict->data['download']." ($match[dlnum])</a>" : "",
			"DEMO_DLS"				=> $match['dlnum'],
			"COMMENTS_NUM"			=> $match['comments'],
			"RESULT"				=> $dict->data[getMatchResult($match['win'])],
			"SCORE"					=> getSign($match['score']),
		);
	$template->assign_variables($MARKERS);
	$match_rows .= $template->build('match') or die("error building: profile\match");
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


$page_title = "$plr[name] - ".$dict->data['player_matches'];

//
// Build Main
//
$MARKERS = Array
	(
		"G_MATCH_LIST"		=> $match_rows,
		"PAGES"				=> $pages,
		"GAME_TYPE_MENU"	=> $gt_menu,
		
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
		"L_AGO"				=> $dict->data['ago'],
		"L_RESULT"			=> $dict->data['result'],
	);
	
$template->assign_variables($MARKERS);
$TMPL_matches .= $template->build('matches') or die("error building: profile\matches");

?>