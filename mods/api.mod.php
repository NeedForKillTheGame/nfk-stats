<?php

if (!defined("NFK_LIVE")) die();
$players = '';
if ($PARAMSTR[2] == 'server') {
	$hostName = urldecode($PARAMSTR[3]);
	if ($hostName == '') die('error');
	$hostName = $db->clean($hostName);
	$res = $db->select('*','onServers',"WHERE serverName = '$hostName'");
	foreach ($res as $row) {
		$plr = $player->fetchName(addslashes($row['playerName']));
		$row['name'] = $row['playerName'];
		$PLAYER_NAME_URL	= ($plr) ? getIcons($plr,true,true,true,true):
										getIcons($row,false,false,false);
		$NUM				= ++$i;


	$players .= <<<HTML
	<tr>
		<td>$NUM</td>
		<td>$PLAYER_NAME_URL</td>
	</tr>
HTML;
	}
	$html = <<<HTML
<html>
<body>
	<table name="$hostName">
$players
	</table>
</body>
</html>
HTML;
die($html);
} else  die('empty');
?>