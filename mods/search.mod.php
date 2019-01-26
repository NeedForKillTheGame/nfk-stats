<?php

if (!defined("NFK_LIVE")) die();

$template->load_template('mod_search');


$query = htmlspecialchars(trim($_POST['q']));
$query = $db->clean($query);
if ($query <> "") {
	$res = $db->select("name, lastIP, playerID","playerStats","WHERE name LIKE '%$query%' LIMIT 60");
	
	// GTW: found_player
	foreach ($res as $row) {
		$MARKERS = Array
			(
				"PLAYER_NAME"		=> getPlayerName($row['playerID']),
	//			"PLAYER_IP"			=> $row['lastIP'],
				"PLAYER_ID"			=> $row['playerID'],

				"PLACE"				=> ++$place,
			);
	$template->assign_variables($MARKERS);
	$found_players .= $template->build('found_player') or die("error building: search\found_player");
	}
}
$MARKERS = Array
	(
		"GTW_LOGIC"			=> ($query <> "") ? true : false,
		"G_FOUND_PLAYERS"	=> $found_players,
		"QUERY"				=> $query,
	);
$template->assign_variables($MARKERS);
$names_found .= $template->build('if_name_found') or die("error building: search\if_name_found");


$page_title = $dict->data['search'];
$page_name = $page_title;

//
// Build Main
//
$MARKERS = Array
	(
		"QUERY"				=> $query,
		"QUERY_IP"			=> $query_ip,
		
		"NAMES_FOUND"		=> $names_found,
	
		"IP_SEARCH"			=> $ip_search,
		
		"L_FOUND_PLAYERS"	=> $dict->data['found_players'],
		"L_NAME"			=> $dict->data['name'],
		"L_PLAYER_NAME"		=> $dict->data['player_name'],
		"L_PLAYER_SEARCH"	=> $dict->data['player_search'],
	);
	
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: search\main");

?>