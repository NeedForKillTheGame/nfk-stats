<?php
if (!defined("NFK_LIVE")) die("error");

// Delete old demos
/*
$demos_dir = "demos/";
$res = $db->select("matchID, dateTime, demo, dlnum","matchList",
						"WHERE demo <> '' 
						AND dateTime <= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
						AND dlnum < 4 
						ORDER BY dateTime DESC");
foreach ($res as $row) {
	if (file_exists($demos_dir.$row['demo'])) {
		if (unlink($demos_dir.$row['demo'])) {
			$db->update("matchList","demo = ''","WHERE matchID = $row[matchID]");
		}
	}
}
*/
// END

// Update all rating
$res = $db->select("AltStat_Players.PlayerId, CtfReiting, TdmReiting, DmReiting, nfkLive_ladderDUEL.score AS DuelReiting","",
						"FROM AltStat_Players 
						INNER JOIN nfkLive_ladderDUEL ON nfkLive_ladderDUEL.playerID = AltStat_Players.PlayerId");
foreach ($res as $row) {
	$AllRating = $row['CtfReiting'] + $row['TdmReiting'] + $row['DmReiting'] + $row['DuelReiting'];
	$db->update2("AltStat_Players","AllRating=$AllRating","WHERE PlayerId=$row[PlayerId]");
}
// END

$db->delete('onServers', "serverName='Null' AND playerName='Null'");
?>