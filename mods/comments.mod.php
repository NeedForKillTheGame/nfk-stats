<?php

if (!defined("NFK_LIVE")) die();

$template->load_template('mod_comments');


// Pages
$p_count = count($PARAMSTR);
$cur_page = ($PARAMSTR[$p_count-2] == "page") ? $PARAMSTR[$p_count-1] : 1;
if ( !is_numeric($cur_page) ) $cur_page = 1;

$res = $db->select("SQL_CALC_FOUND_ROWS *","comments","ORDER BY cmtID DESC LIMIT ".(($cur_page - 1)*$CFG['items_per_page']).", ".($CFG['items_per_page']));

$total = $db->select("FOUND_ROWS() as 'rows'","","");
$total = $total[0]['rows'];
$pages_count = ceil($total / $CFG['items_per_page']);

// GTW: comment 
foreach ($res as $row) {

	if ($row['playerID']<>0) $plr = getPlayer($row['playerID']);
	$moduleID = $row['moduleID'];
	$materialID = $row['materialID'];
	if ($moduleID == 3) {
		$tour = $db->select('title, tourNum','tr_tourneys',"WHERE tourID = $materialID",false);
		$tour = $tour[0];
        $mtrString = ($tour['tourNum']<>'0') ? $tour['title'].' #'.$tour['tourNum'] : $tour['title'];
	} elseif ($moduleID == 4) {
        $mtrString = 'News #'.$materialID;
    } else $mtrString = '#'.$materialID;
	$MARKERS = Array(
		"MATERIAL_ID"		=> $materialID,
		'MOD_URL' 			=> $MODS_URL[$moduleID],
		'MATERIAL_NAME' 	=> $mtrString,//($moduleID == 3) ? $tourTitle : '#'.$materialID,
		"PLAYER_ID"			=> $row['playerID'],	
		"CMT_AUTHOR"		=> ($row['playerID']<>0) ? getIcons($plr):
														getIcons($row,false,false,false),
		"CMT_DATE"			=> $row['postTime'],
		"COMMENT"			=> $row['comment'],
	);
	$template->assign_variables($MARKERS);
	$comments .= $template->build('comment') or die("error building: matchlist\comment");
}

$page_title = $dict->data['comments'];
$page_name = $page_title;


if ( $pages_count > 1 ) {
	require_once("./mods/inc/pages.inc.php");  
}

//
// Build Main
//
$MARKERS = Array
	(
		"G_COMMENTS"		=> $comments,
		"PAGES"				=> $pages,
	);
	
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: matchlist\main");

?>
