<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2009 ConnecT
// Item:	Nodes
// Version:	0.0.2	24.04.2009
/////////////////////////////////////////////

if (!defined("NFK_LIVE")) die();

$node = clean('str',$PARAMSTR[2]);

// we use alias now
//if (!is_numeric($node)) die();

$template->load_template('mod_node');

$res = $db->select('*','nodes',"where `alias` = '$node'");

if ($res[0] != '')
{
	$MARKERS = Array
		(
			"NODE_TITLE"		=> $res[0]['title'],
			"NODE_ALIAS"		=> $res[0]['alias'],
			"NODE_BODY"		=> $res[0]['body'],
			"NODE_POSTER_ID"	=> $res[0]['poster_id'],
			"NODE_POSTED"		=> $res[0]['posted'],
			"THEME_ROOT"		=> $CONFIG_root."themes/".$CFG['theme'],
		);
		
	$template->assign_variables($MARKERS);

	$content_data .= $template->build('main') or die("error building: node\main");
}

?>