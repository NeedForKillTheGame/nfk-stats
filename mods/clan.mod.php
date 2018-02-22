<?php
if (!defined('NFK_LIVE')) die();

if (is_numeric($PARAMSTR[2]))
	$clanID = $PARAMSTR[2];
else header('Location: /clans');

$template->load_template('mod_clan');

$clan = $db->select('*','clanList',"WHERE clanID=$clanID");
$clan = $clan[0];

$res = $db->select('clanScore, playerID, name, clanID, clanGames, country, model','playerStats',"WHERE clanID=$clanID ORDER BY clanScore DESC");

// GTW: player
foreach ($res as $row) {
	$template->assign_variables(Array('GTW_LOGIC' => $clan['leaderID'] == $row['playerID']));
	$IF_LEADER = $template->build('if_leader');
	$MARKERS = Array(
		'IF_LEADER'			=> $IF_LEADER,
		'PLAYER_ID'			=> $row['playerID'],
		'PLAYER_NAME'		=> getIcons($row),//clearName($row['name']),
		'PLAYER_SCORE'		=> $row['clanScore'],
		'PLAYER_GAMES'		=> $row['clanGames'],
		'LEADER_ICO'		=> '',
		'PLACE'				=> ++$place,
	);
	$template->assign_variables($MARKERS);
	$players .= $template->build('player') or die('error building: clans\player');
}

$page_title = $dict->data['clan'].': '.$clan['clanName'];
$page_name = $page_title;

// GTW: if_clan_leader
if (($clan['leaderID'] == $xdata['playerID']) or ($xdata['access'])==3) {
	$MARKERS_IF = Array
		(
			'GTW_LOGIC'				=> true,
			
			'L_CLAN_OPTIONS'		=> $dict->data['clan_options'],
			'L_ADD_PLAYER'			=> $dict->data['add_player'],
			'L_PLAYER_NAME'			=> $dict->data['by_plr_name'],
			'L_OR'					=> $dict->data['or'],
			'L_PLAYER_ID'			=> $dict->data['by_plr_id'],
			'L_CHANGE_LEADER'		=> $dict->data['change_clan_leader'],
			'L_CHANGE'				=> $dict->data['change'],
			'L_REMOVE_PLAYER'		=> $dict->data['remove_player'],
			'L_REMOVE'				=> $dict->data['remove'],
		);
} else {
	$MARKERS_IF = Array
		(
			'GTW_LOGIC'				=> false,
			'NULL'					=> '',
		);
}
$template->assign_variables($MARKERS_IF);
$caln_options = $template->build('if_clan_leader') or die('error building: clans\if_clan_leader');

//
// Build Main
//
$MARKERS = Array
	(
		'G_PLAYERS'			=> $players,
		'CLAN_OPTIONS'		=> $caln_options,
		
		'CLAN_SCORE'		=> $clan['score'],
		'CLAN_ID'			=> $clan['clanID'],
		
		'L_NAME'			=> $dict->data['name'],
		'L_GAMES'			=> $dict->data['games'],
		'L_SCORE'			=> $dict->data['score'],
		'L_PLAYERS'			=> $dict->data['players'],
		'L_CLAN_SCORE'		=> $dict->data['clan_score'],
		'L_POINTS'			=> $dict->data['points'],
	);
	
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die('error building: clans\main');

?>