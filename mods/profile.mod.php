<?php
if (!defined('NFK_LIVE')) die(); 
//$template->load_template('mod_profile');

if (is_numeric($PARAMSTR[2])) {
	$targetUsr = $PARAMSTR[2];
} else if (is_numeric($xdata['playerID'])) {
	$targetUsr = $xdata['playerID'];
} else header('Location: /');

$plr = $player->fetchId($targetUsr) or header('Location: ?/');
$plr['name'] = html_entity_decode($plr['name']);
$plr['nick'] = html_entity_decode($plr['nick']);

$CUR_ADDRES .= $PARAMSTR[2].'/';

$a_pages = array('overall', 'matches', 'duelslist', 'seasons');
$profilePage = (in_array($PARAMSTR[3],$a_pages)) ? $PARAMSTR[3] : 'overall';

// Build Content
if ($profilePage == 'overall') require('inc/profile.overall.php');
if ($profilePage == 'matches') require('inc/profile.matches.php');
if ($profilePage == 'duelslist') require('inc/profile.duelslist.php');
if ($profilePage == 'seasons') require('inc/profile.seasons.php');

$page_name = $dict->data['player_profile'].' - '.getIcons($plr,false,true,true,true);

// Build Main
$MARKERS = Array(
	'OVERALL' => $TMPL_overall,
	'DUELSLIST' => $TMPL_duelslist,
	'MATCHES' => $TMPL_matches,
	'SEASONS' => $TMPL_seasons,

	'PLAYER_ID' => $plr['playerID'],
	
	'MY_ID' => $targetUsr,
	
	'L_FILL_MATCH_LIST' => $dict->data['full_match_list'],
	'L_FILL_DUEL_LIST' => $dict->data['full_duel_list'],
	'L_OVERALL_STATS' => $dict->data['overall_stats'],
	'L_SEASONS' => $dict->data['seasons'],
);
	
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die('error building: profile\main');
?>