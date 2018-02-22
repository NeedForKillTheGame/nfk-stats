<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2009 ConnecT
// Item:	NFK Server
// Version:	0.0.3	08.10.2009
/////////////////////////////////////////////

if (!defined("NFK_LIVE")) die();

//$cmd = clean('str',$PARAMSTR[2]);
$cmd = $_GET['action'];

/*
    HANDSHAKE
*/
if ($cmd == 'hi')
{
    die("OK\nhello");
}

/*
	IDENTIFY
*/
elseif ($cmd == 'IdentifyMe')
{
	$temp_user['login'] = $_GET["login"];
	$temp_user['password'] = $_GET["password"];
	
	if ($this_user = $user->fetchName($temp_user['login']))
	{
		if ($this_user["password"] == $temp_user["password"])
		{
			// login/pass accepted
			// 
			
			$psid = newSID();
			
			$db->insert('sessions', Array(
				'sessionIP'	=> "'".$_SERVER['REMOTE_ADDR']."'",
				'playerID'	=> $this_user['id'],
				'ttl'		=> "'".date("Y-m-d H:i:s",strtotime('+30 seconds'))."'",
				'psid'		=> "'".$psid."'",
			));
			
			die( "OK\n$psid");
			
		}
		else 
		{
			die("Wrong password");
		}
	}
	else die("Unknown user");
}

/**
 *	RETRIEVE SERVER LIST
 */
elseif ($cmd == 'G')
{
	$res = $db->select('*','serverList',"where ttl >= NOW()");
    
    foreach ($res as $row)
	{
$result .= "
$row[hostname]
$row[mapName]
$row[gameType]
$row[playerCount]
$row[playerMax]
$row[serverIP]";
	}
    
    if ($result <> '') { die("OK".$result); }  else die("NO SERVERS");
}

/*
    UPDATE CURRENT USERS
*/
elseif ($cmd == 'C')
{
    if (mysql_query("UPDATE `nfkLive_serverList` 
	SET `playerCount` = $_GET[playerCount] 
	WHERE `ssid` = '$_GET[ssid]'"))
	{ die("OK");  } else die(strip_tags(mysql_error()));
}

/*
    UPDATE GAME TYPE
*/
elseif ($cmd == 'P')
{
    if (mysql_query("UPDATE `nfkLive_serverList` 
	SET `gameType` = $_GET[gameType] 
	WHERE `ssid` = '$_GET[ssid]'"))
	{ die( "OK");  } else die(strip_tags(mysql_error()));
}

/*
    UPDATE HOST NAME
*/
elseif ($cmd == 'N')
{
    if (mysql_query("UPDATE `nfkLive_serverList` 
	SET `hostName` = '$_GET[hostName]' 
	WHERE `ssid` = '$_GET[ssid]'"))
	{ die("OK");  } else die(strip_tags(mysql_error()));
}

/*
    UPDATE MAP NAME
*/
elseif ($cmd == 'm')
{
    if (mysql_query("UPDATE `nfkLive_serverList` 
	SET `mapName` = '$_GET[mapName]' 
	WHERE `ssid` = '$_GET[ssid]'"))
	{ die("OK");  } else die(strip_tags(mysql_error()));
}

/*
    UPDATE MAX PLAYERS
*/
elseif ($cmd == 'M')
{
    if (mysql_query("UPDATE `nfkLive_serverList` 
	SET `playerMax` = $_GET[playerMax] 
	WHERE `ssid` = '$_GET[ssid]'"))
	{ die("OK");  } else die(strip_tags(mysql_error()));
}

/*
    UPDATE PLAYER MODEL
*/
elseif ($cmd == 'PM')
{
	// check for session&
    $q = @mysql_query("SELECT *
	FROM `nfkLive_sessions`
	WHERE `psid` = '$_GET[psid]'
    ");
    
    $q = @mysql_fetch_assoc($q);
    $q = $q['playerID'];
	
    if ($q != '')
    {
		if (mysql_query("UPDATE `nfkLive_users` 
		SET `model` = '$_GET[newModel]' 
		WHERE `playerID` = $q"))
		{ die( "OK");  } else die(strip_tags(mysql_error()));
	}
	else die("INVALID SESSION");
}

/*
    UPDATE PLAYER NAME
*/
elseif ($cmd == 'PN')
{
	// check for session&
    $q = @mysql_query("SELECT *
	FROM `nfkLive_sessions`
	WHERE `psid` = '$_GET[psid]'
    ");
    
    $q = @mysql_fetch_assoc($q);
    $q = $q['playerID'];
	
    if ($q != '')
    {
		if (mysql_query("UPDATE `nfkLive_users` 
		SET `full_name` = '$_GET[newName]' 
		WHERE `playerID` = $q"))
		{ die("OK");  } else die(strip_tags(mysql_error()));
	}
	else die("INVALID SESSION");
}

/*
    KEEP ALIVE
*/
elseif ($cmd == 'keepalive')
{
	if ($_GET['ssid'] != '')
	{
		if (mysql_query("UPDATE `nfkLive_serverList` 
			SET `ttl` = '".date("Y-m-d H:i:s", strtotime('+62 seconds'))."'
			WHERE `ssid` = '$_GET[ssid]'"))
		{ die( "OK"); } else die(strip_tags(mysql_error()));
	}
	else die("INVALID SESSION");
}

/*
    REGISTER SERVER
*/
elseif ($cmd == 'register')
{
    if(!mysql_query("INSERT INTO `nfkLive_serverList` (
	dedicated,
	ssid,
	ttl,
	serverIP,
	port,
	hostname,
	gameType,
	mapName,
	timeLimit,
	timeLeft,
	playerCount,
	playerMax
    ) VALUES (
	$_GET[dedicated],
	'$_GET[ssid]',
	'".date("Y-m-d H:i:s",time()+($_GET[timeLimit] * 60))."',
	'$_SERVER[REMOTE_ADDR]',
	$_GET[port],
	'$_GET[hostname]',
	$_GET[gameType],
	'$_GET[mapName]',
	$_GET[timeLimit],
	$_GET[timeLimit],
	$_GET[playerCount],
	$_GET[playerMax]
    )"))
    {die (strip_tags(mysql_error()));}
    else die("OK");
}
/*
    UNREGISTER SERVER
*/
elseif ($cmd == 'unregister')
{
    if (mysql_query("DELETE FROM `nfkLive_serverList`
	WHERE `ssid` = '$_GET[ssid]'
    ")) { die("OK"); }
    else die(strip_tags(mysql_error()));
}


/*
    MATCH STATS UPDATER
*/
elseif ($cmd == 'ums')
{

	//
	// Update Match Stats
	//
	$s = $_GET;
	
	// Summ hits
	$summ_hits = $s['gaun_hits']
	    +$s['mach_hits']
	    +$s['shot_hits']
	    +$s['gren_hits']
	    +$s['rocket_hits']
	    +$s['shaft_hits']
	    +$s['plasma_hits']
	    +$s['rail_hits']
	    +$s['bfg_hits']
	;
	
	// Summ fire
	$summ_fire = $s['gaun_fire']
	    +$s['mach_fire']
	    +$s['shot_fire']
	    +$s['gren_fire']
	    +$s['rocket_fire']
	    +$s['shaft_fire']
	    +$s['plasma_fire']
	    +$s['rail_fire']
	    +$s['bfg_fire']
	;
	
	// TEST ...
    $sID = @mysql_query("SELECT *
	FROM `nfkLive_serverList`
	WHERE `ssid` = '$_GET[ssid]'
    ");	
    $sID = @mysql_fetch_assoc($sID);
    $sID = $sID['serverID'];
	
	$map = @mysql_query("SELECT *
	FROM `nfkLive_mapList`
	WHERE `mapName` = '$_GET[mapName]'
    ");	
	$map = @mysql_fetch_assoc($map);
    $map = $map['maplayerID'];
	
	if ($map == '') $map='hz';
	
    if ($sID != '')
    {	
		if (!mysql_query("INSERT INTO `nfkLive_matchList` (
			`matchID` ,
			`serverID`,
			`gameType`,
			`gameTime`,
			`playerCount`,
			`maplayerID`,
			`dateTime`
		) VALUES (
			NULL,
			'$sID',
			'$s[gametype]',
			'$s[gameTime]',
			'$s[playerCount]',
			'$map',
			'".date("Y-m-d H:i:s")."'
		)")) { die ( strip_tags(mysql_error()) );}
		else die("OK");
    }
    else die("ERROR: sID = null");
	// ... TEST 
}


/*
    UPDATE PLAYER STATS 
*/
elseif ($cmd = 'ups')
{
    // check for session&ip
    $q = @mysql_query("SELECT *
	FROM `nfkLive_sessions`
	WHERE `psid` = '$_GET[psid]'
    ");
    
    $q = @mysql_fetch_assoc($q);
    $q = $q['playerID'];
	
    if ($q != '')
    {
		// session is valid
		
		//
		// Update Player Stats
		//
		$s = $_GET;
			
		// Summ hits
		$summ_hits = $s['gaun_hits']
			+$s['humiliations']
			+$s['mach_hits']
			+$s['shot_hits']
			+$s['gren_hits']
			+$s['rocket_hits']
			+$s['shaft_hits']
			+$s['plasma_hits']
			+$s['rail_hits']
			+$s['bfg_hits']
		;

		// Summ fire
		$summ_fire = $s['gaun_fire']
			+$s['mach_fire']
			+$s['shot_fire']
			+$s['gren_fire']
			+$s['rocket_fire']
			+$s['shaft_fire']
			+$s['plasma_fire']
			+$s['rail_fire']
			+$s['bfg_fire']
		;
		
		$gameType = GameTypeShort($s['gametype']);
		
		if (!mysql_query("UPDATE `nfkLive_playerStats` SET
			$gameType=$gameType+1,
			games=games+1, frags=frags+$s[kills], deaths=deaths+$s[deaths], 
			hits=hits+$summ_hits, shots=shots+$summ_fire,
			bfg_hits=bfg_hits+$s[bfg_hits], bfg_fire=bfg_fire+$s[bfg_fire]
			WHERE `playerID` = $q")
			)  die(strip_tags(mysql_error()));
		
		if (!mysql_query("UPDATE `nfkLive_playerStats` SET
			gaun_hits=gaun_hits+$s[gaun_hits], mach_hits=mach_hits+$s[mach_hits], 
			mach_fire=mach_fire+$s[mach_fire], lastGame = NOW(), 
			humiliations=humiliations+$s[humiliations],excellents=excellents+$s[excellents],impressives=impressives+$s[impressives]
			WHERE `playerID` = $q")
			)  die(strip_tags(mysql_error()));
			
		if (!mysql_query("UPDATE `nfkLive_playerStats` SET
			 shot_hits=shot_hits+$s[shot_hits], shot_fire=shot_fire+$s[shot_fire], 
			 gren_hits=gren_hits+$s[gren_hits], gren_fire=gren_fire+$s[gren_fire], 
			 rocket_hits=rocket_hits+$s[rocket_hits], rocket_fire=rocket_fire+$s[rocket_fire]
			 WHERE `playerID` = $q")
			)  die(strip_tags(mysql_error())); 
		
		if (!mysql_query("UPDATE `nfkLive_playerStats` SET
			 shaft_hits=shaft_hits+$s[shaft_hits], shaft_fire=shaft_fire+$s[shaft_fire], 
			 plasma_hits=plasma_hits+$s[plasma_hits], plasma_fire=plasma_fire+$s[plasma_fire], 
			 rail_hits=rail_hits+$s[rail_hits], rail_fire=rail_fire+$s[rail_fire]
			 WHERE `playerID` = $q")
			)  die(strip_tags(mysql_error()));
		
		// kills
		if (!mysql_query("UPDATE `nfkLive_playerStats` SET
			 gaun_kills=gaun_kills+$s[humiliations], mach_kills=mach_kills+$s[mach_kills],
			 shot_kills=shot_kills+$s[shot_kills], gren_kills=gren_kills+$s[gren_kills], 
			 rocket_kills=rocket_kills+$s[rocket_kills], shaft_kills=shaft_kills+$s[shaft_kills], 
			 plasma_kills=plasma_kills+$s[plasma_kills], rail_kills=rail_kills+$s[rail_kills],
			 bfg_kills=bfg_kills+$s[bfg_kills]
			 WHERE `playerID` = $q")
			)  die(strip_tags(mysql_error())); 


		// TEST nfkLive_matchData
		$sID = @mysql_query("SELECT *
		FROM `nfkLive_serverList`
		WHERE `ssid` = '$_GET[ssid]'
		");	
		$sID = @mysql_fetch_assoc($sID);
		$sID = $sID['serverID'];
		
		$mID = @mysql_query("SELECT *
		FROM `nfkLive_matchList`
		WHERE `serverID` = $sID
		");	
		$mID = @mysql_fetch_assoc($mID);
		$mID = $mID['matchID'];
		
		if ($mID != '')
		{
			if (!mysql_query("INSERT INTO `nfkLive_matchData` (
				`statID`,
				`playerID`,
				`frags`,
				`deaths`,
				`suisides`,
				`dmgrecvd`,
				`dmggiven`,
				`bfg_hits`,
				`bfg_fire`,
				`matchID`,
				`impressives`,
				`excellents`,
				`humiliations`,
				`gaun_hits`,
				`mach_hits`,
				`shot_hits`,
				`gren_hits`,
				`rocket_hits`,
				`shaft_hits`,
				`plasma_hits`,
				`rail_hits`,
				`mach_fire`,
				`shot_fire`,
				`gren_fire`,
				`rocket_fire`,
				`shaft_fire`,
				`plasma_fire`,
				`rail_fire`
			) VALUES (
				NULL,
				'$q', $s[kills], '$s[deaths]','$s[suisides]',
				'$s[dmgrecvd]', '$s[dmggiven]','$s[bfg_hits]', 
				'$s[bfg_fire]','$mID', '$s[impressives]',
				'$s[excellents]', '$s[humiliations]', '$s[gaun_hits]',
				'$s[mach_hits]', '$s[shot_hits]', '$s[gren_hits]', 
				'$s[rocket_hits]', '$s[shaft_hits]', '$s[plasma_hits]',
				'$s[rail_hits]', '$s[mach_fire]', '$s[shot_fire]',
				'$s[gren_fire]', '$s[rocket_fire]', '$s[shaft_fire]',
				'$s[plasma_fire]', '$s[rail_fire]'
			)")) { die(strip_tags(mysql_error())); }
			else die("OK");
		}	
		else die("ERROR: mID = null");
		// END nfkLive_matchData
	
		//
		// Recalculate favourites
		//
		
		// fav weapon
		//

		$u = $db->select("*","playerStats","WHERE playerID =$q");
		$u = $u[0];
		$fwpn['0'] 	= $u['gaun_hits']/0.04;
		$fwpn['1'] 	= $u['mach_fire']/0.2;
		$fwpn['2'] 	= $u['shot_fire']/0.02;
		$fwpn['3'] 	= $u['gren_fire']/0.022;
		$fwpn['4'] 	= $u['rocket_fire']/0.025;
		$fwpn['5'] 	= $u['shaft_fire']/10;
		$fwpn['6'] 	= $u['plasma_fire']/0.2;
		$fwpn['7'] 	= $u['rail_fire']/0.011;
		$fwpn['8'] 	= $u['bfg_fire']/0.083;
		arsort($fwpn,SORT_NUMERIC); // put most used to the top
	
		foreach ($fwpn as $key => $value) // pick one
		{
			$fwpn = $key;
			break; // one is enough
		}
		
		if (!mysql_query("UPDATE `nfkLive_playerStats` SET
			favWeapon = $fwpn
			WHERE `playerID` = $q"))  die(strip_tags(mysql_error()));
		
		// fav gametype
		//
		$fgt['0'] 	= $u['dm'];
		$fgt['1'] 	= $u['duel'];
		$fgt['2'] 	= $u['tdm'];
		$fgt['3'] 	= $u['ctf'];
		$fgt['4'] 	= $u['gib'];
		$fgt['5'] 	= $u['trx'];
		$fgt['6'] 	= $u['tren'];
		$fgt['7'] 	= $u['dom'];	
		arsort($fgt,SORT_NUMERIC); // put most played to the top
	
		foreach ($fgt as $key => $value) // pick one
		{
			$fgt = $key;
			break; // one is enough
		}
		
		if (!mysql_query("UPDATE `nfkLive_playerStats` SET
			favGametype = $fgt
			WHERE `playerID` = $q"))  die(strip_tags(mysql_error()));
		
		die("OK"); 
    }
    else die("INVALID SESSION");
}

die(); // prevent skin generation
?>