<?php

if (!defined("NFK_LIVE")) die();

$template->load_template('mod_server');
$hostName = $db->clean(urldecode($PARAMSTR[2]));
$res = $db->select('*','onServers',"WHERE serverName = '$hostName'");
// GTW: player
foreach ($res as $row)
{
	//$playerID = GetPID(addslashes($row['playerName']));
	//$playerName = ();
	$plr = $player->fetchName($row['playerName']);
	$row['name'] = $row['playerName'];
	$MARKERS = Array
	(
		"PLAYER_NAME_URL"	=> ($plr) ? getIcons($plr,true,true,true,true):
										getIcons($row,false,false,false),
		"NUM"				=> ++$i,
	);
	$template->assign_variables($MARKERS);
	$players .= $template->build('player') or die("error building: server\player");
}

// default page title
$page_title = $dict->data['server'].": ".$hostName;
$page_name = $page_title;

//
// Build main
//
$MARKERS = Array
	(
		"G_PLAYERS"			=> $players,
		"SERVER_NAME"		=> $hostName,
		
		"L_SERVER"			=> $dict->data['server'],
		"L_PLAYERS"			=> $dict->data['players'],
	);	
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: server\main");
?>