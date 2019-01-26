<?php
if (!defined("NFK_LIVE")) die(); 

switch ( $PARAMSTR[3] ) {

	case "add" :
		$BACKADDRES = $_SERVER['HTTP_REFERER'];
		if ($xdata['login'] <> NULL) {
			if (($xdata['playerID']<>0) and ($xdata['playerID']<>"")) {
				$clan = $db->select("*","clanList","WHERE clanID=$_POST[clanID]");
				$clan = $clan[0];
				if (($xdata['playerID']==$clan['leaderID']) or ($xdata['access']==3)) {
					if ($_POST['plr_name']<>"") {
						$_POST['plr_name'] = $db->clean($_POST['plr_name']);
						$plr_id = $db->select("playerID","playerStats","WHERE name='$_POST[plr_name]'");
						$plr_id = $plr_id[0]['playerID'];
					} else if (is_numeric($_POST['plr_id'])) {
						$plr_id = $_POST['plr_id'];
					} else break;
					if (!is_numeric($plr_id)) break;
					$plr_cid = $db->select("clanID","playerStats","WHERE playerID='$plr_id'");
					$plr_cid = $plr_cid[0]['clanID'];
					if ($plr_cid <> 0) {$error_msg = "Player already in clan"; break;};
					$db->update("playerStats","clanID = '$clan[clanID]', clanScore = 0, ClanGames = 0","WHERE playerID='$plr_id' LIMIT 1");
					$db->update("clanList","players = players + 1","WHERE clanID='$clan[clanID]' LIMIT 1");
				} else $error_msg = 'You are not clan leader';
			} else $error_msg = 'Player not attached to you accaunt';
		} else $error_msg = 'You are not logged';
	break;
	
	case "remove" :
		$BACKADDRES = $_SERVER['HTTP_REFERER'];
		if ($xdata['login'] <> NULL) {
			if (($xdata['playerID']<>0) and ($xdata['playerID']<>"")) {
				$clan = $db->select("*","clanList","WHERE clanID=$_POST[clanID]");
				$clan = $clan[0];
				if (($xdata['playerID']==$clan['leaderID']) or ($xdata['access'])==3){
					if ($_POST['rem_name']<>"") {
						$_POST['rem_name'] = $db->clean($_POST['rem_name']);
						$plr_id = $db->select("playerID","playerStats","WHERE name='$_POST[rem_name]'");
						$plr_id = $plr_id[0]['playerID'];
					} else if (is_numeric($_POST['rem_id'])) {
						$plr_id = $_POST['rem_id'];
					} else break;
					if (!is_numeric($plr_id)) break;
					$plr_cid = $db->select("clanID","playerStats","WHERE playerID='$plr_id'");
					$plr_cid = $plr_cid[0]['clanID'];
					if ($plr_cid <> $clan['clanID']) break;
					$db->update("playerStats","clanID = '0', clanScore = '0'","WHERE playerID='$plr_id' LIMIT 1");
					$db->update("clanList","players = players - 1","WHERE clanID='$clan[clanID]' LIMIT 1");
				} else $error_msg = 'You are not clan leader';
			} else $error_msg = 'Player not attached to you accaunt';
		} else $error_msg = 'You are not logged';
	break;
	
	case "leader" :
		$BACKADDRES = $_SERVER['HTTP_REFERER'];
		if ($xdata['login'] <> NULL) {
			if (($xdata['playerID']<>0) and ($xdata['playerID']<>"")) {
				$clan = $db->select("*","clanList","WHERE clanID=$_POST[clanID]");
				$clan = $clan[0];
				if (($xdata['playerID']==$clan['leaderID']) or ($xdata['access'])==3) {
					if ($_POST['cl_name']<>"") {
						$cl_name = $_POST['cl_name'];
						$cl_name = $db->clean($cl_name);
						$cl_id = $db->select("playerID","playerStats","WHERE name='$cl_name'");
						$cl_id = $cl_id[0]['playerID'];
					} else if (is_numeric($_POST['cl_id'])) {
						$cl_id = $_POST['cl_id'];
					} else break;
					if (!is_numeric($cl_id)) break;
					$cl_cid = $db->select("clanID","playerStats","WHERE playerID='$cl_id'");
					$cl_cid = $cl_cid[0]['clanID'];
					if ($cl_cid <> $clan['clanID']) {$error_msg = "This player is not in your clan"; break;};
					$db->update("clanList","leaderID = $cl_id","WHERE clanID='$clan[clanID]' LIMIT 1");
				} else $error_msg = 'You are not clan leader';
			} else $error_msg = 'Player not attached to you accaunt';
		} else $error_msg = 'You are not logged';
	break;
	
	case "create" :

	break;
	
	case "delete" :

	break;
	
	default: header("Location: /");
}
?>