<?php
if (!defined('NFK_LIVE')) die();

// Построение сетки
$template->load_template('mod_tourneys/tourneys_full');

$tourID = (int)$PARAMSTR[2];

// Запрос
$tour = $db->select("*","tr_tourneys","WHERE tourID = $tourID", false);
if (count($tour)<>1) {die('error');}
$tour = $tour[0];
// map list
$maps = explode(',',$tour['mapList']);
foreach ($maps as $map) {
	$MAP_LIST .= "<span class='map'>$map</span>, "; 
}
$MAP_LIST = substr($MAP_LIST, 0, -2);
// GTW: if_stage
$if_grid = $tour['status'] >= 2 and $tour['status']<4;
if ($if_grid) {
	// Настройка сетки
	$players = $tour['checkNum'];
	
	$stage = log($slotsNum,2); 
	//$games = pow(2,$stage)/2;

	$stages_num = $tour['stages'];
	//$games = array( 5 => 16, 4 => 8, 3 => 4, 2 => 2, 1 => 1 );
	$delimID = 0;

	// Запрос
	$matchesList = $db->select("*","tr_matches",
				"INNER JOIN tr_matchData on tr_matchData.matchID = tr_matches.matchID
				WHERE tr_matches.tourID = $tourID", false);

	foreach($matchesList as $match) {
		$s = $match['stage'];
		$g = $match['game'];
		if ($match['greedPos']=='top') 
			$test[$s][$g][1] = $match;
		else $test[$s][$g][2] = $match;
	}

	// GTW: stage 
	for ($i=$stages_num; $i>=1; $i--){
		$delimID++;
		$MATCHES = '';
		$games = pow(2,$i)/2;
		for ($n=1; $n<=$games; $n++){
			$player = $test[$i][$n];
			$pID1 = $player[1]['playerID'];
			$pID2 = $player[2]['playerID'];
			if (empty($player[1]) or (empty($player[2]))) {
				$score1_css = 'looser';
				$score1_score = '';
				$player1_name = getPlayerName($pID1);
				$score2_css = 'looser';
				$score2_score = '';
				$player2_name = getPlayerName($pID2);
			} else {
				$score1_css = ($player[1]['score']>$player[2]['score']) ? 'winner' : 'looser';
				$score1_score = $player[1]['score'];
				$player1_name = getPlayerName($pID1);
				$score2_css = ($player[2]['score']>$player[1]['score']) ? 'winner' : 'looser';
				$score2_score = $player[2]['score'];
				$player2_name = getPlayerName($pID2);
			}
			if ($pID1 == 0 and !empty($player[1])) {
				$score1_css = 'looser';
				$score1_score = '';
				$player1_name = '[Free slot]';
				$score2_css = 'winner';
				$score2_score = '';
			}
			if ($pID2 == 0 and !empty($player[2])) {
				$score2_css = 'looser';
				$score2_score = '';
				$player2_name = '[Free slot]';
				$score1_css = 'winner';
				$score1_score = '';
			}
			
			$gameList = explode(',', $player[1]['gameIDs']);
			$gameNum = 0;
			$GAME_LIST = '';
			foreach ($gameList as $gameID) {
				if ($gameID <> '' and $gameID <> 0) {
					$gameNum++;
					$GAME_LIST .= "<a href='/match/$gameID'>#$gameNum</a>, ";
				}
			}
			$GAME_LIST = substr($GAME_LIST, 0, -2);
			
			// if_delim
			$template->assign_variables(Array('GTW_LOGIC'=>$games <> $n));
			$IF_DELIM = $template->build('if_delim');
				
			$MARKERS2 = Array (
				'IF_DELIM' => $IF_DELIM,
				
				'PLAYER1_NAME' => $player1_name,
				'SCORE1_CSS' => $score1_css,
				'SCORE1_NUM' => $score1_score,
				'PLAYER2_NAME' => $player2_name,
				'SCORE2_CSS' => $score2_css,
				'SCORE2_NUM' => $score2_score,
				'GAME_LIST' => $GAME_LIST,
				'DELIM_ID' => $delimID,
			);
			$template->assign_variables($MARKERS2);
			$MATCHES .= $template->build('match') or die("error building: tourneys\match");
		}
		
		$MARKERS = Array (
			'GTW_LOGIC'	=> true,
			'G_MATCHES'	=> $MATCHES,
			'DELIM_ID' => $delimID,
			'UPPERDELIM_ID' => $delimID-1,
			'STAGE_ID' => $i,
		);
		$template->assign_variables($MARKERS);
		$STAGES .= $template->build('if_stage') or die("error building: tourneys\if_stage");
	}
} else {
	$nowDate = date('Y-m-d H:i:s');
	
	// regPlayers
	$res = $db->select("*","tr_playersReg","WHERE tourID = $tourID AND status = 0", false);
	foreach ($res as $plr){
		$regPlayers .= getPlayerName($plr['playerID']).', ';
	}
	
	// checkPlayers
	$res = $db->select("*","tr_playersReg","WHERE tourID = $tourID AND status = 1", false);
	foreach ($res as $plr){
		$checkPlayers .= getPlayerName($plr['playerID']).', ';
	}
	
	// if_canreg
	//$template->assign_variables(Array('GTW_LOGIC'=>(date($tour['dateReg'])<=$nowDate)and(date($tour['dateStart'])>=$nowDate)));
	$template->assign_variables(Array('GTW_LOGIC'=>($tour['status']==0)and(date($tour['dateReg'])<=$nowDate)));
	$IF_CANREG = $template->build('if_canreg');
	
	// if_user
	$template->assign_variables(Array('GTW_LOGIC'=>($xdata['login']<>'')and($xdata['playerID']<>0)));
	$IF_USER = $template->build('if_user');
	
	// if_registred
	if ($xdata['playerID'] <> '') {
		$res = $db->select('*','tr_playersReg',"WHERE tourID = $tour[tourID] AND playerID=$xdata[playerID]",false);
	} else $res = null;
	$template->assign_variables(Array('GTW_LOGIC'=>count($res)==1));
	$IF_REGISTRED = $template->build('if_registred');
	
	// if_cancheck
	$template->assign_variables(Array('GTW_LOGIC'=>(date($tour['dateCheckin'])<=$nowDate)and(date($tour['dateStart'])>=$nowDate)));
	$IF_CANCHECK = $template->build('if_cancheck');
	
	// if_checked
	if ($xdata['playerID'] <> '') {
		$res = $db->select('*','tr_playersReg',"WHERE tourID = $tour[tourID] AND playerID=$xdata[playerID] AND status = 1",false);
	} else $res = null;
	$template->assign_variables(Array('GTW_LOGIC'=>count($res)==1));
	$IF_CHECKED = $template->build('if_checked');
	
	$MARKERS = Array (
		'GTW_LOGIC'	=> false,
		'IF_CANREG' => $IF_CANREG,
		'IF_USER' => $IF_USER,
		'IF_REGISTRED' => $IF_REGISTRED,
		'IF_CANCHECK' => $IF_CANCHECK,
		'IF_CHECKED' => $IF_CHECKED,
		'REG_PLAYERS' => substr($regPlayers, 0, -2),
		'CHECK_PLAYERS' => substr($checkPlayers, 0, -2),
	);
	$template->assign_variables($MARKERS);
	$STAGES .= $template->build('if_stage') or die("error building: tourneys\if_stage");
}

// if_tour_failed
$template->assign_variables(Array('GTW_LOGIC'=>$tour['status']==4));
$IF_TOUR_FAILED = $template->build('if_tour_failed');

// Опции доступные пользователю на турнире
if ($xdata['playerID']) {
	$res = $db->select('playerID','tr_playersReg',"WHERE tourID = $tour[tourID] AND status = 1 AND playerID = $xdata[playerID]",false);
	$tourOptions = $tour['status'] == 2 && $xdata['playerID']<>0 && count($res) == 1;
	if ($tourOptions) {
		$template->assign_variables(Array('GTW_LOGIC'=> true));
		$IF_CAN_LEAVE = $template->build('if_can_leave');
		$template->assign_variables(Array('GTW_LOGIC'=> true));
		$IF_CAN_REPORT = $template->build('if_can_report');
	}
} else $tourOptions = false;
// if_tour_options
$template->assign_variables(Array(
	'GTW_LOGIC' => false,//$tourOptions,
	'IF_CAN_LEAVE' => $IF_CAN_LEAVE,
	'IF_CAN_REPORT' => $IF_CAN_REPORT,
	'L_LEAVE_TOUR' => $dict->data['LEAVE_TOUR'],
	'L_REPORT_TOUR' => $dict->data['REPORT_TOUR'],
	));
$IF_TOUR_OPTIONS = $template->build('if_tour_options');

// Подключение комментариев
$res = $db->select("*","comments","WHERE materialID = $tour[tourID] AND moduleID = 3 ORDER BY cmtID DESC");
// GTW: comment
$cmtnum = count($res);
foreach ($res as $row) {
	if ($row['playerID']<>0) $plr = getPlayer($row['playerID']);
	$MARKERS = Array(
			"CMT_AUTHOR"		=> ($row['playerID']<>0) ? getIcons($plr):getIcons($row,false,false,false),
			"CMT_DATE"			=> $row['postTime'],
			"COMMENT"			=> $row['comment'],
			"CMT_NUM"			=> $cmtnum--,
			"CMT_DELETE"		=> ($xdata['access']>=3) ? "<a href='/do/comment/delete/$row[cmtID]/$row[materialID]'><img src='$THEME_ROOT/images/delete_ico.gif' /></a>" : "",
		);
	$template->assign_variables($MARKERS);
	$materialComments .= $template->build('comment') or die("error building: match\comment");
}

// GTW: if_have_comments
$MARKERS_IF = Array
	(
		"GTW_LOGIC"			=> (count($res)>0) ? (true) : (false),
		"G_MATERIAL_COMMENTS"	=> $materialComments,
	);

$template->assign_variables($MARKERS_IF);
$IF_HAVE_COMMENTS = $template->build('if_have_comments') or die("error building: tour\if_have_comments");

$template->assign_variables(Array('GTW_LOGIC'=> $xdata['playerID'] <> 0));
$IF_LOGGED = $template->build('if_logged') or die("error building: tour\if_logged");

// ban comments
$banned = false;
$ipLong = ip2long($_SERVER['REMOTE_ADDR']);
$ipLong = sprintf("%u", $ipLong);
$res = $db->select('*','bans',"WHERE banLevel=2 AND (banMaskStart < '$ipLong' AND banMaskEnd > '$ipLong') AND (banEnd>NOW()) LIMIT 1");
if (count($res) > 0) {
	$banned = true;
	$ban = $res[0];
	$BAN_MSG = ("<div align='center'><b>You can not post comments! Ban expire at $ban[banEnd]<br>Reason: $ban[banReas]</b></div>");
}
$MARKERS_IF = Array
	(
		"GTW_LOGIC"			=> !$banned,
		'IF_LOGGED' => $IF_LOGGED,
		'MATERIAL_ID'		=> $tourID,
		'BAN_MSG' => $BAN_MSG
	);
$template->assign_variables($MARKERS_IF);
$IF_CAN_CMT = $template->build('if_can_cmt') or die("error building: match\if_can_cmt");


$page_title = ($tour['title']<>'') ? $tour['title'].' #'.$tour['tourNum'] : 'Tourney not found';
$page_name = $page_title;

// GTW: main
$MARKERS = Array (
	'G_STAGES' => $STAGES,
	'IF_HAVE_COMMENTS' => $IF_HAVE_COMMENTS,
	
	'IF_TOUR_OPTIONS' => $IF_TOUR_OPTIONS,
	'IF_TOUR_FAILED' => $IF_TOUR_FAILED,
	'IF_CAN_CMT' => $IF_CAN_CMT,
	
	'TOURNEY_TITLE' => ($tour['title']<>'') ? $tour['title'].' #'.$tour['tourNum'] : 'Tourney not found',
	'TOURNEY_ID' => $tourID,
	'TOUR_DATE_CHECK' => $tour['dateCheckin'],
	'TOUR_DATE_START' => $tour['dateStart'],
	'TOUR_DATE_REG' => $tour['dateReg'],
	'DATE_REG' => _countTime(strtotime($tour['dateReg']),$CUR_LANG),
	'TOUR_DATE_REG' => $tour['dateReg'],
	'DATE_START' => _countTime(strtotime($tour['dateStart']),$CUR_LANG),
	'MAP_LIST'	=> $MAP_LIST,
	'MODULE_ID' => 3,
	'MATERIAL_ID' => $tourID,
	
	'L_YOU_ARE_CHECKED' => $dict->data['YOU_ARE_CHECKED'],
	'L_TOUR_START_AT' => $dict->data['TOUR_START_AT'],
	'L_CHECKIN' => $dict->data['CHECKIN'],
	'L_REGISTER_ON_TOUR' => $dict->data['REGISTER_ON_TOUR'],
	'L_ONLY_FOR_LOGINED' => $dict->data['ONLY_FOR_LOGINED'],
	'L_REG_LIST' => $dict->data['REG_LIST'],
	'L_CHECK_LIST' => $dict->data['CHECK_LIST'],
	'L_CHECKIN_START_AT' => $dict->data['CHECKIN_START_AT'],
	'L_CHECKIN_TO' => $dict->data['CHECKIN_TO'],
	'L_MOSСOW_TIME' => $dict->data['MOSСOW_TIME'],
	'L_MAP_LIST' => $dict->data['MAP_LIST'],
	'L_REG_START_IN' => $dict->data['REG_START_IN'],
	'L_ADD_COMMENT' => $dict->data['add_comment'],
	'L_ADD' => $dict->data['add'],
	'L_NAME' => $dict->data['name'],
	'L_TOUR_FAILED' => $dict->data['TOUR_FAILED'],
);
?>