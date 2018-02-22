<?php
if (!defined('NFK_LIVE')) die();

$template->load_template('mod_tourneys/tourneys_list');

// GTW: up_tourney
$res = $db->select('*','tr_tourneys','WHERE status < 3',false);
foreach($res as $tour) {
	if ($tour['status'] == 0) $status = 'Coming';
		else if ($tour['status'] == 1) $status = 'Check In';
		else if ($tour['status'] == 2) $status = 'Started';
		else if ($tour['status'] == 3) $status = 'Completed';
		else if ($tour['status'] == 4) $status = 'Failed';
	$MARKERS = Array (
			'TOURNEY_ID' => $tour['tourID'],
			'TITLE'	=> $tour['title'].' #'.$tour['tourNum'],
			'STATUS' => $status,
			'DATE_REG' => $tour['dateReg'],
			'DATE_CHECK' => $tour['dateCheckin'],
			'DATE_START' => $tour['dateStart'], 
			'REG_NUM' => $tour['regNum'],
			'CHECK_NUM' => $tour['checkNum'] 
		);
	$template->assign_variables($MARKERS);
	$UP_TOURNEYS .= $template->build('up_tourney') or die("error building: tourneys\up_tourney");
}

// GTW: last_tourney
$res = $db->select('*','tr_tourneys','WHERE status = 3  ORDER BY dateStart DESC',false);
foreach($res as $tour) {
	$MARKERS = Array (
			'TOURNEY_ID' => $tour['tourID'],
			'TITLE'	=> $tour['title'].' #'.$tour['tourNum'],
			'PLAYERS_NUM' => $tour['regNum'],
			'PLAYERS_CHECK' => $tour['checkNum'] ,
			'DATE_END' => ($tour['dateEnd'])?$tour['dateEnd']:'&mdash;',
			'WINNER' => ($tour['winnerID']<>0)?getPlayerName($tour['winnerID']):'&mdash;',
		);
	$template->assign_variables($MARKERS);
	$LAST_TOURNEYS .= $template->build('last_tourney') or die("error building: tourneys\last_tourney");
}

$page_title = $dict->data['tourneys'];
$page_name = $page_title;

// GTW: main
$MARKERS = Array (
	'G_UP_TOURNEYS' => $UP_TOURNEYS,
	'G_LAST_TOURNEYS' => $LAST_TOURNEYS,
	
	'L_TOUR_TITLE' => $dict->data['tour_title'],
	'L_STATUS' => $dict->data['tour_status'],
	'L_DATE_REG' => $dict->data['tour_date_reg'],
	'L_DATE_CHECKIN' => $dict->data['tour_date_check'],
	'L_DATE_START' => $dict->data['tour_date_start'],
	'L_DATE_END' => $dict->data['tour_date_end'],
	'L_REG_NUM' => $dict->data['tour_reg_num'],
	'L_CHECK_NUM' => $dict->data['tour_check_num'],
	'L_PLAYERS_NUM' => $dict->data['tour_plr_num'],
	'L_WINNER' => $dict->data['tour_winner'],
	'L_UPCOMING_TOURNEYS' => $dict->data['tour_upcoming'],
	'L_LAST_TOURNEYS' => $dict->data['tour_last'],
);
?>