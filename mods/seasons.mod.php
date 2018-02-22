<?php
if (!defined("NFK_LIVE")) die();

$ladderPage = $PARAMSTR[2];

$template->load_template('mod_seasons');

$arr_gt = array("duel", "dm", "ctf", "tdm", "dom");
if (in_array($PARAMSTR[2],$arr_gt)) {
	$ladderTbl = 'st_seasons_'.strToLower($PARAMSTR[2]);
	$gType = strToUpper($PARAMSTR[2]);
	$CUR_ADDRES .= $PARAMSTR[2]."/";
} else {
	$ladderTbl = 'st_seasons_duel';
	$gType = "DUEL";
	$CUR_ADDRES .= "duel/";
}

$seas_row = $db->select('*','seasons','');
$seasNum = count($seas_row);

for ($i = $seasNum-1; $i>=0; $i--) {
	$res = $db->select("*","$ladderTbl","WHERE season = $i AND games <> 0 ORDER BY place LIMIT 10",false);
	unset($ladder);
	foreach ($res as $row) {
		if ($gType == "DUEL") $d_rank = floor($row['score']/100);
		$MARKERS = array (
			"PLAYER_ID"			=> $row['playerID'],
			"PLAYER_NAME"		=> getPlayerName($row['playerID']),
			
			"FRAGS"				=> $row['frags'],
			"DEATHS"			=> $row['deaths'],
			
			"GAMES"				=> $row['games'],
			"WINS"				=> $row['wins'],
			"LOSSES"			=> $row['losses'],
			"WIN_RATE"			=> ($row['wins'] != 0) ? (round($row['wins']*100/($row['wins']+$row['losses'])) ): ("0"),

			"SCORE"				=> $row['score'],
			"DUEL_RANK"			=> ($gType == "DUEL") ? "<img title='$row[score]' src='/themes/$CFG[theme]/images/ranks/d".$d_rank.".jpg'> " : "",
			
			"PLACE"				=> $row['place'],
			
			"PLAYED_TIME"		=> $row['time'],
		);
		$template->assign_variables($MARKERS);
		$ladder .= $template->build('player') or die("error building: seasons\player");
	}
	$MARKERS2 = array (
		'SEASON_NUM' => $i+1,
		'SEAS_DATE_START' => date("Y.m.d", strtotime($seas_row[$i]['dateStart'])),
		'SEAS_DATE_END' => date("Y.m.d", strtotime($seas_row[$i]['dateEnd'])),
		'G_LADDER' => $ladder,
	);
	$template->assign_variables($MARKERS2);
	$seasons .= $template->build('season') or die("error building: seasons\row");
}

$page_title = $dict->data['seasons'].": ".$gType;
$page_name = $page_title;

// Build Main
$MARKERS = array (
	"G_SEASONS"			=> $seasons,
	"L_WINS"			=> $dict->data['wins'],
	"L_LOSSES"			=> $dict->data['losses'],
	"L_WIN_RATE"		=> $dict->data['win_rate'],
	"L_GAMES"			=> $dict->data['games'],
	"L_FRAGS"			=> $dict->data['frags'],
	"L_DEATHS"			=> $dict->data['deaths'],
	"L_NAME"			=> $dict->data['name'],
	"L_SCORE"			=> $dict->data['score'],
	"L_SEASON"			=> $dict->data['season'],
);
	
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: seasons\main");

?>