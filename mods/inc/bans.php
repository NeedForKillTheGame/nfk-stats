<?php 
	$ipLong = ip2long($_SERVER['REMOTE_ADDR']);
	$ipLong = sprintf("%u", $ipLong);
	$res = $db->select('*','bans',"WHERE banLevel=1 AND (banMaskStart < '$ipLong' AND banMaskEnd > '$ipLong') AND (banEnd>NOW()) LIMIT 1");
	// бан найден
	if (count($res) > 0) {
		$ban = $res[0];
		die("<div align='center'><b>You are banned! Ban expire at $ban[banEnd]<br>Reason: $ban[banReas]</b></div>"); 
	}
?>