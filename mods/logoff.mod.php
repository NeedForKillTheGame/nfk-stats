<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2009 ConnecT
// Module:	Core
// Item:	Logoff
// Version:	0.0.2	05.10.2009
/////////////////////////////////////////////


if (!defined("NFK_LIVE")) die();


//
// Logoff
//
$null_array = Array();

$db->delete('sessions', "psid = '".$_SESSION['me_data']['psid']."'");

$me->assign($null_array);
$_SESSION['me_data'] = $null_array;
	
setCookie("_nll_data", '' , 0, "/"); 
setCookie("_nlp_data", '', 0, "/");
	
//header('Location: '.$PHP_SELF.'?/'.$CFG['default_page']); 

$REFSTR = explode('/', $_SERVER['HTTP_REFERER']);
if (($REFSTR[4] <> "do") or ($REFSTR[4] <> "login")){
	echo header("Location: $_SERVER[HTTP_REFERER]");
} else echo header("Location: /");

?>