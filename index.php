<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2009 ConnecT, 2011 coolant
// Module:	CORE
// Item:	Index
// Version:	0.1.14	14.07.2011
/////////////////////////////////////////////
//if ($_SERVER['REMOTE_ADDR']<>'158.46.2.34' ) die("<h1 align='center'>SITE UNDER CONSTRUCTION</h1>");


if (!defined("NFK_LIVE")) define("NFK_LIVE", true);
ini_set('display_errors', 1);
error_reporting(E_ERROR | E_WARNING | E_PARSE);

// Page Generated Time
$start_time = microtime();
$start_array = explode(" ",$start_time);
$start_time = $start_array[1] + $start_array[0];
//
session_name("_nls_data");
if (!session_id()) session_start();

// Configuration
require("inc/config.inc.php");

// Functions
require("inc/functions.inc.php");

// Classes
require("inc/classes.inc.php");

// skin
$template = new skin();
// db connect
$db = new db();
$db->connect
(
    $CFG['db_host'],
    $CFG['db_login'],
    $CFG['db_pass'],
    $CFG['db_name'],
    $CFG['db_prefix']
);

// dictionary
$dict = new dictionary();
//$dict->pick_language($CFG['language']);
$lang_arr = array("ru", "en");
if (in_array($_COOKIE['_nllang'],$lang_arr)) { 
	$dict->pick_language($_COOKIE['_nllang']); 
		$CUR_LANG = $_COOKIE['_nllang'];
	} else if ( substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2) == 'ru' ) {  
		$dict->pick_language("ru"); 
		$CUR_LANG = 'ru';
		setCookie("_nllang", "ru" , 0, "/"); 
	} else {
		$dict->pick_language("en"); 
		setCookie("_nllang", "en" , 0, "/"); 
		$CUR_LANG = 'en';
	};

require "mods/inc/bans.php";

// user
$me = new user();
$user = new user();
$player = new player();

// mini calendar
//$minical = new mini_calendar();

//
// Default Values
//
date_default_timezone_set('Europe/Moscow');

$THEME_ROOT = $CFG['root']."/themes/".$CFG['theme'];
//
// Settings
//
$PHP_SELF = "http://".$_SERVER['HTTP_HOST']."/";
$CUR_ADDRES = "/";
// merge with config form db
/*
$DBCFG = $db->select("*","settings","");
$DBCFG = $DBCFG[0];
print_r($DBCFG);
$CFG = array_merge($CFG,$DBCFG);
*/

//
// Main input
//
$PARAMSTR = explode('/', $_SERVER['REQUEST_URI']);
// paramstr[0] is always null
// paramstr[1] module or node alias
// paramstr[2] module parameter

//
// Module
//
$module = ($PARAMSTR[0] != '') ? ($PARAMSTR[0]) : ($PARAMSTR[1]);
$module = clean($module);

require("mods/inc/autologin.inc.php");

// wtf?
if ($_SESSION['me_data'] != "") $me->assign($_SESSION['me_data']);
$xdata = $_SESSION['me_data'];

// default page title
$page_title = $dict->data['main_page'];
$page_name = $dict->data['main_page'];

require_once('inc/autoloader.php');
Autoloader::register();
// default module
if ($module == '') $module = $CFG['default_page'];


if (file_exists("./mods/".$module.".mod.php")) {
    // this is module
	$CUR_ADDRES .=$module."/";
    $template->setRoute($module);

    include("./mods/".$module.".mod.php");

} else {
    // it could be node then, check it
    $res = $db->select("id","nodes","where `alias` = '$module'");
    
    if ($res[0]['id'] != '') {
		// ok it's a node, push alias as a parameter
		$PARAMSTR[2] = $module;
		include("./mods/node.mod.php");   
    } else {
		// nope, so show this instead of error
		$PARAMSTR[2] = "under-construction";
		include("./mods/node.mod.php");
    }
}
	

$template->load_template('overall');

//
// User Block
//
$MARKERS = Array
	(
		"SELF"				=> $PHP_SELF,
		"THEME_ROOT"		=> $CFG['root']."themes/".$CFG['theme'],
		
		"L_OR"				=> $dict->data['or'],
	);
if ($xdata['login'] == NULL) {
	// Login block for unregistred
	$MARKERS += Array
		(
			"GTW_LOGIC"			=> false,
			// dict
			"L_ENTER"			=> $dict->data['enter'],
			"L_LOGIN"			=> $dict->data['login'],
			"L_LOGIN_USER"		=> $dict->data['login_user'],
			"L_PASSWORD"		=> $dict->data['password'],
			"L_CREATE_ACC"		=> $dict->data['create_acc'],
			"L_REGISTER"		=> $dict->data['register'],
		);
} else {
	// For logged on users
	$MARKERS += Array
		(
			"GTW_LOGIC"			=> true,
			"NULL"				=> NULL,
		);
		
	$MARKERS_ATT = Array
		(
			"GTW_LOGIC"			=> ($xdata['playerID'] <> 0) ? (true) : (false),
			"USER_LOGIN"		=> $xdata['login'],
			"NICK_NOCOLOR"		=> getUserName($xdata),
			"MODELSKIN_LOW"		=> $xdata['model'],
			"DATE_REGISTERED"	=> date("F j,Y",strtotime($xdata['regDate'])),
			
			"L_LOGOFF"			=> $dict->data['logoff'],
			"L_WELCOME" 		=> $dict->data['welcome'],
			
			"SELF"				=> $PHP_SELF,
		);
	if ($xdata['playerID'] == 0) {
		$MARKERS_ATT += Array
			(
				"L_ATTACH_PLAYER"		=> $dict->data['attach_player'],
				"L_ATTACH_PLAYERTOACC"	=> $dict->data['attach_player_to_acc'],
				"L_BY_PLR_NAME"			=> $dict->data['by_plr_name'],
				"L_BY_PLR_ID"			=> $dict->data['by_plr_id'],
				"L_ATTACH"				=> $dict->data['attach'],
				"L_OR"					=> $dict->data['or'],
			);
	} else {
		$MARKERS_ATT += Array
			(
				// dict
				"NULL" 				=> NULL,
				"L_PROFILE" 		=> $dict->data['profile'],
			);
	}
	$template->assign_variables($MARKERS_ATT);
	$attach_player = $template->build('if_attached') or die("error building: if_attached");
}
$MARKERS += Array
	(
		"NULL"				=> NULL,
		"G_ATTACH_PLAYER"	=> $attach_player,
	);
$template->assign_variables($MARKERS);
$TEMPLATE_login = $template->build('if_login') or die("error building: if_login");


//
// Build main
//

//
$end_time = microtime();
$end_array = explode(" ",$end_time);
$end_time = $end_array[1] + $end_array[0];
$gentime = $end_time - $start_time;
$gentime = round($gentime,3);
//

$MARKERS = Array
(

	"PAGE_TITLE"		=> $page_title,
	"PAGE_NAME"			=> $page_name,
	"SUB_NAME"			=> $sub_name,

	"C_DATA_ROW"		=> $o_data_row,
	"IF_TEST"			=> $if_test,
	"OVERLAY_CONTENT"	=> $OVERLAY_CONTENT,
	"CONTENT"			=> $content_data,
	"SUBMENU"			=> $TEMPLATE_submenu,
	"G_FORM_LOGIN"		=> $TEMPLATE_login,
	"IF_LOGGED2"		=> $TEMPLATE_logged2,
	"IF_LOGGED3"		=> $TEMPLATE_logged3,
	
	"ROOT_PATH"			=> $CFG['root'],
	"THEME_ROOT"		=> $THEME_ROOT,
	"SELF"				=> $PHP_SELF,
	
	// dict	
	"L_VALUE"			=> "Value",
	"L_BOOL"			=> "Boolean",
	"L_MAIN_PAGE"		=> $dict->data['main_page'],
	"L_MATCHES" 		=> $dict->data['matches'],
	"L_DEMOS" 			=> $dict->data['demos'],
	"L_COMMENTS" 		=> $dict->data['comments'],
	"L_SEARCH"			=> $dict->data['search'],
	"L_LADDER" 			=> $dict->data['ladder'],
	"L_CLAN_LADDER"		=> $dict->data['clan_ladder'],
	"L_TOUR_LADDER"		=> $dict->data['tour_ladder'],
	"L_TOURNEYS"		=> $dict->data['tourneys'],
	"L_STATS_FROM"		=> $dict->data['stats_from_servers'],
	"L_VERSION"			=> $dict->data['version'],
	"L_BY"				=> $dict->data['by'],
	"L_PAGE_GEN_IN"		=> $dict->data['page_gen_in'],
	"L_SEC"				=> $dict->data['secunds'],
	"L_SEASONS"			=> $dict->data['seasons'],
	"L_MAPS"			=> $dict->data['maps'],
    "L_NEWS"			=> $dict->data['news'],
	"ENGINE_VERSION"	=> "1.3.1",
	"PAGE_GEN_TIME"		=> $gentime,
);
$template->assign_variables($MARKERS);
$res = $template->build('main') or die("error building: main");

$SITE .= $res;
print $SITE;

?>