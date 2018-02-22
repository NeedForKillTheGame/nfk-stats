<?php
if (!defined('NFK_LIVE')) die();

$template->load_template('mod_tourneys/tourneys_ladder');

// GTW: tour_ladder
$res = $db->select('*','tr_ladder','ORDER BY score DESC',false);
foreach($res as $row) {
	$MARKERS = Array (
			'PLACE' => ++$place,
			'PLAYER' => getPlayerName($row['playerID']),
			'SCORE'	=> $row['score'],
			'TOUR_WINS' => $row['tourWins'],
			'TOUR_NUM' => $row['tourNum'],
			'GAMES_WINS' => $row['wins'],
			'GAMES_LOSSES' => $row['losses'], 
			'GAMES_NUM' => $row['games']
		);
	$template->assign_variables($MARKERS);
	$TOUR_LADDER .= $template->build('tour_ladder') or die("error building: tourneys\tour_ladder");
}

$page_title = $dict->data['tour_ladder'];
$page_name = $page_title;

// GTW: main
$MARKERS = Array (
	'G_TOUR_LADDER' => $TOUR_LADDER,
	
	'L_TOUR_TITLE' => $dict->data['tour_title'],
	'L_PLAYER' => $dict->data['player'],
	'L_SCORE' => $dict->data['score'],
	'L_TOUR_WINS' => $dict->data['TOUR_WINS'],
	'L_TOUR_NUM' => $dict->data['TOUR_NUM'],
	'L_GAMES_NUM' => $dict->data['GAMES_NUM'],
	'L_GAMES_WINS' => $dict->data['GAMES_WINS'],
	'L_GAMES_LOSSES' => $dict->data['GAMES_LOSSES'],
	'L_TOUR_LADDER' => $dict->data['tour_ladder'],

);
?>