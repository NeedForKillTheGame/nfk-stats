<?php
if (!defined("NFK_LIVE")) define("NFK_LIVE", true);
ini_set('display_errors',0);
error_reporting(E_ERROR | E_WARNING | E_PARSE);
// Configuration
require("inc/config.inc.php");
// Functions
require("inc/functions.inc.php");
// Classes
require("inc/classes.inc.php");
include('inc/AntiMat.class.php');
// db connect
$db = new db();
$db->connect($CFG['db_host'],$CFG['db_login'],$CFG['db_pass'],$CFG['db_name'],$CFG['db_prefix']);

$act = $_GET['action'];

switch ($act) {
	case 'server':
		// test
		
	break;
	
	case 'test':
		$str = $_GET['str'];
		echo $str.'<br>';
		$am = new AntiMat;
		$str = $am->filter($str);
		echo $str.'<br>';
	break;
	
	default: die("Hello World!");
}
echo '<br>end';
?>