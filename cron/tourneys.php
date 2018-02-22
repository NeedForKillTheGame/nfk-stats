<?php
if (!defined("NFK_LIVE")) define("NFK_LIVE", true);

require_once("../inc/config.inc.php");
require_once("../inc/functions.inc.php");
require_once("../inc/classes.inc.php");
$db = new db();
$db->connect($CFG['db_host'], $CFG['db_login'], $CFG['db_pass'], $CFG['db_name'], $CFG['db_prefix']);

$action = $_GET['action'];

switch ($action) {
	case "tourney-start":
		function getSlotsArray($slotsNum, $full = false) {
			$stages = log($slotsNum,2); 
			$fmod = fmod($stages, 1);
			$y = explode('.', $fmod);
			if (!empty($y[1])) return -1;
			$slot[1][1]=1;
			$slot[1][2]=2;
			$stages = (int)"$stages";
			for($i=1; $i<=$stages; $i++){ 
				$slots = pow(2,$i);
				$games = $slots/2;
				for($j=1; $j<=$games; $j++){
					if ($j == 1) {
						$slot[$i][1]=1;
						$slot[$i][2] = $slots;
					} else {
						if ($i>1) $slot[$i][$j*2] = $slot[$i-1][$j];
						$x = $slot[$i][$j*2];
						$slot[$i][($j*2)-1] = ($slots+1)-$x;
					}
				}
				ksort($slot[$i]);
			}
			if ($full) return $slot;
				  else return $slot[$stages];
		}
		$nowDate = date('Y-m-d H:i:s');
		$res = $db->select('*','tr_tourneys',"WHERE status=0 AND dateStart<='$nowDate'",false);
		if (count($res)<1) break;
		$tour = $res[0];
		$tourID = $tour['tourID'];
		/*$players = $db->select('playerID, score','tr_playersReg',
								"LEFT JOIN nfkLive_ladderDUEL USING (playerID)
								WHERE tourID=$tourID AND status=1 
								ORDER BY rank DESC",false);*/
		$players = $db->select('playerID, score','tr_playersReg',
								"LEFT JOIN tr_ladder USING (playerID)
								WHERE tourID=$tourID AND status=1 
								ORDER BY score DESC",false);
		$playersNum = count($players);
		
		if ($playersNum < 4) {
			$db->update('tr_tourneys','status = 4', "WHERE tourID=$tourID",false);
			// Создаем новый турнир
			$db->insert('tr_tourneys',
				Array (
					'title' => "'$tour[title]'",
					'tourNum' => $tour['tourNum']+1,
					'dateStart' => "'$tour[dateStart]' + INTERVAL 7 DAY",
					'dateCheckin' => "'$tour[dateCheckin]' + INTERVAL 7 DAY",
					'dateReg' => "'$tour[dateReg]' + INTERVAL 7 DAY",
					'mapList' => "'".implode(',', getNewMapList())."'",
				),false);
			break;
		} else {
			$slotsNum = $playersNum;
			$y = explode('.', fmod(log($slotsNum,2), 1));
			while(!empty($y[1])) {
				$players[] = Array('playerID' => 0, 'score' => 0);
				$slotsNum++;
				$y = explode('.', fmod(log($slotsNum,2), 1));
			}
			$slots = getSlotsArray($slotsNum);
			if ($slots == -1) die('getSlotsArray error');
			$stage = (int)"".log($slotsNum,2); 
			$games = pow(2,$stage)/2;
			$db->update('tr_tourneys',"status=2, stages=$stage, playersNum=$slotsNum", "WHERE tourID=$tourID",false);
			$freeSlotsBoth = False;
			for($i=1; $i<=$games; $i++){
				// Создаем матч
				$matchID = $db->insert('tr_matches',
					Array( 'tourID' => $tourID, 'stage' => $stage,
							'game' => $i, 'status' => 1 ),False);
				if ($matchID>0) {
					// Получаем ID игроков
					if ($i==1) {
						$playerID[1] = $players[$slots[1]-1]['playerID'];
						$playerID[2] = $players[$slots[2]-1]['playerID'];
					} else {
						$playerID[1] = $players[$slots[($i*2)-1]-1]['playerID'];
						$playerID[2] = $players[$slots[$i*2]-1]['playerID'];
					}
					
					// Для каждого игрока
					for ($j=1; $j<=2; $j++) {
						// Если это не пустой слот, создаем запись в рейтинге, если необходимо
						if ($playerID[$j] <> 0)
							$db->insert2('tr_ladder',Array('playerID' => $playerID[$j]),
									"ON DUPLICATE KEY UPDATE tourNum=tourNum+1");
						
						// Добавляем игрока в матч
						$db->insert('tr_matchData', 
							Array('matchID' => $matchID, 'playerID' => $playerID[$j], 'greedPos' => $j),False);
					}
					// Есть пустые слоты?
					if ($playerID[1] == 0 or $playerID[2] == 0) {
						// Да
						// Закрываем матч
						$db->update('tr_matches',"status=2","WHERE matchID=$matchID",false);
						// Продвигаем игрока дальше
						$new_stage = $stage - 1;
						$new_game = ceil($i/2);
						// Обновляем или создаем матч?
						$res = $db->select('matchID','tr_matches',
									"WHERE tourID = $tourID AND stage = $new_stage AND game = $new_game",False);
						if (count($res) == 0) {
							// Создаем
							$nextMatchID = $db->insert('tr_matches',
								Array (
									'tourID' => $tourID,
									'stage' => $new_stage,
									'game' => $new_game,
									'status' => 0
								),False);
						} else {
							// Обновляем
							$nextMatchID = $res[0]['matchID'];
							$db->update('tr_matches',"status=1","WHERE matchID=$nextMatchID",False);
						}
						// Добавляем игрка в матч
						$db->insert('tr_matchData',
							Array (
								'matchID' => $nextMatchID,
								'playerID' => ($playerID[1] == 0) ? $playerID[2] : $playerID[1],
								'greedPos' => ($i & 1) ? 1 : 2,
								'score' => 0
							),False);
						if ($playerID[1] == $playerID[2]) {
							// Оба пустые слоты
							$freeSlotsBoth = True;
							// Возможно ли такое?
						}
					}
				}
			}
		}
	break;
	default: die("Hello World!");
}