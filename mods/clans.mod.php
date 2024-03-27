<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2009 ConnecT, 2011 coolant
// Module:	Home (server listing)
// Item:	
// Version:	0.1.8	14.07.2011
/////////////////////////////////////////////

if (!defined("NFK_LIVE")) die();

$template->load_template('mod_clans');


// Pages
$p_count = count($PARAMSTR);
$cur_page = ($PARAMSTR[$p_count-2] == "page") ? $PARAMSTR[$p_count-1] : 1;
if ( !is_numeric($cur_page) ) $cur_page = 1;

$res = $db->select("*","clanList","ORDER BY score DESC");

$total = $db->select("FOUND_ROWS() as 'rows'","","");
$total = $total[0]['rows'];
$pages_count = ceil($total / $CFG['items_per_page']);

// GTW: clans 
foreach ($res as $row) {
	$MARKERS = Array
	(
		"CLAN_ID"			=> $row['clanID'],
		"CLAN_NAME"			=> $row['clanName'],
		"CLAN_TAG"			=> $row['clanTag'],
		"CLAN_SCORE"		=> $row['score'],
		"CLAN_PLAYERS"		=> $row['players'],
		"PLACE"				=> ++$place,
	);
	$template->assign_variables($MARKERS);
	$clanlist .= $template->build('clan') or die("error building: clans\clan");
}

$page_title = $dict->data['clan_ladder'];
$page_name = $page_title;

if ( $pages_count > 1 ) {
	require_once("./mods/inc/pages.inc.php");  
}

//
// Build Main
//
$MARKERS = Array
	(
		"G_CLAN_LIST"		=> $clanlist,
		"PAGES"				=> $pages,
		
		"L_CLAN"			=> $dict->data['clan'],
		"L_SCORE"			=> $dict->data['score'],
		"L_PLAYERS"			=> $dict->data['players'],
	);
	
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: clans\main");

?>
