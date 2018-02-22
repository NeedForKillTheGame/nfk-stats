<?php 
// Переменные
// $tourneys [tourID, stages, title, tourNum, dateStart, dateReg, dateCheckin, mapList]

// Получаем ID игроков
$res = $db->select('playerID, win, map','matchData',"
					INNER JOIN nfkLive_matchList USING ( matchID )
					WHERE nfkLive_matchList.matchID=$gameID");
if (count($res)<>2) break;
$matchMap = $res[0]['map'];
$playerID1 = $res[0]['playerID'];
$playerID2 = $res[1]['playerID'];
if ($res[0]['win']==1) {
	$winnerID = $playerID1;
} else if ($res[1]['win']==1) {
	$winnerID = $playerID2;
}
//$winnerID = ($res[0]['win']==1) ? $playerID1 : ($res[1]['win']==1) ? $playerID2 : 0;
if ($winnerID == 0) break;

// В каждом открытом турнире проверяем матч
foreach($tourneys as $tourney){
	// MT_WAITING = 0; MT_STARTED = 1; MT_ENDED = 2;
	// Засчитывать этот матч?
	$res = $db->select('*','tr_matches',
		"INNER JOIN tr_matchData USING (matchID)
		WHERE status = 1 AND tourID = $tourney[tourID] AND (playerID = $playerID1 OR playerID = $playerID2)
		",false);
	if (count($res) <> 2) continue;
	$players[1] = $res[0];
	$players[2] = $res[1];
	
	if ($players[1]['matchID'] <> $players[2]['matchID']) continue;
	
	$wrongMap = True;
	$mapList = explode(',', $tourney['mapList']);
	if (in_array($matchMap,$mapList))
		$wrongMap = False;

	$mapList = explode(',', $res[0]['mapList']);
	if (in_array($matchMap,$mapList))
		$wrongMap = True;
	
	if ($wrongMap) continue;
	unset($res);
	// Да, засчитывать
	// Добавляем игру в список игр для этого матча
	$matchID = $players[1]['matchID'];
	$db->update('tr_matches',"gameIDs = CONCAT(gameIDs,'$gameID,'),
								mapList = CONCAT(mapList,'$matchMap,')",
					"WHERE matchID=$matchID",false);
	// Работа с игроками
	$recWins = Array (1 => 3, 2 => 2, 3 => 2, 4 => 2, 5 => 2);
	$score_arr = Array (1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5);
	foreach ($players as $plr) {
		$is_winner = $winnerID == $plr['playerID'];
		if ($is_winner) {
			$addwin = 1; 
			$addlose = 0;
			$was_stages = $tourney['stages'] - ($plr['stage']-1);
			$addscore = $score_arr[$was_stages];
			if ($addscore < 0) $addscore = 0;
		} else {
			$addwin = 0;
			$addlose = 1; 
			$addscore = 0;
		}
		// Обновляем статистику в рейтинге
		$db->update('tr_ladder' ,"games = games + 1, wins = wins + $addwin, 
								losses = losses + $addlose, score = score + $addscore,
								lastGame = NOW()"
							,"WHERE playerID=$plr[playerID]" ,false);
		// Если не победитель, то выходим
		if (!$is_winner) continue; 
		// Только для победителя
		$plr['score']++;
		// Если игрок набрал необходимое число побед
		if ($plr['score'] >= $recWins[$plr['stage']]) {
			// Закрываем текущий матч
			$db->update('tr_matchData',"score=$plr[score]","WHERE dataID=$plr[dataID]",false);
			$db->update('tr_matches',"status=2","WHERE matchID=$matchID",false);
			// Продвигаем его вперед, если это не финал
			if ($plr['stage'] > 1) {
				$tourID = $plr['tourID'];
				$new_stage = $plr['stage'] - 1;
				$new_game = ceil($plr['game']/2);
				// Обновляем или создаем матч?
				$res = $db->select('matchID','tr_matches',
							"WHERE tourID = $tourID AND stage = $new_stage AND game = $new_game",false);
				if (count($res) == 0) {
					// Создаем
					$nextMatchID = $db->insert('tr_matches',
						Array (
							'tourID' => $plr['tourID'],
							'stage' => $new_stage,
							'game' => $new_game,
							'status' => 0
						),false);
				} else {
					// Обновляем
					$nextMatchID = $res[0]['matchID'];
					$db->update('tr_matches',"status=1","WHERE matchID=$nextMatchID",false);
				}
				// Добавляем игрка в матч
				$db->insert('tr_matchData',
					Array (
						'matchID' => $nextMatchID,
						'playerID' => $plr['playerID'],
						'greedPos' => ($plr['game'] & 1) ? 1 : 2,
						'score' => 0
					),false);
			} else {
				// Это финал
				// Обновояем число побед у победителя
				$db->update('tr_ladder',"tourWins = tourWins + 1","WHERE playerID=$winnerID",false);
				// Закрываем турнир, указываем победителя
				$db->update('tr_tourneys',
							"status = 3, winnerID = $plr[playerID], dateEnd = NOW()",	
							"WHERE tourID=$plr[tourID]",false);
				// Создаем новый турнир
				$db->insert('tr_tourneys',
					Array (
						'title' => "'$tourney[title]'",
						'tourNum' => $tourney['tourNum']+1,
						'dateStart' => "'$tourney[dateStart]' + INTERVAL 7 DAY",
						'dateCheckin' => "'$tourney[dateCheckin]' + INTERVAL 7 DAY",
						'dateReg' => "'$tourney[dateReg]' + INTERVAL 7 DAY",
						'mapList' => "'".implode(',', getNewMapList())."'",
					),false);
			}
		} else {
			// Если нет, то обновляем число побед у игрока
			$db->update('tr_matchData',"score=$plr[score]","WHERE dataID=$plr[dataID]",false);
		}
	}
	break;
}
?>