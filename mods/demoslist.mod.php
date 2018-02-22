<?php
if (!defined("NFK_LIVE")) die();

$template->load_template('mod_demoslist');

//$CUR_ADDRES .= $PARAMSTR[2]."/";
if ($PARAMSTR[2]=='downloads') {
	$order = "dlnum";
	$CUR_ADDRES .= $PARAMSTR[2]."/";
} else $order = "matchID";

// Pages
$p_count = count($PARAMSTR);
$cur_page = ($PARAMSTR[$p_count-2] == "page") ? $PARAMSTR[$p_count-1] : 1;
if ( !is_numeric($cur_page) ) $cur_page = 1;

$res = $db->select("SQL_CALC_FOUND_ROWS *","matchList","WHERE demo <> '' ORDER BY $order DESC LIMIT ".(($cur_page - 1)*$CFG['items_per_page']).", ".($CFG['items_per_page']));

$total = $db->select("FOUND_ROWS() as rows","","");
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
		"MATCH_DATE_AGO"		=> ($CFG['language'] == 'ru') ? ago_rus(strtotime($row['dateTime'])) : ago_(strtotime($row['dateTime'])),
		"MATCH_DATE"			=> $row['dateTime'],
		"GAME_TIME"				=> floor($row['gameTime']/60).":".$row['gameTime'] % 60,
		"DEMO_LINK"				=> "<a href='/demo/$row[matchID]'>".$dict->data['download']." ($row[dlnum])</a>",
		"DEMO_DLS"				=> $row['dlnum'],
		"COMMENTS_NUM"			=> $row['comments'],
		
		"L_AGO"					=> $dict->data['ago'],
	);
	$template->assign_variables($MARKERS);
	$demolist .= $template->build('match') or die("error building: demolist\match");
}


$page_title = $dict->data['demo_list'];
$page_name = $page_title;

if ( $pages_count > 1 ) {
	require_once("./mods/inc/pages.inc.php");  
}

//
// Build Main
//
$MARKERS = Array
	(
		"G_DEMO_LIST"		=> $demolist,
		"PAGES"				=> $pages,
		
		"L_HOSTNAME"		=> $dict->data['host_name'],
		"L_MAP"				=> $dict->data['map'],
		"L_GAMETYPE"		=> $dict->data['game_type'],
		"L_GAMETIME"		=> $dict->data['game_time'],
		"L_PLAYERS"			=> $dict->data['players'],
		"L_DATE"			=> $dict->data['date'],
		"L_DEMO"			=> $dict->data['demo'],
		"L_SORT_BY"			=> $dict->data['sort_by'],
		"L_BY_DONWLOADS"	=> $dict->data['by_downloads'],
		"L_BY_DATE"			=> $dict->data['by_date'],
		
	);
	
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: demolist\main");

?>