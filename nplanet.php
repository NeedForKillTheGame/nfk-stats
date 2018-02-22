<!--
# Online NFK planet scanner unit for pff.clan.su  
# by boobl, bitnik, coolant                                                       
# Updated: 04.01.2010                                  
# <a title="Подключиться" href="nfk://'.$server["IP"].'"></a>
-->

<html>
<head>
<title>NFK Planet Scaner</title>
<link type="text/css" rel="StyleSheet" href="http://needforkill.ru/_st/my.css" />
<style>
body {background: #ebebeb;}
.eBlock1 {font-size:13px;font-family:Tahoma,Geneva,sans-serif;color:#3a3a3a;background: #ebebeb;}
</style>
<?php /*
<style>
BODY,td,th {
 font: 11px Verdana, Arial, Helvetica, sans-serif;
SCROLLBAR-FACE-COLOR:#7284A0;
SCROLLBAR-SHADOW-COLOR:#E8EAED;
SCROLLBAR-HIGHLIGHT-COLOR:#7284A0;
SCROLLBAR-3DLIGHT-COLOR:#E8EAED;
SCROLLBAR-DARKSHADOW-COLOR:#7284A0;
SCROLLBAR-TRACK-COLOR:#B7C0CF;
SCROLLBAR-ARROW-COLOR:#E8EAED;
}
</style>
<BODY BGCOLOR="#E8EAED" 
   TEXT="#354D73" 
   LINK="#354D73"
   VLINK="#354D73"
   ALINK="#354D73"
   >
*/

?>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
</head>
<BODY>
<?php

require_once("mods/inc/nfk_planet.inc.php");
function stripNameColor($nick)
{
	for ($i = 0; $i<=strlen($nick); $i++ )
	{
		if (($nick[$i] != '^') and ($nick[$i-1] != '^'))
				$pure .= $nick[$i];
	}
	
	return $pure;
}
function cmpServers($a, $b)
{
    if ($a['Players'] == $b['Players']) {
        return 0;
    }
    return ($a['Players'] > $b['Players']) ? -1 : 1;
}

$servers = nfkpl_getServers();
$playersCount = 0;
if (count($servers) == 0)
{
    $html .= 'Планета временно пуста';
}
else
{
usort($servers, "cmpServers");
$html .= '<table class="eBlock1">'
	.'<tr><td width="190"><b>Хост</b></td>'
                .'<td width="130"><b>Карта</b></td>'
                .'<td width="120"><b>Тип</b></td>'
                .'<td width="120"><b>Игроки</b></td>'
                .'<td width="120"><b>IP</b></td></tr>';
    foreach ($servers as $key => $server)
    {preg_replace('/\W/', '', $a);
		$hostlink = stripNameColor($server['Hostname']);
		$hostlinkx = $hostlink;
		$hostlink = str_replace("#", "%23", $hostlink); 
		//$link = ((substr($server["Hostname"], 0, 3) == "Rip") or (substr($server["Hostname"], 0, 9) == "[twuo.ru]")) ? ('<a target="_blank" href="/server/'.$hostlink.'">'.$hostlinkx.'</a>'):($hostlinkx);
       	$link = '<a target="_blank" href="/server/'.$hostlink.'">'.$hostlinkx.'</a>';
		$html .= <<<HTML
						<tr>
							<td>$link</td>
							<td>$server[Map]</td>
							<td>$server[Gametype]</td>
							<td>$server[Players]/$server[Maxplayers]</td>
							<td><a href="nfk://$server[IP]:$server[Port]">$server[IP]:$server[Port]</a></td>
						</tr>
HTML;
        $playersCount += $server["Players"]; 
    }
}

$html .= '</table>';

//print "<b>NFK Planet - ".count($servers)." servers / ".$playersCount." players</b>";
print $html;

// ===============


$Data2 = "+ VISITOR - IP: ".$_SERVER['REMOTE_ADDR'].""; 
$Data4 = " - DATE: ".date("d.m.y").""; 
$Data5 = " - TIME: ".date("H:i:s")."\n"; 

$File = "nplanet.txt"; 
//$Handle = fopen($File, 'a');
//fwrite($Handle, $Data2); 
//fwrite($Handle, $Data4); 
//fwrite($Handle, $Data5); 
//fclose($Handle); 
//===============

//$maplist = file("maplist.txt");
//foreach($maplist as $key => $row)
//    if (substr($row, 0, 2) == '//') unset($maplist[$key]);
//$Vars['MAPLIST'] = implode('<br />', $maplist);

?>
</body>
</html>
