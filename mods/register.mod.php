<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2009 ConnecT
// Item:	Register
// Version:	0.0.2	03.06.2010
/////////////////////////////////////////////

if (!defined("NFK_LIVE")) die();

$template->load_template('mod_register');

// TODO: if not looged on check

	/* 
	 * Generate key for captcha
	 */
	$regCode = rand(100000,9999999);
	$regCode = dechex($regCode);
	$_SESSION['regCode'] = $regCode;
	$regCrypt = hexdec($regCode)+date("dm");
	

	$MARKERS = Array
		(
			"CAPTCHA_KEY"		=> $regCrypt,
			"THEME_ROOT"		=> $CFG['root']."/themes/".$CFG['theme'],
			"ROOT_PATH"		=> $CFG['root'],
		);
		
	$template->assign_variables($MARKERS);

	$OVERLAY_CONTENT .= $template->build('main') or die("error building: register\main");
