<?php
if (!defined("NFK_LIVE")) define("NFK_LIVE", true);
define('apikey', '1RuRmiMsKv');
ini_set('display_errors',E_ALL);

$act = $_REQUEST['action'];

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

		$hostName = urldecode($_REQUEST['name']);
		if ($hostName == '') die('error');
		
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
	
	case 'get-server-list':
	case 'gsl':
		include_once('nfkpl.php');
		$servers = nfkpl_getServers();
		$players = $db->select('serverName, playerName, p.playerID, nick, model, country, AllRating',
			'onServers s','LEFT JOIN nfkLive_playerStats p ON p.name = s.playerName LEFT JOIN AltStat_Players a ON a.PlayerId = p.playerID WHERE s.playerName <>  \'Null\' GROUP BY serverName, playerName');
			
		$plrs = array();
		foreach($players as $plr){
			$plrs[$plr['serverName']][] = array(
				'playerID'=>$plr['playerID'],
				'nick'=>$plr['nick'],
				'name'=>$plr['playerName'],
				'country'=>$plr['country'],
				'model'=>$plr['model'],
				'points'=>$plr['AllRating'],
				'place'=>'0',
			);
		}
		$srvs = array();
		foreach($servers as $srv){
			$name = stripColor($srv['Hostname']);
			$srvs[] = array(
				'name'=>$name,
				'hostname'=>$srv['Hostname'],
				'map'=>$srv['Map'],
				'gametype'=>$srv['Gametype'],
				'load'=>$srv['Players'].'/'.$srv['Maxplayers'],
				'ip'=>$srv['IP'].':'.$srv['Port'],
				'players'=>$plrs[$name]
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