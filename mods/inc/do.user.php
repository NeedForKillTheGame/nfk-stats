<?php
if (!defined("NFK_LIVE")) die(); 

switch ( $PARAMSTR[3] ) {

	case "attach" :
		if ($xdata['login'] <> NULL) {
			if ($xdata['playerID']==0) {
				if ($_POST['plr_name']<>'') {
					if (!is_numeric($_POST['plr_name'])) {
						$att_name = parseString($_POST['plr_name']);
						$att_name = $db->clean($att_name);
						$q_plr_id = $db->select("`playerID`, `userID`","playerStats","WHERE `name`='$att_name' LIMIT 1");
						$plr_id = $q_plr_id[0]['playerID'];
						$plr_userid = $q_plr_id[0]['userID'];
						if (!is_numeric($plr_id)) {$error_msg ="PLAYER NOT FOUND"; break;}
						if ($plr_userid == 0) {
							$db->update("playerStats","userID = '$xdata[id]'","WHERE playerID='$plr_id'");
							$db->update("users","playerID = '$plr_id'","WHERE id='$xdata[id]'");
							$xdata['playerID'] = $plr_id;
							$_SESSION['me_data']['playerID'] = $plr_id;
						} else $error_msg = 'Player already attached';
					} else $error_msg = 'Player not found';
				} else if (is_numeric($_POST['plr_id'])) {
					$plr_id = $_POST['plr_id'];
					$q_plr_id = $db->select("`userID`","playerStats","WHERE `playerID`='$plr_id' LIMIT 1");
					$plr_userid = $q_plr_id[0];
					$plr_userid = $plr_userid['userID'];
					if (!is_numeric($plr_id)) Die('ERROR PLAYER NOT FOUND');
					if ($plr_userid == 0) {
						$db->update("playerStats","userID = '$xdata[id]'","WHERE playerID='$plr_id'");
						$db->update("users","playerID = '$plr_id'","WHERE id='$xdata[id]'");
						$xdata['playerID'] = $plr_id;
						$_SESSION['me_data']['playerID'] = $plr_id;
					} else $error_msg = 'Player already attached';
				} else $error_msg = 'Invalid Player ID or Name';
			} else $error_msg = 'Your account already has been attaching';
		} else $error_msg = 'You are not logged';
		$notice_msg = "Player successfully attached";
	break;
	
	case "rename" :
		$BACKADDRES = $_SERVER['HTTP_REFERER'];
		if ($xdata['login'] <> NULL) {
			if (($xdata['playerID']<>0) and ($xdata['playerID']<>"")) {
				$newnick = $_POST['nickname'];
				if ( (!is_numeric($newnick)) and ($newnick<>'') ) {
					$newnick = $db->clean($newnick);
					$sql = $db->select("`playerID`","playerStats","WHERE `name`='$newnick' LIMIT 1");
					if (count($sql) == 0) {
						$db->update("playerStats","name = '$newnick', nick = '$newnick'","WHERE playerID='$xdata[playerID]' LIMIT 1");
						//unset($_SESSION['me']['name']);
						$_SESSION['me_data']['name'] = $newnick;
					} else $error_msg = 'New nick name already use';
				} else $error_msg = 'New nick name is invalid';
			} else $error_msg = 'Player not attached to you accaunt';
		} else $error_msg = 'You are not logged';
	break;
	
	case "changepass" :
		$BACKADDRES = $_SERVER['HTTP_REFERER'];
		if ($xdata['login'] <> NULL) {
			if (($xdata['playerID']<>0) and ($xdata['playerID']<>"")) {
				$newpass = $_POST['newpass'];
				if ( $newpass<>'' ) {
					$newpass = md5($newpass);
					$db->update("users","password = '$newpass'","WHERE playerID='$xdata[playerID]' LIMIT 1");
					//$_SESSION['me_data']['password'] = $newpass;
					setCookie("_nlp_data", $newpass, time()+(60*60*24*7), "/"); 
					$notice_msg = 'Пароль успешно изменён';
				} else $error_msg = 'New nick name is invalid';
			} else $error_msg = 'Player not attached to you accaunt';
		} else $error_msg = 'You are not logged';
	break;
	
	case "leaveclan" :
		$BACKADDRES = $_SERVER['HTTP_REFERER'];
		if ($xdata['login'] <> NULL) {
			if (($xdata['playerID']<>0) and ($xdata['playerID']<>"")) {
				$plr_id = $xdata['playerID'];
				$plr_cid = $db->select("`clanID`","playerStats","WHERE `playerID`='$plr_id' LIMIT 1");
				$plr_cid = $plr_cid[0]['clanID'];
				if ($plr_cid == 0) { $error_msg = "You are not in clan";  break;};
					$db->update("playerStats","clanID = '0', clanScore = '0'","WHERE playerID='$plr_id' LIMIT 1");
					$db->update("clanList","players = players - 1","WHERE clanID='$plr_cid' LIMIT 1");
			} else $error_msg = 'Player not attached to you accaunt';
		} else $error_msg = 'You are not logged';
	break;
	
	case "register" :
		$BACKADDRES = "";
		if ((trim($_POST['login'])<>'') && (trim($_POST['password'])<>'') && (trim($_POST['email'])<>'')) {
			$login = $db->clean(trim($_POST['login']));
			$password = md5($_POST['password']);
			$email = $db->clean(trim($_POST['email']));
			
			$res = $db->insert('users', Array(
				'login'			=> "'$login'",
				'password'		=> "'$password'",
				'email'			=> "'$email'",
				'access'		=> "'1'",
				'playerID'		=> "'0'", 
				'regIP'			=> "'$_SERVER[REMOTE_ADDR]'",
				'regDate'		=> "NOW()",
			));
			
			if (is_numeric($res)) {
				$notice_msg = "User <b>$login</b> registred! You can Login now";
			} else { 
				if (mysql_errno() == '1062') $error_msg = "<b>$login</b> already registred"; else
					$error_msg = mysql_error();
			};
		} else $error_msg = "Invalid Login, Password or Email";
	break;
	
	case "reg-tour" :
		$BACKADDRES = $_SERVER['HTTP_REFERER'];
		if (!is_numeric($PARAMSTR[4])) { 
			$error_msg = "Tourney ID error";  break;
		} else $tourID = $PARAMSTR[4];
		$nowDate = date('Y-m-d H:i:s');
		$res = $db->select('*','tr_tourneys',"WHERE tourID = $tourID AND dateReg<='$nowDate' AND status = 0",false);
		if (count($res)<>1) {$error_msg = "Tourney reg error";  break;}
		$plr_id = $xdata['playerID'];
		if ($xdata['login'] <> NULL) {
			if (($plr_id<>0) and ($plr_id<>"")) {
				$res = $db->select('*','tr_playersReg',"WHERE playerID=$plr_id AND tourID = $tourID",false);
				if (count($res)==1) {$error_msg = "Already registred";  break;}
				$res = $db->insert('tr_playersReg', Array (
							'tourID' => $tourID, 'playerID' => $plr_id, 'dateReg' => 'NOW()'
					),false);
				
				if ($res > 0) {
					$db->update('tr_tourneys','regNum = regNum+1',"WHERE tourID=$tourID",false);
				}
			} else $error_msg = 'Player not attached to you accaunt';
		} else $error_msg = 'You are not logged';
	break;
	
	case "check-in" :
		$BACKADDRES = $_SERVER['HTTP_REFERER'];
		if (!is_numeric($PARAMSTR[4])) { 
			$error_msg = "Tourney ID error";  break;
		} else $tourID = $PARAMSTR[4];
		$nowDate = date('Y-m-d H:i:s');
		$res = $db->select('*','tr_tourneys',"WHERE tourID = $tourID AND dateCheckin<='$nowDate' AND dateStart>='$nowDate'",false);
		if (count($res)<>1) {$error_msg = "Tourney checkin error";  break;}
		$plr_id = $xdata['playerID'];
		if ($xdata['login'] <> NULL) {
			if (($plr_id<>0) and ($plr_id<>"")) {
				$res = $db->select('*','tr_playersReg',"WHERE playerID=$plr_id AND tourID = $tourID AND status=1",false);
				if (count($res)==1) {$error_msg = "Already checkined!";  break;}
				$db->update('tr_playersReg','status = 1',"WHERE tourID=$tourID AND playerID = $plr_id",false);
				$db->update('tr_tourneys','checkNum = checkNum+1',"WHERE tourID=$tourID",false);
			} else $error_msg = 'Player not attached to you accaunt';
		} else $error_msg = 'You are not logged';
	break;
	
	default: header("Location: /");
}

?>