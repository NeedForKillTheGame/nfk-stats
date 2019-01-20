<?php
if (!defined("NFK_LIVE")) die(); 

switch ( $PARAMSTR[3] ) {

	case "add" :	
		if ($_POST['uname'] <> '') {
			$db->insert("bans",$cell = Array(
					"banIP"			=> "'$_SERVER[REMOTE_ADDR]'",
					"banEnd"		=> "'2013-07-05 21:30:00'",
					"banReas"		=> "'Bot'",));
			die('OK');
		};
		$BACKADDRES = $_SERVER['HTTP_REFERER'];
		
		$ipLong = ip2long($_SERVER['REMOTE_ADDR']);
		$ipLong = sprintf("%u", $ipLong);
		$res = $db->select('*','bans',"WHERE banLevel=2 AND (banMaskStart < '$ipLong' AND banMaskEnd > '$ipLong') AND (banEnd>NOW()) LIMIT 1");
		// бан найден
		if (count($res) > 0) {
			$ban = $res[0];
			break;
		}
		
		
		if (is_numeric($PARAMSTR[4])) $moduleID = $PARAMSTR[4]; else $moduleID = 2; 
		
		if  (is_numeric($_POST['materialID'])) $materialID = $_POST['materialID']; else break;
		$cmt = $_POST['cmessage'];
		$cmt = htmlspecialchars( $cmt, ENT_QUOTES );
		$cmt = preg_replace("#\r\n#i","<br>", $cmt);
		if (!$cmt) break;
		$NickName = ($xdata['login']==NULL) ? parseString($_POST['afxad']) : getUserName($xdata);
		$NickName = mysqli_real_escape_string($db->link,$NickName);
		
		
		if (($cmt <> "") & ($NickName<>"")) {
			//require_once('inc/AntiMat.class.php');
			//$am = new AntiMat();
			//$f_cmt = $am->filter($cmt);
			$f_cmt = $cmt; // FIXME: (harpywar) upper code does not work by a reason
			$cell = Array (
				"materialID"	=> $materialID,
				'moduleID'		=> $moduleID,
				"author"		=> "'$NickName'",
				"playerID"		=> ($xdata['playerID']<>NULL) ? $xdata['playerID'] : "0",
				"comment"		=> "'$f_cmt'",
				"orig_cmt"		=> "'$cmt'",
				"postTime"		=> "CURRENT_TIMESTAMP",
				"userIP"		=> "'$_SERVER[REMOTE_ADDR]'",
				"country"		=> "'".ip2country($_SERVER['REMOTE_ADDR'])."'",
			);
			$db->insert("comments",$cell);
			if ($moduleID == 3) {
                $db->update("tr_tourneys","comments = comments + 1","WHERE `tourID` = $materialID",false);
            } elseif ($moduleID == 4) {
                $db->update("news","comments = comments + 1","WHERE `news_id` = $materialID");
            } else {
                $db->update("matchList","comments = comments + 1","WHERE `matchID` = $materialID");
            }
		}
	break;
	
	case "edit" :

	break;
	
	case "delete" :
		$BACKADDRES = $_SERVER['HTTP_REFERER'];
		$cmtID = $PARAMSTR[4];
		$materialID = $PARAMSTR[5];
		if ((!is_numeric($cmtID)) or (!is_numeric($materialID))) break;
		if ($xdata['access']>=3) {
			$cmtID = $PARAMSTR[4];
			if (!is_numeric($cmtID)) break;
			$moduleID = $db->select('moduleID','comments',"WHERE cmtID='$cmtID'");
			$moduleID = $moduleID[0]['moduleID'];
			$db->delete("comments","cmtID='$cmtID'");
			if ($moduleID == 3) {
                $db->update("tr_tourneys","comments = comments - 1","WHERE `tourID` = $materialID",false);
            } elseif ($moduleID == 4) {
                $db->update("news","comments = comments - 1","WHERE `news_id` = $materialID");
            } else {
                $db->update("matchList","comments = comments - 1","WHERE `matchID` = $materialID");
            }
		};
	break;
	
	default: header("Location: /");
}


?>