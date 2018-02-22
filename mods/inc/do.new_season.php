<?php
if (!defined("NFK_LIVE")) die("error");


if (is_numeric($PARAMSTR[3])) {
	
	// Получаем текущий сезон
	$res = $db->select('seasNum','seasons','ORDER BY seasNum DESC');
	if (count($res)<1) die('Seasons not found');
	$curSeas = $res[0]['seasNum'];
	if ($curSeas<1) die('Invalid seas num');
	//die('test '.$curSeas);
	//$oldsid = $PARAMSTR[3]-1;

	//DM
	
	$res = $db->select('*', 'ladderDM', '');
	foreach ($res as $row) {
		$score = $db->select2('DmReiting as score', 'AltStat_Players', "WHERE Playerid=$row[playerID]");
		if (!$score) continue;
		$score = $score[0]['score'];
		if ($row['games']=='0') {
			$rank=-1;
			continue;
		} else {
			$r_rank = $db->select2('COUNT(*) AS Rank', 'AltStat_Players', "WHERE DmReiting > $score");
			$rank=$r_rank[0]['Rank']+1;
		}
		$db->insert2('st_seasons_dm', Array(
				'id'		=> $row['tableID'],
				'playerID'		=> $row['playerID'],
				'frags'		=> $row['frags'],
				'deaths'	=> $row['deaths'],
				'games'		=> $row['games'],
				'wins'		=> $row['wins'],
				'losses'	=> $row['losses'],
				'time'		=> $row['time'],
				'season'	=> $curSeas,
				'score'		=> $score,
				'place'		=> $rank,
			));	
	}
	
	
	//TDM
		
	$res = $db->select('*', 'ladderTDM', '');
	foreach ($res as $row) {
		$score = $db->select2('TdmReiting as score', 'AltStat_Players', "WHERE Playerid=$row[playerID]");
		if (!$score) continue;
		$score = $score[0]['score'];
		if ($row['games']=='0') {
			$rank=-1;
			continue;
		} else {
			$r_rank = $db->select2('COUNT(*) AS Rank', 'AltStat_Players', "WHERE TdmReiting > $score");
			$rank=$r_rank[0]['Rank']+1;
		}
		$db->insert2('st_seasons_tdm', Array(
				'id'		=> $row['tableID'],
				'playerID'		=> $row['playerID'],
				'frags'		=> $row['frags'],
				'deaths'	=> $row['deaths'],
				'games'		=> $row['games'],
				'wins'		=> $row['wins'],
				'losses'	=> $row['losses'],
				'time'		=> $row['time'],
				'season'	=> $curSeas,
				'score'		=> $score,
				'place'		=> $rank,
			));
				
	}
	
	//CTF
	$res = $db->select('*', 'ladderCTF', '');
	foreach ($res as $row) {
		$score = $db->select2('CtfReiting as score', 'AltStat_Players', "WHERE Playerid=$row[playerID]");
		if (!$score) continue;
		$score = $score[0]['score'];
		if ($row['games']=='0') {
			$rank=-1;
			continue;
		} else {
			$r_rank = $db->select2('COUNT(*) AS Rank', 'AltStat_Players', "WHERE CtfReiting > $score");
			$rank=$r_rank[0]['Rank']+1;
		}
		$db->insert2('st_seasons_ctf', Array(
				'id'		=> $row['tableID'],
				'playerID'		=> $row['playerID'],
				'frags'		=> $row['frags'],
				'deaths'	=> $row['deaths'],
				'games'		=> $row['games'],
				'wins'		=> $row['wins'],
				'losses'	=> $row['losses'],
				'time'		=> $row['time'],
				'season'	=> $curSeas,
				'score'		=> $score,
				'place'		=> $rank,
			));
				
	}
	
	//DOM
	$res = $db->select('*', 'ladderDOM', '');
	foreach ($res as $row) {
		$score = $db->select2('DomReiting as score', 'AltStat_Players', "WHERE Playerid=$row[playerID]");
		if (!$score) continue;
		$score = $score[0]['score'];
		if ($row['games']=='0') {
			$rank=-1;
			continue;
		} else {
			$r_rank = $db->select2('COUNT(*) AS Rank', 'AltStat_Players', "WHERE DomReiting > $score");
			$rank=$r_rank[0]['Rank']+1;
		}
		$db->insert2('st_seasons_dom', Array(
				'id'		=> $row['tableID'],
				'playerID'		=> $row['playerID'],
				'frags'		=> $row['frags'],
				'deaths'	=> $row['deaths'],
				'games'		=> $row['games'],
				'wins'		=> $row['wins'],
				'losses'	=> $row['losses'],
				'time'		=> $row['time'],
				'season'	=> $curSeas,
				'score'		=> $score,
				'place'		=> $rank,
			));
				
	}
	
	//DUEL
	
	$res = $db->select('*', 'ladderDUEL', '');
	foreach ($res as $row) {
		
		$score = $row['score'];
		if (!$score) continue;
		if ($row['games']=='0') {
			$rank=-1;
			continue;
		} else {
			$r_rank = $db->select('COUNT(*) AS Rank', 'ladderDUEL', "WHERE score > $score AND score<>-1 AND games<>0");
			$rank=$r_rank[0]['Rank']+1;
		}
		$db->insert2('st_seasons_duel', Array(
				'id'		=> $row['tableID'],
				'playerID'		=> $row['playerID'],
				'frags'		=> $row['frags'],
				'deaths'	=> $row['deaths'],
				'games'		=> $row['games'],
				'wins'		=> $row['wins'],
				'losses'	=> $row['losses'],
				'time'		=> $row['time'],
				'season'	=> $curSeas,
				'score'		=> $score,
				'place'		=> $rank,
			));
				
	}
	
	
	//CLANS
	
	$res = $db->select('*', 'clanList', 'ORDER BY score DESC');
	$place = 0;
	foreach ($res as $row) {
		
		
		$score = $row['score'];
		if (!$score) continue;
		$place++;
		$db->insert2('st_seasons_clan', Array(
				'clanID'	=> $row['clanID'],
				'seasonID'	=> $curSeas,
				'score'		=> $row['score'],
				'playersNum'=> $row['players'],
				'place'		=> $place,
			));
				
	}
	//die('test:'.$curSeas);
echo('DONE save season: '.$curSeas);


	$db->update('ladderCTF',"
				`frags` = '0',
				`deaths` = '0',
				`games` = '0',
				`wins` = '0',
				`losses` = '0',
				`time` = '0',
				`lastGame` = NULL",'');
	$db->update('ladderDM',"
				`frags` = '0',
				`deaths` = '0',
				`games` = '0',
				`wins` = '0',
				`losses` = '0',
				`time` = '0',
				`lastGame` = NULL",'');
	$db->update('ladderDUEL',"
				`frags` = '0',
				`deaths` = '0',
				`games` = '0',
				`wins` = '0',
				`losses` = '0',
				`time` = '0',
				`rank` = '1',
				`score`= '100',
				`lastGame` = NULL",'');
	$db->update('ladderTDM',"
				`frags` = '0',
				`deaths` = '0',
				`games` = '0',
				`wins` = '0',
				`losses` = '0',
				`time` = '0',
				`lastGame` = NULL",'');
	$db->update('ladderDOM',"
				`frags` = '0',
				`deaths` = '0',
				`games` = '0',
				`wins` = '0',
				`losses` = '0',
				`time` = '0',
				`lastGame` = NULL",'');
	$db->update2('AltStat_Players',"
				`CtfReiting` = '100',
				`TdmReiting` = '100',
				`DmReiting` = '100',
				`DomReiting` = '100',
				`AllRating` = '400'",'');
	$db->update('playerStats',"
				ClanScore = '0',
				ClanGames = '0'" ,'WHERE clanID <> 0');
	$db->update('clanList',"
				score = '0'",'');	
				
	Die('DONE season clear');
} else die('error');

if ($_G['do']=='seasonclear') { // 

}
?>