<?php
if (!defined('NFK_LIVE')) die(); 

$template->load_template('mod_profile/profile_seasons');

$seasons = $db->select('*','seasons','');
$seasNum = count($seasons);
if ($seasNum == 0) die('Error...');
function getRankIco($score){
	if (!$score) return '';
	return floor($score/100);
}

$fields = Array('frags','deaths','score','wins','losses','games','time');
$qeryStats['DM'] = $db->select('*','st_seasons_dm',"WHERE playerID = $plr[playerID]",false);
$qeryStats['TDM'] = $db->select('*','st_seasons_tdm',"WHERE playerID = $plr[playerID]",false);
$qeryStats['CTF'] = $db->select('*','st_seasons_ctf',"WHERE playerID = $plr[playerID]",false);
$qeryStats['DUEL'] = $db->select('*','st_seasons_duel',"WHERE playerID = $plr[playerID]",false);
foreach($qeryStats['DM'] as $row) $seasStats['DM'][$row['season']] = $row;
foreach($qeryStats['TDM'] as $row) $seasStats['TDM'][$row['season']] = $row;
foreach($qeryStats['CTF'] as $row) $seasStats['CTF'][$row['season']] = $row;
foreach($qeryStats['DUEL'] as $row) $seasStats['DUEL'][$row['season']] = $row;

foreach($seasons as $seas){
	$seasID = $seas['seasID']-1;
	$statsDM = $seasStats['DM'][$seasID];
	$statsTDM = $seasStats['TDM'][$seasID];
	$statsCTF = $seasStats['CTF'][$seasID];
	$statsDUEL = $seasStats['DUEL'][$seasID];
	if (!$statsDM['games'] and !$statsTDM['games'] and !$statsCTF['games'] and !$statsDUEL['games']) continue;
	foreach($fields as $field){
		$statsTOTAL[$field] = $statsDM[$field]+$statsTDM[$field]+$statsDUEL[$field]+$statsCTF[$field];
	}
	
	$MARKERS = Array (
		'SEASON_NUM' => $seas['seasNum'],
		'SEAS_DATE_START' => date("Y.m.d", strtotime($seas['dateStart'])),
		'SEAS_DATE_END' => date("Y.m.d", strtotime($seas['dateEnd'])),
		
		'DM_PLACE' => getPlaceIco($statsDM['place']),
		'DM_SCORE' => $statsDM['score'],
		'DM_WINS' => $statsDM['wins'],
		'DM_LOSSES' => $statsDM['losses'],
		'DM_WIN_RATE' => ($statsDM['games'])?(round($statsDM['wins']*100/$statsDM['games'])).'%':'-',
		'DM_FRAGS' => $statsDM['frags'],
		'DM_DEATHS' => $statsDM['deaths'],
		'DM_FRAG_RATE' => ($statsDM['deaths'])?(round($statsDM['frags']/$statsDM['deaths'],2)):'-',
		'DM_GAMES' => $statsDM['games'],
		'DM_PLAYED' => sec2HourDays($statsDM['time']),
		
		'TDM_PLACE' => getPlaceIco($statsTDM['place']),
		'TDM_SCORE' => $statsTDM['score'],
		'TDM_WINS' => $statsTDM['wins'],
		'TDM_LOSSES' => $statsTDM['losses'],
		'TDM_WIN_RATE' => ($statsTDM['games'])?(round($statsTDM['wins']*100/$statsTDM['games'])).'%':'-',
		'TDM_FRAGS' => $statsTDM['frags'],
		'TDM_DEATHS' => $statsTDM['deaths'],
		'TDM_FRAG_RATE' => ($statsTDM['deaths'])?(round($statsTDM['frags']/$statsTDM['deaths'],2)):'-',
		'TDM_GAMES' => $statsTDM['games'],
		'TDM_PLAYED' => sec2HourDays($statsTDM['time']),

		'CTF_PLACE' => getPlaceIco($statsCTF['place']),
		'CTF_SCORE' => $statsCTF['score'],
		'CTF_WINS' => $statsCTF['wins'],
		'CTF_LOSSES' => $statsCTF['losses'],
		'CTF_WIN_RATE' => ($statsCTF['games'])?(round($statsCTF['wins']*100/$statsCTF['games'])).'%':'-',
		'CTF_FRAGS' => $statsCTF['frags'],
		'CTF_DEATHS' => $statsCTF['deaths'],
		'CTF_FRAG_RATE' => ($statsCTF['deaths'])?(round($statsCTF['frags']/$statsCTF['deaths'],2)):'-',
		'CTF_GAMES' => $statsCTF['games'],
		'CTF_PLAYED' => sec2HourDays($statsCTF['time']),

		/*'DOM_PLACE' => getPlaceIco($statsDOM['place']),
		'DOM_SCORE' => $statsDOM['score'],
		'DOM_WINS' => $statsDOM['wins'],
		'DOM_LOSSES' => $statsDOM['losses'],
		'DOM_WIN_RATE' => ($statsDOM['games'])?(round($statsDOM['wins']*100/$statsDOM['games'])):'-',
		'DOM_FRAGS' => $statsDOM['frags'],
		'DOM_DEATHS' => $statsDOM['deaths'],
		'DOM_FRAG_RATE' => ($statsDOM['deaths'])?(round($statsDOM['frags']/$statsDOM['deaths'],2)):'-',
		'DOM_GAMES' => $statsDOM['games'],
		'DOM_PLAYED' => $statsDOM['time'],*/	
		
		'DUEL_PLACE' => getPlaceIco($statsDUEL['place']),
		'DUEL_SCORE' => $statsDUEL['score'],
		'DUEL_RANK' => getRankIco($statsDUEL['score']),
		'DUEL_WINS' => $statsDUEL['wins'],
		'DUEL_LOSSES' => $statsDUEL['losses'],
		'DUEL_WIN_RATE' => ($statsDUEL['games'])?(round($statsDUEL['wins']*100/$statsDUEL['games'])).'%':'-',
		'DUEL_FRAGS' => $statsDUEL['frags'],
		'DUEL_DEATHS' => $statsDUEL['deaths'],
		'DUEL_FRAG_RATE' => ($statsDUEL['deaths'])?(round($statsDUEL['frags']/$statsDUEL['deaths'],2)):'-',
		'DUEL_GAMES' => $statsDUEL['games'],
		'DUEL_PLAYED' => sec2HourDays($statsDUEL['time']),
	
		'TOTAL_PLACE' => '-',
		'TOTAL_SCORE' => $statsTOTAL['score'],
		'TOTAL_WINS' => $statsTOTAL['wins'],
		'TOTAL_LOSSES' => $statsTOTAL['losses'],
		'TOTAL_WIN_RATE' => ($statsTOTAL['games'])?(round($statsTOTAL['wins']*100/$statsTOTAL['games'])).'%':'-',
		'TOTAL_FRAGS' => $statsTOTAL['frags'],
		'TOTAL_DEATHS' => $statsTOTAL['deaths'],
		'TOTAL_FRAG_RATE' => ($statsTOTAL['deaths'])?(round($statsTOTAL['frags']/$statsTOTAL['deaths'],2)):'-',
		'TOTAL_GAMES' => $statsTOTAL['games'],
		'TOTAL_PLAYED' => sec2HourDays($statsTOTAL['time']),
	);
	$template->assign_variables($MARKERS);
	$SEASONS .= $template->build('season') or die("error building: profile\season");
}

$res = $db->select('*','playerStats',"WHERE playerID=$plr[playerID]");
$statsALL = $res[0];

$page_title = "$plr[name] - ".$dict->data['seasons'];

// Build Main
$MARKERS = Array (		
	'G_SEASONS'	=> $SEASONS,
	
	'ALL_WINS' => $statsALL['wins'],
	'ALL_LOSSES' => $statsALL['losses'],
	'ALL_WIN_RATE' => ($statsALL['losses'])?(round($statsALL['wins']*100/($statsALL['wins']+$statsALL['losses']))):'-',
	'ALL_FRAGS' => $statsALL['frags'],
	'ALL_DEATHS' => $statsALL['deaths'],
	'ALL_FRAG_RATE' => ($statsALL['deaths'])?(round($statsALL['frags']/$statsALL['deaths'],2)):'-',
	'ALL_GAMES' => $statsALL['games'],

	'PLAYER_NAME' => $statsALL['name'],
	'PLAYER_ID' => $statsALL['playerID'],

	'L_SEASON'				=> $dict->data['season'],
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
	'L_AGO'					=> $dict->data['ago'],	
	'L_FRAGS'				=> $dict->data['frags'],
	'L_DEATHS'				=> $dict->data['deaths'],
	'L_FRAG_RATE'			=> $dict->data['frag_rate'],
	'L_RESULT'				=> $dict->data['result'],
	'L_NAME'				=> $dict->data['name'],
	'L_SCORE'				=> $dict->data['score'],
	'L_TIME'				=> $dict->data['time'],
	'L_TOTAL'				=> $dict->data['total'],
	'L_PLACE'				=> $dict->data['place'],
	'L_PLAYED'				=> $dict->data['played'],
	'L_ALL_SEASONS'			=> $dict->data['all_seasons'],
	'L_POINTS'				=> $dict->data['points'],
);
$template->assign_variables($MARKERS);
$TMPL_seasons .= $template->build('seasons') or die('error building: profile\seasons');
?>