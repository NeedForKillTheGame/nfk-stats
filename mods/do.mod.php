<?php

if (!defined("NFK_LIVE")) die();

switch ( $PARAMSTR[2] ) {

	case "user" :
		require_once ("inc/do.user.php");
	break;
	
	case "comment" :
		require_once ("inc/do.comment.php");
	break;

	case "clan" :
		require_once ("inc/do.clan.php");
	break;
	
	case "language" :
		require_once ("inc/do.language.php");
	break;
	
	case "new_seasonJGA" :
		require_once ("inc/do.new_season.php");
	break;
	
	default: header("Location: ?/");
}

if ($error_msg <> "") {
	$template->load_template('mod_error');
	$MARKERS = Array (
		"ERROR"			=> $error_msg,
		"L_ERROR"		=> $dict->data['error'],
	);
	$template->assign_variables($MARKERS);
	$content_data .= $template->build('main') or die("error building: error\main");
} else if ($notice_msg <> "") {
	$template->load_template('mod_notice');
	$MARKERS = Array (
		"NOTICE"		=> $notice_msg,
		"L_NOTICE"		=> $dict->data['notice'],
	);
	$template->assign_variables($MARKERS);
	$content_data .= $template->build('main') or die("error building: notice\main");
} else if ($BACKADDRES <> "") header("Location: $BACKADDRES"); else header("Location: /")

?>