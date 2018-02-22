<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2009 ConnecT
// Module:	Core
// Item:	Login
// Version:	0.0.4	05.10.2009
/////////////////////////////////////////////

if (!defined("NFK_LIVE")) die();

if ($_POST['f_action'] == 'login')
{
	//
	// Login
	//

	$temp_user['login'] = $_POST["f_login"];
	$temp_user['password'] = md5($_POST["f_password"]);

	if ($this_user = $user->fetchName($temp_user['login'])) {
		if ($this_user["password"] == $temp_user["password"]) {
			// login/pass accepted
			// 
			
			$me->assign($this_user);
			$_SESSION['me_data'] = $me->data;
			
			$_SESSION['me_data']['psid'] = newSID();
			$xdata = $_SESSION['me_data'];
			/*$db->insert('sessions', Array(
				'sessionIP'	=> "'".$_SERVER['REMOTE_ADDR']."'",
				'playerID'	=> $_SESSION['me_data']['id'],
				'ttl'		=> "'".date("Y-m-d H:i:s",strtotime('+30 seconds'))."'",
				'psid'		=> "'".$_SESSION['me_data']['psid']."'",
			));*/
			
			setCookie("_nll_data", $temp_user['login'] , time()+(60*60*24*7), "/"); 
			setCookie("_nlp_data", $temp_user['password'], time()+(60*60*24*7), "/"); 
			$db->update('users',"loginDate = NOW(), loginIP = '$_SERVER[REMOTE_ADDR]'","WHERE `id` = $xdata[id] LIMIT 1");
			// redirect
			
			//header('Location: '.$PHP_SELF.'?/'.$CFG['default_page']);
			$REFSTR = explode('/', $_SERVER['HTTP_REFERER']);
			if (($REFSTR[4] <> "do") or ($REFSTR[4] <> "login")){
				echo header("Location: $_SERVER[HTTP_REFERER]");
			} else echo header("Location: /");
			
		} else {
				$error_msg = $dict->data['invalid_login_or_pass'];
			}
	} else {
			$error_msg = $dict->data['invalid_login_or_pass'];
		}
}

if ($error_msg <> "") {
	$template->load_template('mod_error');
	$MARKERS = Array (
		"ERROR"			=> $error_msg,
		"L_ERROR"		=> $dict->data['error'],
	);
	$template->assign_variables($MARKERS);
	$content_data .= $template->build('main') or die("error building: login\main");
}
?>