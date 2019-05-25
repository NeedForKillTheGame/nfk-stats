<?php
if (!defined("NFK_LIVE")) define("NFK_LIVE", true);
ini_set('display_errors',1);
error_reporting(E_ALL);
$G = $_GET;
//if (!$G['dllvers']) die("Hello World!");
//if ($G['dllvers']<45) die('ERROR:001 Your DLL Version is not suported on this server.');
$act = isset($G['action']) ? $G['action'] : false;
//if ($act == '') die('ERROR:002 Action cannot be empty');


// Configuration
require("inc/config.inc.php");
// Functions
require("inc/functions.inc.php");
// Classes
require("inc/classes.inc.php");
// db connect

if (LOG_RESPONSES)
{
	file_put_contents(RESPONSE_LOG_FILE, "\n\n--------------------------------\n[" . date("d.m.Y H:i:s") . "]\n" . var_export($_REQUEST, true), FILE_APPEND);
}


$db = new db();
$db->connect(
    $CFG['db_host'],
    $CFG['db_login'],
    $CFG['db_pass'],
    $CFG['db_name'],
    $CFG['db_prefix']
);
$uploadDir = "demos/";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['version']) && $_POST['version'] >= 99) {
        switch ($_POST['action']) {
            case 'addMatch':
                $info = json_decode($_POST['match']);
                $match = $info['match'];
                $players = $info['players'];
                $gameType = $match['gametype'];
				$match['hostname'] = $db->clean($match['hostname']);
                $db->insert('matchList', Array(
                    'hostName' => "'$match[hostname]'",
                    'map' => "'$match[map]'",
                    'gameType' => "'$match[gametype]'",
                    'timeLimit' => "'$match[timelimit]'",
                    'players' => "'$match[players]'",
                    'redScore' => isset($match['redscore']) ? "'$match[redscore]'" : "''",
                    'blueScore' => isset($match['redscore']) ? "'$match[bluescore]'" : "''",
                    'dateTime' => "NOW()",
                    'gameTime' => "'$match[matchtime]'",
                ));
                $res = $db->select("last_insert_id( ) as matchID","","");
                $matchID = $res[0]['matchID'];
                if (isset($_FILES['demo'])) {
                    $filename = iconv('CP1251','UTF-8', "{$matchID}_".basename($_FILES['demo']['name']));
                    $uploadFile = $uploadDir . $filename;
                    if (move_uploaded_file($_FILES['demo']['tmp_name'], $uploadFile)) {
                        $db->update("matchList","demo='$filename'","WHERE matchID='$matchID' LIMIT 1");
                    }
                }
                if ($players) {
                    foreach ($players as $player) {
                        $playerID = GetPID($player['name']);
                        $playerName = $db->clean(iconv('CP1251','UTF-8', $player['name']));
                        $playerNick = $db->clean(iconv('CP1251','UTF-8', $player['nick']));
                        // ADD NEW PLAYER
                        if (!$playerID) {
                            $db->insert("playerStats", Array(
                                'name'		=> "'$playerName'",
                                'regIP'		=> "'$player[ip]'",
                                'regDate'	=> "NOW()",
                                'country'	=> "'".ip2country($player['ip'])."'"
                            ));
                            $res = $db->select("last_insert_id( ) as playerID","","");
                            $playerID = $res[0]['playerID'];
                            $db->insert("ladderDOM", Array('playerID' => "'$playerID'",));
                            $db->insert("ladderDM", Array('playerID' => "'$playerID'",));
                            $db->insert("ladderTDM", Array('playerID' => "'$playerID'",));
                            $db->insert("ladderCTF", Array('playerID' => "'$playerID'",));
                            $db->insert("ladderDUEL", Array('playerID' => "'$playerID'",));
                        }
                        $win = 0;
                        $modScore = 0;
                        $lose = 0;
                        $db->insert("matchData", Array(
                            'matchID' => "'$matchID'", 'playerID' => "'$playerID'", 'frags'	=> "'$player[frags]'",
                            'deaths' => "'$player[deaths]'", 'team' => "'$player[team]'", 'win' => "'$win'",
                            'score' => "'$modScore'", 'ping' => "'$player[ping]'", 'time' => "'$player[time]'",
                            'IP' => "'$player[ip]'", 'suisides' => "'$player[suisides]'", 'dmgrecvd' => "'$player[dmgrecvd]'",
                            'dmggiven' => "'$player[dmggiven]'", 'bfg_hits'	=> "'$player[bfg_hits]'", 'bfg_fire' => "'$player[bfg_fire]'",
                            'impressives' => "'$player[impressives]'", 'excellents'	=> "'$player[excellents]'", 'humiliations' => "'$player[humiliations]'",
                            'gaun_hits' => "'$player[gaun_hits]'", 'mach_hits' => "'$player[mach_hits]'", 'shot_hits' => "'$player[shot_hits]'",
                            'gren_hits' => "'$player[gren_hits]'", 'rocket_hits' => "'$player[rocket_hits]'", 'shaft_hits'	=> "'$player[shaft_hits]'",
                            'plasma_hits' => "'$player[plasma_hits]'", 'rail_hits' => "'$player[rail_hits]'", 'mach_fire' => "'$player[mach_fire]'",
                            'shot_fire'	=> "'$player[shot_fire]'", 'gren_fire' => "'$player[gren_fire]'", 'rocket_fire'	=> "'$player[rocket_fire]'",
                            'shaft_fire' => "'$player[shaft_fire]'", 'plasma_fire' => "'$player[plasma_fire]'", 'rail_fire' => "'$player[rail_fire]'",
                            'redArmors' => "'$player[redarmors]'", 'yellowArmors' => "'$player[yellowarmors]'", 'megaHealthes' => "'$player[megahealthes]'",
                            'powerUps' => "'$player[powerups]'",
                        ));
                        $ladderTables = array(
                            'DOM' => 'ladderDOM', 'DM' => 'ladderDM', 'DUEL' => 'ladderDUEL', 'TDM' => 'ladderTDM', 'CTF' => 'ladderCTF',
                        );
                        $ladderTable = (isset($ladderTables[$gameType])) ? $ladderTables[$gameType] : null;
                        $duelUpdate = ($gameType == 'DUEL') ? ", rank=$G[rank], score=score+$G[modscore]" : null;
                        $db->update(
                            $ladderTable, "time=time+$G[time],frags=frags+$G[frags],deaths=deaths+$G[deaths],games=games+1,
						        wins=wins+$win,losses=losses+$lose,lastGame=NOW() $duelUpdate",
                            "WHERE `playerID` = '$playerID'"
                        );
                        // UPDATE CLAN SCORE
                        if ($gameType == 'DUEL') {
                            $clanID = $db->select("clanID","playerStats","WHERE `playerID`='$playerID'");
                            $clanID = $clanID[0]['clanID'];
                            if ($clanID) {
                                $db->update("clanList","score=score+$G[modscore]","WHERE clanID='$clanID'");
                                $updateClanScore = "clanScore=clanScore+$G[modscore], clanGames=clanGames+1,";
                            }
                        }
                        // UPDATE PLAYER STATS
                        $sumHits = $player['gaun_hits']+$player['mach_hits']+$player['shot_hits']+$player['gren_hits']+$player['rocket_hits']
                            +$player['shaft_hits']+$player['plasma_hits']+$player['rail_hits']+$player['bfg_hits'];
                        $sumFire = $player['gaun_fire']+$player['mach_fire']+$player['shot_fire']+$player['gren_fire']+$player['rocket_fire']
                            +$player['shaft_fire']+$player['plasma_fire']+$player['rail_fire']+$player['bfg_fire'];
                        $nfkModel = $player['model'];
                        $db->update("playerStats",
                            "time=time+$player[time], $gameType=$gameType+1, wins=wins+$win,
                                 losses=losses+$lose, games=games+1, frags=frags+$player[kills],
                                deaths=deaths+$player[deaths], hits=hits+$sumHits, shots=shots+$sumFire,
                                bfg_hits=bfg_hits+$player[bfg_hits], bfg_fire=bfg_fire+$player[bfg_fire],gaun_hits=gaun_hits+$player[gaun_hits],
                                mach_hits=mach_hits+$player[mach_hits],mach_fire=mach_fire+$player[mach_fire], lastGame = NOW(),
                                humiliations=humiliations+$player[humiliations],excellents=excellents+$player[excellents],impressives=impressives+$player[impressives],
                                shot_hits=shot_hits+$player[shot_hits], shot_fire=shot_fire+$player[shot_fire],
                                gren_hits=gren_hits+$player[gren_hits], gren_fire=gren_fire+$player[gren_fire],
                                rocket_hits=rocket_hits+$player[rocket_hits], rocket_fire=rocket_fire+$player[rocket_fire],
                                shaft_hits=shaft_hits+$player[shaft_hits], shaft_fire=shaft_fire+$player[shaft_fire],
                                plasma_hits=plasma_hits+$player[plasma_hits], plasma_fire=plasma_fire+$player[plasma_fire],
                                rail_hits=rail_hits+$player[rail_hits], rail_fire=rail_fire+$player[rail_fire],
                                gaun_kills=gaun_kills+$player[humiliations], mach_kills=mach_kills+$player[mach_kills],
                                shot_kills=shot_kills+$player[shot_kills], gren_kills=gren_kills+$player[gren_kills],
                                rocket_kills=rocket_kills+$player[rocket_kills], shaft_kills=shaft_kills+$player[shaft_kills],
                                plasma_kills=plasma_kills+$player[plasma_kills], rail_kills=rail_kills+$player[rail_kills],
                                bfg_kills=bfg_kills+$player[bfg_kills], $updateClanScore
                                model='$nfkModel', nick='$playerNick', lastIP = '$player[ip]'",
                            "WHERE `playerID` = '$playerID'");
                    }
                }
                break;
            default: die('invalid action');
        }
    }
}

switch ($act) {
	// UPDATE MATCH STATS
	case "ums":
		$hostName = $db->clean($G['hostname']);
		$map = $db->clean($G['map']);
		$db->insert('matchList', Array(
			'hostName'		=> "'$hostName'",
			'map'			=> "'$map'",
			'gameType'		=> "'$G[gametype]'",
			'timeLimit'		=> "'$G[timelimit]'",
			'players'		=> "'$G[players]'", 
			'redScore'		=> isset($G['redscore']) ? "'$G[redscore]'" :  "''",
			'blueScore'		=> isset($G['bluescore']) ? "'$G[bluescore]'" :  "''",
			'dateTime'		=> "NOW()",
			'gameTime'		=> "'$G[matchtime]'",
		));
		$mID = $db->select("last_insert_id( ) as mID","","");
		$mID = $mID[0]['mID'];
		if ($mID == '') die("Error: Match ID is null");
		die($mID);
	break;
	
	// GET PLAYER SCORE
	case 'score':
		$pname = $_GET['name'];
		$playerID = GetPID($pname);
		if (is_numeric($playerID)) {
			$stats = $db->select("score","ladderDUEL","WHERE `playerID`='$playerID'");
			die ($stats[0]['score']);
		} else die ("100");
	break;
	
	// ADD PLAYER
	case 'addpl':
		$name = $G['name'];
		$name = $db->clean(iconv('CP1251','UTF-8',$name));
		$server = $db->clean($G['server']);
		$res = $db->insert('onServers', Array(
			'serverName'	=> "'$server'",
			'playerName'	=> "'$name'",
			'dxid'			=> "'$G[dxid]'",
		));
		if ($res == -1) {
			$res = $db->delete('onServers', "dxid='$G[dxid]' LIMIT 1");
		};	
	break;
	
	// REMOVE PLAYER
	case 'delpl':
		$res = $db->delete('onServers', "dxid='$G[dxid]' LIMIT 1");	
		if ($res == 0) {
			$db->insert('onServers', Array(
				'serverName'	=> "'Null'",
				'playerName'	=> "'Null'",
				'dxid'			=> "'$G[dxid]'",
			));
		};	
	break;
	
	// REMOVE ALL PLAYERS
	case 'delallpl':
		$server = $db->clean($G['server']);
		$db->delete('onServers', "serverName='$server'");
	break;
	
	
	// REMOVE ALL PLAYERS + ADD PLAYERS FROM REQUEST
	case 'updallpl':
		// delete players for the server
		$server = $db->clean($G['server']);
		$db->delete('onServers', "serverName='$server'");
		
		// if no players
		if ( !isset($G['name']) )
			break;
	
		// add players
		foreach ($G['name'] as $i => $val)
		{
			$name = $val;
			$name = $db->clean(iconv('CP1251','UTF-8',$name));
			$dxid = $G['dxid'][$i];
			$res = $db->insert('onServers', Array(
				'serverName'	=> "'$server'",
				'playerName'	=> "'$name'",
				'dxid'			=> "'$dxid'",
			));
			if ($res == -1) {
				$res = $db->delete('onServers', "dxid='$dxid' LIMIT 1");
			};
		}
	break;
	
	
	// CHECK DUEL LIMITS
	case 'checkduel':
		//die("OK");
		// Проверка карты
		/*$mapName = $db->clean($_GET['map']);
		if ($mapName) {
			$db->instert2('mapList',Array('mapName'=>"'$mapName'"),
							'ON DUPLICATE KEY UPDATE gamesNum=gamesNum+1');
			$res = $db->select('*','mapList',"WHERE mapName = '$mapName' AND ladderMap = 1");
			if (!count($res)) {
				die('NLM');
			}
		}
		*/
		$playerID1 = GetPID($G['plr1']);
		$playerID2 = GetPID($G['plr2']);
		if ((!is_numeric($playerID1)) or (!is_numeric($playerID2))) die("OK");
		// Сейчас есть
		// Игроки участвуют на турнире?
		$res = $db->select('*, COUNT(matchID) as count','tr_matches',
			"INNER JOIN tr_matchData USING (matchID)
			WHERE status = 1 AND (playerID = $playerID1 OR playerID = $playerID2)
			GROUP BY matchID",false);
		if (count($res) <> 0) {
			foreach ($res as $row) {
				// Да
				if ($row['count'] == 2) {
					die('TOUR');
				}
			}
		}

		// Лимит дуелей
		$matchData = $db->prefix."_matchData";
		$matchList = $db->prefix."_matchList";
		$res = $db->select("","","SELECT SUM(win1) AS wins1, SUM($matchData.win) AS wins2
			FROM (
				SELECT $matchList.matchID, $matchList.dateTime, $matchData.win AS win1
				FROM $matchList
				INNER JOIN $matchData ON $matchList.matchID = $matchData.matchID
				WHERE $matchData.playerID = '$playerID1' AND $matchList.gameType = 'DUEL'
			) AS curlist
			INNER JOIN $matchData ON curlist.matchID = $matchData.matchID 
			WHERE $matchData.playerID = '$playerID2' AND DATE(curlist.dateTime)=DATE(NOW())");
		$res = $res[0];
		if (($res['wins1']>=9) or ($res['wins2']>=9)) die('Limit Reached'); else die("OK");
	break;
	
	// UPDATE PLAYER STATS
	case 'ups': 
		$pname = $db->clean(iconv('CP1251','UTF-8',$G['name']));
		$pnick = $db->clean(iconv('CP1251','UTF-8',$G['nick']));
		$nfkmodel = $G['model'];
		if ($pname == '') die("Error: Player name is invalid.");
		
		$gtype = $G['gt'];
		switch ($gtype) {
			case "DOM": $dbase="ladderDOM"; break;
			case "DM": $dbase="ladderDM"; break;
			case "DUEL": $dbase="ladderDUEL"; break;
			case "TDM": $dbase="ladderTDM"; break;
			case "CTF": $dbase="ladderCTF"; break;
			default: die("Error: Ivalid game type.");	
		}
		
		$pWIN = $G['winner'];
		switch ($pWIN) {
			case '0': $win=0; $lose = 1; break;
			case '1': $win=1; $lose = 0; break;
			case '-1': $win=-1; $lose = 0; break;
			default: $win=0; $lose = 0;
		}
		
		$playerID = GetPID($G['name']);

		// ADD NEW PLAYER
		if ($playerID == false) {
			$db->insert("playerStats", Array(
				'name'		=> "'$pname'",
				'regIP'		=> "'$G[ip]'", 
				'regDate'	=> "NOW()",
				'country'	=> "'".ip2country($G['ip'])."'"
			));
			$res = $db->select("last_insert_id( ) as playerID","","");
			$playerID = $res[0]['playerID'];
			if ($playerID == '') die("Error: playerID is null.");
			if ($playerID == '0') die("Error: Invalid playerID.");
			$db->insert("ladderDOM", Array('playerID' => "'$playerID'",));
			$db->insert("ladderDM", Array('playerID' => "'$playerID'",));
			$db->insert("ladderTDM", Array('playerID' => "'$playerID'",));
			$db->insert("ladderCTF", Array('playerID' => "'$playerID'",));
			$db->insert("ladderDUEL", Array('playerID' => "'$playerID'",));
		}
		
		// Summ hits
		$summ_hits = $G['gaun_hits']+$G['mach_hits']+$G['shot_hits']+$G['gren_hits']+$G['rocket_hits']
					+$G['shaft_hits']+$G['plasma_hits']+$G['rail_hits']+$G['bfg_hits'];
		// Summ fire
		$summ_fire = (isset($G['gaun_fire']) ? $G['gaun_fire'] : '') + $G['mach_fire']+$G['shot_fire']+$G['gren_fire']+$G['rocket_fire']
					+$G['shaft_fire']+$G['plasma_fire']+$G['rail_fire']+$G['bfg_fire'];
		
		// INSERT MATCH DATA
		$db->insert("matchData", Array(
			'matchID'		=> "'$G[mid]'",
			'playerID'		=> "'$playerID'",
			'frags'			=> "'$G[frags]'",
			'deaths'		=> "'$G[deaths]'",
			'team'			=> isset($G['team']) ? "'$G[team]'" : "''", 
			'win'			=> "'$win'",
			'score'			=> isset($G['modscore']) ? "'$G[modscore]'" : "''",
			'ping'			=> "'$G[ping]'",
			'time'			=> "'$G[time]'",
			'IP'			=> "'$G[ip]'",
			'suisides'		=> isset($G['suisides']) ? "'$G[suisides]'" : "''",
			'dmgrecvd'		=> "'$G[dmgrecvd]'",
			'dmggiven'		=> "'$G[dmggiven]'",
			'bfg_hits'		=> "'$G[bfg_hits]'", 
			'bfg_fire'		=> "'$G[bfg_fire]'",
			'impressives'	=> "'$G[impressives]'",
			'excellents'	=> "'$G[excellents]'",
			'humiliations'	=> "'$G[humiliations]'",
			'gaun_hits'		=> "'$G[gaun_hits]'",
			'mach_hits'		=> "'$G[mach_hits]'",
			'shot_hits'		=> "'$G[shot_hits]'",
			'gren_hits'		=> "'$G[gren_hits]'",
			'rocket_hits'	=> "'$G[rocket_hits]'", 
			'shaft_hits'	=> "'$G[shaft_hits]'",
			'plasma_hits'	=> "'$G[plasma_hits]'",
			'rail_hits'		=> "'$G[rail_hits]'",
			'mach_fire'		=> "'$G[mach_fire]'",
			'shot_fire'		=> "'$G[shot_fire]'",
			'gren_fire'		=> "'$G[gren_fire]'",
			'rocket_fire'	=> "'$G[rocket_fire]'",
			'shaft_fire'	=> "'$G[shaft_fire]'",
			'plasma_fire'	=> "'$G[plasma_fire]'", 
			'rail_fire'		=> "'$G[rail_fire]'", 
			'redArmors'		=> "'$G[redarmors]'", 
			'yellowArmors'		=> "'$G[yellowarmors]'", 
			'megaHealthes'		=> "'$G[megahealthes]'", 
			'powerUps'		=> "'$G[powerups]'", 
			
		));
		
		// UPDATE LADDER STATS
		//if ($G['limit']<>'1') {
			if ($win==-1) $win=0;
			$duelTable = false;
			if ($gtype == 'DUEL') {$duelTable = ", rank=$G[rank], score=score+$G[modscore]";};
			$db->update($dbase,
						"time=time+$G[time],
						frags=frags+$G[frags], 
						deaths=deaths+$G[deaths], 
						games=games+1,
						wins=wins+$win,
						losses=losses+$lose,
						lastGame=NOW()
						$duelTable",
						"WHERE `playerID` = '$playerID'");
						
			$updateClanScore = false;
			// UPDATE CLAN SCORE
			if ($gtype == 'DUEL') {
				$clanID = $db->select("clanID","playerStats","WHERE `playerID`='$playerID'");
				$clanID = $clanID[0]['clanID'];
				if ($clanID <> 0) {
					$db->update("clanList","score=score+$G[modscore]","WHERE clanID='$clanID'");
					$updateClanScore = "clanScore=clanScore+$G[modscore], clanGames=clanGames+1,";
				}
			}
						
			// UPDATE PLAYER STATS
			$db->update("playerStats",
					"time=time+$G[time], $gtype=$gtype+1, wins=wins+$win,
					 losses=losses+$lose, games=games+1, frags=frags+$G[kills],
					deaths=deaths+$G[deaths], hits=hits+$summ_hits, shots=shots+$summ_fire,
					bfg_hits=bfg_hits+$G[bfg_hits], bfg_fire=bfg_fire+$G[bfg_fire],gaun_hits=gaun_hits+$G[gaun_hits], 
					mach_hits=mach_hits+$G[mach_hits],mach_fire=mach_fire+$G[mach_fire], lastGame = NOW(), 
					humiliations=humiliations+$G[humiliations],excellents=excellents+$G[excellents],impressives=impressives+$G[impressives],
					shot_hits=shot_hits+$G[shot_hits], shot_fire=shot_fire+$G[shot_fire], 
					gren_hits=gren_hits+$G[gren_hits], gren_fire=gren_fire+$G[gren_fire],
					rocket_hits=rocket_hits+$G[rocket_hits], rocket_fire=rocket_fire+$G[rocket_fire],
					shaft_hits=shaft_hits+$G[shaft_hits], shaft_fire=shaft_fire+$G[shaft_fire], 
					plasma_hits=plasma_hits+$G[plasma_hits], plasma_fire=plasma_fire+$G[plasma_fire], 
					rail_hits=rail_hits+$G[rail_hits], rail_fire=rail_fire+$G[rail_fire],
					gaun_kills=gaun_kills+$G[humiliations], mach_kills=mach_kills+$G[mach_kills],
					shot_kills=shot_kills+$G[shot_kills], gren_kills=gren_kills+$G[gren_kills], 
					rocket_kills=rocket_kills+$G[rocket_kills], shaft_kills=shaft_kills+$G[shaft_kills], 
					plasma_kills=plasma_kills+$G[plasma_kills], rail_kills=rail_kills+$G[rail_kills],
					bfg_kills=bfg_kills+$G[bfg_kills], $updateClanScore
					model='$nfkmodel', nick='$pnick', lastIP = '$G[ip]'",
					"WHERE `playerID` = '$playerID'");
		//} else die("Error: Limit Detected");
	break;
	
	// UPDATE SCORE
	case 'UpdateScore':
		//old method
		//include("mods/inc/updateAltStats.inc.php");
		//new method by Kain
		include("mods/inc/updateAltStatsVer2.inc.php");
	break;
	
	// TOURNEYS
	case 'checktourneys':
		$gameID = $_GET['mid'];
		if (!is_numeric($gameID)) die('Error');
		// TR_WAITING = 0; TR_CHECKIN = 1; TR_STARTED = 2; TR_ENDED = 3; TR_FAILED = 4;
		// PL_REG = 0; PL_CHECK = 1;
		// Есть ли сейчас активные турниры?
		$tourneys = $db->select('tourID, stages, title, tourNum, 
									dateStart, dateReg, dateCheckin, mapList',
								'tr_tourneys','WHERE status=2',false);
		if (count($tourneys) == 0) break;
		// Да, есть
		// Подгружаем файл для обработки результата 
		include("mods/inc/checkTourneys.inc.php");	
	break;
	
	
	
	case 'upload':
		$uploaddir = "demos/"; 
		$filename = "$_GET[id]_".basename($_FILES['userfile']['name']);
		$filename = iconv('CP1251','UTF-8',$filename);
		$uploadfile = $uploaddir . $filename;
		//if (getExt($_FILES['userfile']['name']) == 'ndm') {
			if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
				$db->update("matchList","demo='$filename'","WHERE matchID='$_GET[id]' LIMIT 1");
			} 
		//}
	break;
	
	case 'test':
		/*	$res = $db->select("regIP, lastIP, playerID","playerStats","");
			foreach ($res as $row) {
				$country = ip2country($row['regIP']);
				$db->update("playerStats","country = '$country'","WHERE `playerID` = '$row[playerID]'");
			}
			echo "done";

			$res = $db->select("userIP, cmtID","matchComments","");
			foreach ($res as $row) {
				$country = ip2country($row['userIP']);
				$db->update("matchComments","country = '$country'","WHERE `cmtID` = '$row[cmtID]'");
			}
			echo "done";

		//echo parseNameColor("asd^1asd^2asd^3asd^4asdasd^5asd^6asd^7asd^!asd^#asds^\$asd^&asd^^aaa^b^n");
		*/
		/*
		echo $G['action']."-".$G['name']."<br>";
		$name =$G['name'];//($G['name']);
		//$name = urldecode($G['name']);//($G['name']);
		//echo $G['action']."-".$name."<br>";
		//echo "<br>123".win2utf($name);
		$name = CP1251toUTF8($name);
		$res = $db->select("playerID","playerStats",'WHERE `name` = "'.$name.'" LIMIT 1');
		print_r($res);*/
		/*
		$slotsNum = 8;
		$stages = log($slotsNum,2); //4
		if (fmod($stages, 1)<>0) die('error');
		$slot[1][1]=1;
		$slot[1][2]=2;
		
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
		
		echo '<pre>';
		print_r($slot);
		echo '</pre>';
		*/
		echo $flat_ip = sprintf("%u", ip2long('158.46.2.34'));
	break;
	
	// ??
	default: die("Hello World!");
}

?>
