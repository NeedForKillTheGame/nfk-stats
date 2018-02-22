<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2009 ConnecT, 2011 coolant
// Module:	Home (server listing)
// Item:	
// Version:	0.1.8	14.07.2011
/////////////////////////////////////////////

if (!defined("NFK_LIVE")) die();

if ($_SESSION['me'] != "") $me->assign($_SESSION['me']);
$xdata = $_SESSION['me'];

//
// Render "Create Server" button
//

$template->load_template('mod_home');

/*
$MARKERS = Array
	(
		"GTW_LOGIC"	=> ($xdata['login'] != null) ? (true) : (false),
		"PSID"		=> $xdata['psid'],
	);
	
$template->assign_variables($MARKERS);
$TEMPLATE_logged2 = $template->build('if_logged_createsrv') or die("error building: home\if_logged_createSrv");
*/

//
// Server List
//
$res = $db->select('*','serverList','where ttl >= NOW()');
// GTW: match_row 
foreach ($res as $row) {
	$MARKERS = Array
	(
		"THEME_ROOT"		=> $CFG['root']."/themes/".$CFG['theme'],
		"MY_ID"				=> $xdata['id'],
		"PSID"				=> $xdata['psid'],
		
		"MATCH_ID"			=> $row['serverID'],
		"MAP_NAME"			=> $row['mapName'],
		"CITY"				=> $row['hostname'],
		"SERVER_IP"			=> $row['serverIP'],
		"GAMETYPE"			=> GameType($row['gameType']),
		"GAMETYPE_SHORT"	=> GameTypeShort($row['gameType']),
		"PLAYERCOUNT"		=> $row['playerCount'],
		"PLAYERMAX"			=> $row['playerMax'],
		"COUNTRY_CC3"		=> strtolower( ip2country('cc3',$row['serverIP']) ),
	);
	$template->assign_variables($MARKERS);
	$match_rows .= $template->build('match_row') or die("error building: home\match_row");
}

//
// Brief Stats
//

$MARKERS_IF = Array
	(
		"GTW_LOGIC"				=> ($xdata['login'] <> null) ? (true) : (false),
		"THEME_ROOT"			=> $CFG['root']."/themes/".$CFG['theme'],
		"MY_ID"					=> $xdata['id'],		
	);

if ($xdata['login'] <> null) {

	$MARKERS_ATT = Array
		(
			"GTW_LOGIC"				=> ($xdata['playerID'] <> 0) ? (true) : (false),
			"THEME_ROOT"			=> $CFG['root']."/themes/".$CFG['theme'],
			"MY_ID"					=> $xdata['id'],		
		);
	if ($xdata['playerID'] <> 0) {
		
		// get stats
		$player = $player->fetchId($xdata['playerID']);
		//$stats = $db->select('*','playerStats','WHERE playerID = '.$xdata['playerID']);
		//$stats = $stats[0];
		
		// get clan
		$clan = $db->select('clanTag','clanList','WHERE clanID = '.$player['clanID']);

		// AWARDS
		$awards = $db->select('medalID','playerRewards','WHERE playerID = '.$player['playerID'].' ORDER BY `rewardTime` DESC LIMIT 6');
		$award_n = 0; // awards in a row counter
		foreach ($awards as $award) {	
			// build single award_row
			$award_n++;
			$this_award = $db->select('*','medals','WHERE medalID = '.$award['medalID']);
			$this_award = $this_award[0];
					
			$MARKERS = Array
				(
					"AWARD_NAME"		=> $this_award['medalName'],
					"AWARD_NAME_FILE"	=> strtolower( str_replace(' ','_',$this_award['medalName']) ),
					"AWARD_DESCRIPTION"	=> $this_award['medalDescription'],
					"AWARD_ID"			=> $this_award['medalID'],
				);
			$template->assign_variables($MARKERS);
			$award_row .= $template->build('award_row') or die("error building: home\award_row");

			// build rows block (by 3)
			if ($award_n >= 3) {
				$MARKERS = Array
				(
					"AWARD_ROWS"	=> $award_row,
					"THEME_ROOT"	=> $CFG['root']."/themes/".$CFG['theme'],
				);
				
				$template->assign_variables($MARKERS);
				$award_rows .= $template->build('awards') or die("error building: home\awards");
				
				$award_n = 0;
				$award_row = '';
			}
		}
		// build last award block
		if ($award_n > 0) {
			$MARKERS = Array
			(
				"AWARD_ROWS"	=> $award_row,
				"THEME_ROOT"	=> $CFG['root']."/themes/".$CFG['theme'],
			);
				
			$template->assign_variables($MARKERS);
			$award_rows .= $template->build('awards') or die("error building: home\awards");
		}
		// AWARDS END

		// buld fav_weapon
		$MARKERS = Array
			(
				"WEAPON_SHORTNAME"	=> 	weaponShortName($player['favWeapon']),
				"WEAPON_FULLNAME"	=> 	weaponFullName($player['favWeapon']),
			);
				
		$template->assign_variables($MARKERS);
		$fav_weapon = $template->build('fav_weap') or die("error building: home\fav_weap");

		// buld fav_gametype
		$MARKERS = Array
			(
				"GAMETYPE_SHORT"	=> 	GameTypeShort($player['favGameType']),
				"GAMETYPE_FULL"		=> 	GameType($player['favGameType']),
			);
				
		$template->assign_variables($MARKERS);
		$fav_gametype = $template->build('fav_gametype') or die("error building: home\fav_gametype");

		// get model name
		$model_name = explode('_',$player['model']); // split model from skin by '_'
		$model_name = ucfirst($model_name[0]); // first letter is uppercase

		// build quickstats
		$MARKERS_ATT += Array
			(
				"TIME_FULL"				=> $player['time'],
				"TIME_DAYS"				=> sec2HourDays($player['time']),
				"LASTGAME_FULL"			=> ago_(strtotime($player['lastGame'])), 
				"LASTGAME_DAYHOURS"		=> ago_(strtotime($player['lastGame'])), //?
				
				"ACCURACY"				=> ($player['shots'] != 0) ? ( round($player['hits'] * 100 / $player['shots']) ) : (0),
				"HITS"					=> $player['hits'],
				"FIRE"					=> $player['shots'],
				
				"GAMES"					=> $player['games'],
				"WINS"					=> $player['wins'],
				"LOSSES"				=> $player['losses'],
				"WIN_RATIO"				=> ($player['wins'] != 0) ? (round($player['wins']*100/($player['wins']+$player['losses'])) ): ("0"),
				
				"FRAGS"					=> $player['frags'],
				"DEATHS"				=> $player['deaths'],
				"FRAG_RATIO"			=> ($player['deaths'] != 0) ? (round($player['frags'] / $player['deaths'],2)) : (0),
				
				"AWARDS"				=> $award_rows,
				"FAV_WEAPON"			=> $fav_weapon,
				"FAV_GAMETYPE"			=> $fav_gametype,
				"FAV_MAP"				=> '',
				
				"MODELSKIN_LOW"			=> $player['model'],
				"MODEL_NAME"			=> $model_name,
				
				"NICK_NOCOLOR"			=> stripNameColor($player['name']),
				"CLANTAG_NOCOLOR"		=> stripNameColor($clan[0]['clanTag']),
			);
	}
	$template->assign_variables($MARKERS_ATT);
	$player_stats = $template->build('if_attached') or die("error building: home\if_attached");
}
$MARKERS_IF += Array
	(
		"NULL"				=> "NULL",
		"PLAYER_STATS"		=> $player_stats,
	);

$template->assign_variables($MARKERS_IF);
$brief_stats = $template->build('if_logged') or die("error building: home\if_logged");

//
// Build Main
//
$MARKERS = Array
	(
		"MATCH_ROWS"		=> $match_rows,
		"BRIEF_STATS"		=> $brief_stats,
		"MY_ID"				=> $xdata['id'],
		"THEME_ROOT"		=> $CFG['root']."/themes/".$CFG['theme'],
	);
	
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: home\main");

?>