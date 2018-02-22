<?php
if (!defined("NFK_LIVE")) die();

if (is_numeric($PARAMSTR[2])) {
	include('inc/tourneys.full.inc.php');
} else
	if ($PARAMSTR[2]=='ladder')
		include('inc/tourneys.ladder.inc.php');
	else
		include('inc/tourneys.list.inc.php');

// GTW: main
$template->assign_variables($MARKERS);
$content_data .= $template->build('main') or die("error building: tourneys\main");
?>