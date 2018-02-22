<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2009-2010 ConnecT, 2011 coolant
// Module:	Profile 
// Item:	
// Version:	0.1.5	14.07.2011
/////////////////////////////////////////////

if (!defined("NFK_LIVE")) die(); 

// given or me
$targetUsr = ($PARAMSTR[3] != '') ? (urldecode($PARAMSTR[3])) : ( $_POST['targetUsr']);
$targetUsr = ($targetUsr == '') ? (getUserName($xdata)) : ($targetUsr);
$targetUsr = ($targetUsr == '') ? ("<PFF>coolant") : ($targetUsr) ;
$targetUsr = $player->fetchName($targetUsr);

$profilePage = ($PARAMSTR[2] != '') ? ($PARAMSTR[2]) : ('summary'); // given or default profile page

//
// Build Content
//
if ($profilePage == 'summary') require_once("inc/profile.summary.php");
if ($profilePage == 'statistics') require_once("inc/profile.stats.php");
if ($profilePage == 'matches') require_once("inc/profile.matches.php");

//
// Build Main
//
$MARKERS = Array
	(
		"SUMMARY"			=> $TMPL_summary,
		"STATISTICS"		=> $TMPL_statistics,
		"MATCHES"			=> $TMPL_matches,
		
		"MY_ID"				=> $targetUsr['id'],
		"THEME_ROOT"		=> $CFG['root']."/themes/".$CFG['theme'],
	);
	
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: profile\main");

?>