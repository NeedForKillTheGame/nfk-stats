<?php
if (!defined("NFK_LIVE")) define("NFK_LIVE", true);
define('apikey', '1RuRmiMsKv');
ini_set('display_errors',E_ALL);

$act = isset($_REQUEST['action']) ? $_REQUEST['action'] : false;

// Configuration
require("inc/config.inc.php");
// Functions
require("inc/functions.inc.php");
// Classes
require("inc/classes.inc.php");
// db connect
$db = new db();
$db->connect(
    $CFG['db_host'],
    $CFG['db_login'],
    $CFG['db_pass'],
    $CFG['db_name'],
    $CFG['db_prefix']
);
$player = new player();
header('Content-type: application/json');
switch ($act) {
	// UPDATE MATCH STATS
	case "server":
		$players = array();

		if (!isset($_REQUEST['name'])) die('invalid server "name"');
		$hostName = urldecode($_REQUEST['name']);
		$hostName = $db->clean($hostName);
		$res = $db->select('*','onServers',"WHERE serverName = '$hostName'");
		foreach ($res as $row) {
			$plr = $player->fetchName(addslashes($row['playerName']));
	
			$row['name'] = $row['playerName'];
			$PLAYER_NAME_URL	= ($plr) ? getIcons($plr,true,true,true,true):
											getIcons($row,false,false,false);
			$NUM				= ++$i;
			$players[] = array('name'=>$row['name'],'url'=>$PLAYER_NAME_URL,'num'=>$NUM);
		}
		die(json_encode($players));
	break;
	
	case "matches":
		$players = array();

		$skip = isset($_GET['skip']) ? $_GET['skip'] : 0;  // offset
		if (!is_numeric($skip))
			$skip = 0;
		$take = isset($_GET['take']) ? $_GET['take'] : 10; // count
		if (!is_numeric($take))
			$take = 10;
		if ($take > 1000)
			$take = 1000;
		
		$res = $db->select('*','matchList',"ORDER BY matchID desc LIMIT $skip, $take");
		die(json_encode($res));
	
	case 'get-server-list':
	case 'gsl':
		include_once('nfkpl.php');
		$servers = nfkpl_getServers();
		$players = $db->select('id, serverName, playerName, p.playerID, nick, model, country, AllRating',
			'onServers s','LEFT JOIN nfkLive_playerStats p ON p.name = s.playerName LEFT JOIN AltStat_Players a ON a.PlayerId = p.playerID WHERE s.playerName <>  \'Null\' GROUP BY playerName,serverName ORDER BY id');

/*
		$plrs = array();
		foreach($players as $plr){
			$plrs[$plr['serverName']][] = array(
				'playerID'=>$plr['playerID'],
				'nick'=>html_entity_decode($plr['nick']),
				'name'=>html_entity_decode($plr['playerName']),
				'country'=>$plr['country'],
				'model'=>$plr['model'],
				'points'=>$plr['AllRating'],
				'place'=>'0',
			);
		}
		$players_unique = array();
*/

		$srvs = array();
		foreach($servers as $srv){
			$servername = stripColor( $srv['Hostname'] );
			$plist = array();

			
			// place each player on appropriate server
			$i = 0;
			$count = 0;
			foreach($players as $plr)
			{
				// if we reach current players count on a server
				if ($count >= $srv['Players'])
					break;
				if ($plr['serverName'] == $servername)
				{
					$plist[] = array(
						'playerID'=>$plr['playerID'],
						'nick'=>html_entity_decode($plr['nick']),
						'name'=>html_entity_decode($plr['playerName']),
						'country'=>$plr['country'],
						'model'=>$plr['model'],
						'points'=>$plr['AllRating'],
						'place'=>'0',
					);
					array_splice($players, $i, 1); // remove current element
					$count++;
				}
				else
					$i++; // index
			}
			
			$srvs[] = array(
				'name'=>$servername,
				'hostname'=>$srv['Hostname'],
				'map'=>$srv['Map'],
				'gametype'=>$srv['Gametype'],
				'load'=>$srv['Players'].'/'.$srv['Maxplayers'],
				'ip'=>$srv['IP'].':'.$srv['Port'],
				'players'=> $plist
			);
			
		}
		die(json_encode($srvs));
	break;
    case 'getdemo':
        if (!isset($_GET['apikey'], $_GET['appid']) || $_GET['apikey'] != apikey) {
            echo "invalid parameters";
            exit;
        }
        $appid = $db->clean($_GET['appid']);
        $matches = $db->select('*','matchList ml','WHERE ml.videoAppID = "'.$appid.'" ORDER BY dateTime DESC LIMIT 1');
        if (!isset($matches[0])) {
            $matches = $db->select('*','matchList ml','WHERE ml.videoAppID is null and demo <> "" and video is null ORDER BY dateTime DESC LIMIT 1');
            if (!isset($matches[0])) {
                echo "match is not found";
                exit;
            }
        }
        $match = $matches[0];
        $db->update('matchList ml', 'videoAppID = "'.$appid.'"', 'WHERE ml.matchID = '.(int)$match['matchID']);
        $players = $db->select('*','matchData md','JOIN nfkLive_playerStats ps on ps.playerID = md.playerID WHERE md.matchID = ' . (int)$match['matchID']);
        $playerNames = array();
        foreach ($players as $player) {
            $playerNames[] = $player['name'];
        }
        echo json_encode(array(
            'id' => $match['matchID'],
            'file' => 'http://stats.needforkill.ru/demos/' . urlencode($match['demo']),
            'date' => strtotime($match['dateTime']),
            'gametype' => $match['gameType'],
            'duration' => $match['gameTime'],
            'map' => $match['map'],
            'players' => $playerNames,
        ));
        exit;
    case 'setvideo':
        if (!isset($_GET['apikey'], $_GET['appid'], $_GET['demoid'])) {
            echo "invalid parameters";
            exit;
        }
        if ($_GET['apikey'] != apikey) die('apikey is invalid');
        if (!isset($_POST['video'])) die('video is not set');
        if (empty($_POST['video']))  die('video is empty');
        $matches = $db->select('*','matchList ml','WHERE videoAppID = "'.$db->clean($_GET['appid']).'" and matchID = ' . (int)$_GET['demoid']);
        if (!isset($matches[0])) {
            echo "match is not found";
            exit;
        }
        $match = $matches[0];
        $db->update('matchList ml', 'videoAppID = null, video = "'.$db->clean($_POST['video']).'"', 'WHERE ml.matchID = '.(int)$match['matchID']);
        echo 'success';
        exit;
	// ??
	default: die("Hello World!");
}
