<?php
/////////////////////////////////////////////
// nfkLive Engine
// Author: 	2009-2010 ConnecT
// Module:	Core
// Item:	Functions
// Version:	0.0.8	04.06.2010
/////////////////////////////////////////////

//
// Get permission state for current user
//
function check_rules($rule)
{
	$me = $me;
	
	if ($me['login'] == "") return false;
	
	$q = mysql_query("select `group_id` from `ct_usergroups` where `user_id` = $me[id]");
		$group_id = mysql_result($q,0);
	
	$q = mysql_query("select `state` from `ct_permissions` where `label` = '$rule' and `group_id` = $group_id");
	
	return @mysql_result($q,0);
}

// ?
function get_user($user_id)
{	
	global $db_link;
	$q = mysql_query("select * from `ct_users` where `id` = $user_id",$db_link);
	return mysql_fetch_array($q);
}

function getPlayer($playerID){	
	global $db;
	if (!is_numeric($playerID)) return 0;
	$res = $db->select('*','playerStats',"WHERE playerID = $playerID");
	if (count($res)==1) return $res[0];
		else return 0;
}

//
// Clean input values
//
function clean($variable,$type = "str")
{
	$type = strtolower($type);
	
	// common cleaning
		$result = $variable;
		$result = strip_tags($result);  // strip HTML tags
		$result = trim($result);		// remove spaces at the beginnig and at the end
		$result = htmlspecialchars(stripslashes($result), ENT_QUOTES);
		$result = str_replace(chr(39), '&#39;', $result);
	// ---
	
	if ($type == "int") 
	{
		/*
		if (!is_int($result))
		{
			// doesn't look like integer for me
			return null;
		}
		*/
	}
	elseif ($type == "date")
	{
		// To check if it's right date format, we need to count ':' and '.' chars 
		// fitting template 'HH:MM:nn dd-mm-yyyy'
		
		//$count = 
		//if ()
	}
	elseif ($type == "str")
	{
		
	}
	elseif ($type == "arr")
	{
	
	
	}
	
	return $result;	
}


/**
 * Return unicode char by its code
 *
 * @param int $u
 * @return char
 */
function unichr($u) {
    return mb_convert_encoding('&#' . intval($u) . ';', 'UTF-8', 'HTML-ENTITIES');
}


/**
 * Convert x.x.x.x to 000000000000
 */
function flatIP($dotIP)
{
	$dotIP = explode('.',$dotIP);
	
	foreach ($dotIP as $ip)
	{
		while (strlen($ip) < 3) { $ip = '0' . $ip; } // 2 > 002
		$result .= $ip;
	}

	return $result;
} 

/**
 *
 */
function win2utf($s)
{
	for($i=0, $m=strlen($s); $i<$m; $i++)
	{
		$c=ord($s[$i]);
		if ($c<=127)
		{$t.=chr($c); continue; }
		if ($c>=192 && $c<=207)
		{$t.=chr(208).chr($c-48); continue; }
		if ($c>=208 && $c<=239)
		{$t.=chr(208).chr($c-48); continue; }
		if ($c>=240 && $c<=255)
		{$t.=chr(209).chr($c-112); continue; }
		if ($c==184) { $t.=chr(209).chr(209);
			continue; };
		if ($c==168) { $t.=chr(208).chr(129);
			continue; };
	}
	echo $t;
	return $t;
}


/**
 * 
 *
 */
function ip2russia($ip)
{
	$data = '<ipquery><fields><city/></fields><ip-list><ip>'.$ip.'</ip></ip-list></ipquery>';
	$url = "http://194.85.91.253:8090/geo/geo.html";
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url); // set url to post to
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
	curl_setopt($ch, CURLOPT_TIMEOUT, 3); // times out after 4s
	curl_setopt($ch, CURLOPT_POST, 1); // set POST method
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data ); // add POST fields
	$result = curl_exec($ch); // run the whole process
	curl_close($ch); 
	
	$result = win2utf(strip_tags($result));
	
	if ($result != '')
	return 'ru';
}

/**
 * Get country by ip
 * @par {'cc2';'cc3'}
 * @return str
 */
function ip2country($ip,$par = 'cc2') {
	global $db;
	$par = strtolower($par);
	if (($par <> 'cc2') or ($par <> 'cc3') or ($par <> 'ccFull')) $par = 'cc2';
	
	// make flat if needed
	//if (strpos($ip,'.') > 0) $flat_ip = flatIP($ip);
	$flat_ip = sprintf("%u", ip2long($ip));
	
	//$res = $db->select('*','geoipDB','WHERE ip_from <= $ip and ip_to >= $ip');
	$res = mysqli_query($db->link,"select $par from nfkLive_geoipDB WHERE `ip_from` <= $flat_ip and ip_to >= $flat_ip");
	$res = mysqli_fetch_assoc($res);
	
	if ($res == '') {
		return 'ru';
	} else return strtolower(implode($res));
} 

/**
 *
 */
function GameType($gt) {
	if (is_numeric($gt)) {
		$gameType[0] = "Deathmatch";
		$gameType[1] = "Duel";
		$gameType[2] = "Team Deathmatch";
		$gameType[3] = "Capture The Flag";
		$gameType[4] = "Rail Arena";
		$gameType[5] = "Trix Arena";
		$gameType[6] = "Practice";
		$gameTyme[7] = "Domination";
		
		return $gameType[$gt];
	} else {
		$gameType['DM'] 	= "Deathmatch";
		$gameType['DUEL'] 	= "Duel";
		$gameType['TDM'] 	= "Team Deathmatch";
		$gameType['CTF'] 	= "Capture The Flag";
		$gameType['RAIL'] 	= "Rail Arena";
		$gameType['TRIX'] 	= "Trix Arena";
		$gameType['PRAC'] 	= "Practice";
		$gameTyme['DOM']	= "Domination";
		
		return $gameType[$gt];
	}
}
/**
 *
 */
function GameTypeShort($gt) {
	if (is_numeric($gt)) {
		$gameType[0] = "dm";
		$gameType[1] = "duel";
		$gameType[2] = "tdm";
		$gameType[3] = "ctf";
		$gameType[4] = "gib";
		$gameType[5] = "trx";
		$gameType[6] = "tren";
		$gameTyme[7] = "dom";
		
		return $gameType[$gt];
	} else {
		$gameType['DM'] = "dm";
		$gameType['DUEL'] = "duel";
		$gameType['TDM'] = "tdm";
		$gameType['CTF'] = "ctf";
		$gameType['RAIL'] = "gib";
		$gameType['TRIX'] = "trx";
		$gameType['PRAC'] = "tren";
		$gameTyme['DOM'] = "dom";
		
		return $gameType[$gt];
	}
}

function GameTypeShortU($gt) {
	if (is_numeric($gt)) {
		$gameType[0] = "DM";
		$gameType[1] = "DUEL";
		$gameType[2] = "TDM";
		$gameType[3] = "CTF";
		$gameType[4] = "RAIL";
		$gameType[5] = "TRIX";
		$gameType[6] = "PRAC";
		$gameTyme[7] = "DOM";
		
		return $gameType[$gt];
	} else {
		$gameType['dm'] = "DM";
		$gameType['duel'] = "DUEL";
		$gameType['tdm'] = "TDM";
		$gameType['ctf'] = "CTF";
		$gameType['rail'] = "RAIL";
		$gameType['trix'] = "TRIX";
		$gameType['prac'] = "PRAC";
		$gameTyme['dom'] = "DOM";
		
		return $gameType[$gt];
	}
}

function stripColor($nick) {
	$pure = "";
	for ($i = 0; $i<strlen($nick); $i++ ) {
		if ($nick[$i] == '^')
			$i++;
		else
			$pure .= $nick[$i];		
	}
	return html_entity_decode($pure);
}

/**
 * Strip name colors
 */
function stripNameColor($nick)
{
	return stripColor($nick);
}

/**
 * Parse name colors
 */
function parseNameColor($nick) {
	global $COLORS;
	$col_arr = array("1", "2", "3", "4", "5", "6", "7", "0", "!", "#", "%", "&");
	$colorized = "<font color='".$COLORS[7]."'>";
	for ($i = 0; $i<=mb_strlen(($nick)); $i++ ) {
		if (($nick[$i] != '^') and ($nick[$i-1] != '^')) $colorized .= $nick[$i];
		if (($nick[$i] == '^') and ($nick[$i+1] == '^')) $colorized .= $nick[$i];
		if (($nick[$i] == '^') and ($nick[$i+1] <> '^') and (in_array($nick[$i+1],$col_arr))) 
			$colorized .= "</font><font color='".$COLORS[$nick[$i+1]]."'>";
	
	//	if (($nick[$i] == '^') and (in_array($nick[$i+1],$col_arr)) ) {
	//		$colorized .= "</font><font color='".$COLORS[$nick[$i+1]]."'>";
	//	} else if ( ($nick[$i-1] == '^') and (in_array($nick[$i],$col_arr)) ) { /* skip color code */ }
	//	else if ( ($nick[$i] == '^') and (!in_array($nick[$i+1],$col_arr)) ) { /* skip color code */ }
	//	else $colorized .= $nick[$i]; 
	}
	return $colorized . '</font>';
}

/**
 *
 */
function ago_rus($tm,$rcs = 0) 
{
    $cur_tm = time(); $dif = $cur_tm-$tm;
    $pds = array('секунд','минут','час','дня','недел','месяц','год','десятилетие');
    $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);
    for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); 
	if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);
    $no = floor($no); 
	if ($v == 0) {
		if($no == 1) $pds[$v] .='а';
		if(($no >= 2) and ($no <= 4)) $pds[$v] .='ы';
		if ($no >= 21) {
			$no1=$no%10;
			if($no1 == 1) $pds[$v] .='а';
			if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='ы';
		}
	}
	if ($v == 1) {
		if($no == 1) $pds[$v] .='а';
		if(($no >= 2) and ($no <= 4)) $pds[$v] .='ы';
		if ($no >= 21) {
			$no1=$no%10;
			if($no1 == 1) $pds[$v] .='а';
			if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='ы';
		}
	}
	if ($v == 2) {
		if(($no >= 2) and ($no <= 4)) $pds[$v] .='а';
		if(($no >= 5) and ($no <= 20))  $pds[$v] .='ов';
		if ($no >= 21) {
			$no1=$no%10;
			if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='а';
			if($no1 >= 5) $pds[$v] .='ов';
		}
	}
	if ($v == 3) {
		if($no == 1) $pds[$v] ='день';
		if(($no >= 5) and ($no <= 20)) $pds[$v] ='дней';
		if ($no >= 21) {
			$no1=$no%10;
			if($no1 == 1) $pds[$v] ='день';
			if($no1 >= 5) $pds[$v] ='дней';
		}
	}
	if ($v == 4) {
		if($no == 1) $pds[$v] .='я';
		if(($no >= 2) and ($no <= 4)) $pds[$v] .='и';
		if(($no >= 5) and ($no <= 20)) $pds[$v] .='ь';
		if ($no >= 21) {
			$no1=$no%10;
			if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='и';
			if($no1 >= 5) $pds[$v] .='ь';
		}
	}
	if ($v == 5) {
		if(($no >= 2) and ($no <= 4)) $pds[$v] .='а';
		if(($no >= 5) and ($no <= 20)) $pds[$v] .='ев';
		if ($no >= 21) {
			$no1=$no%10;
			if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='а';
			if($no1 >= 5) $pds[$v] .='ев';
		}
	}
	if ($v == 6) {
		if(($no >= 2) and ($no <= 4)) $pds[$v] .='а';
		if(($no >= 5) and ($no <= 20)) $pds[$v] ='лет';
		if ($no >= 21) {
			$no1=$no%10;
			if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='а';
			if($no1 >= 5) $pds[$v] ='лет';
		}
	}
	
	$x=sprintf("%d %s ",$no,$pds[$v]);
    if(($rcs == 1)&&($v >= 1)&&(($cur_tm-$_tm) > 0)) $x .= time_ago($_tm);
    return $x;
}

function ago_($tm,$rcs = 0) 
{
    $cur_tm = time(); $dif = $cur_tm-$tm;
    $pds = array('second','minute','hour','day','week','month','year','decade');
    $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);
    for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);
    $no = floor($no); 
	if($no <> 1) $pds[$v] .='s'; 
	$x=sprintf("%d %s ",$no,$pds[$v]);
    if(($rcs == 1)&&($v >= 1)&&(($cur_tm-$_tm) > 0)) $x .= time_ago($_tm);
    return $x;
}

function timeAgo($tm, $lang) {
	$rusLang = ($lang == 'ru'); 
    $cur_tm = time(); $dif = $cur_tm-$tm;
    if ($rusLang) $pds = array('секунд','минут','час','дня','недел','месяц','год','десятилетие');
		else $pds = array('second','minute','hour','day','week','month','year','decade');
    $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);
    for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);
    $no = floor($no); 
	if ($rusLang) {
		if ($v == 0) {
			if($no == 1) $pds[$v] .='а';
			if(($no >= 2) and ($no <= 4)) $pds[$v] .='ы';
			if ($no >= 21) {
				$no1=$no%10;
				if($no1 == 1) $pds[$v] .='а';
				if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='ы';
			}
		}
		if ($v == 1) {
			if($no == 1) $pds[$v] .='а';
			if(($no >= 2) and ($no <= 4)) $pds[$v] .='ы';
			if ($no >= 21) {
				$no1=$no%10;
				if($no1 == 1) $pds[$v] .='а';
				if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='ы';
			}
		}
		if ($v == 2) {
			if(($no >= 2) and ($no <= 4)) $pds[$v] .='а';
			if(($no >= 5) and ($no <= 20))  $pds[$v] .='ов';
			if ($no >= 21) {
				$no1=$no%10;
				if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='а';
				if($no1 >= 5) $pds[$v] .='ов';
			}
		}
		if ($v == 3) {
			if($no == 1) $pds[$v] ='день';
			if(($no >= 5) and ($no <= 20)) $pds[$v] ='дней';
			if ($no >= 21) {
				$no1=$no%10;
				if($no1 == 1) $pds[$v] ='день';
				if($no1 >= 5) $pds[$v] ='дней';
			}
		}
		if ($v == 4) {
			if($no == 1) $pds[$v] .='я';
			if(($no >= 2) and ($no <= 4)) $pds[$v] .='и';
			if(($no >= 5) and ($no <= 20)) $pds[$v] .='ь';
			if ($no >= 21) {
				$no1=$no%10;
				if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='и';
				if($no1 >= 5) $pds[$v] .='ь';
			}
		}
		if ($v == 5) {
			if(($no >= 2) and ($no <= 4)) $pds[$v] .='а';
			if(($no >= 5) and ($no <= 20)) $pds[$v] .='ев';
			if ($no >= 21) {
				$no1=$no%10;
				if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='а';
				if($no1 >= 5) $pds[$v] .='ев';
			}
		}
		if ($v == 6) {
			if(($no >= 2) and ($no <= 4)) $pds[$v] .='а';
			if(($no >= 5) and ($no <= 20)) $pds[$v] ='лет';
			if ($no >= 21) {
				$no1=$no%10;
				if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='а';
				if($no1 >= 5) $pds[$v] ='лет';
			}
		}
	} else if($no <> 1) $pds[$v] .='s'; 
	$x=sprintf("%d %s ",$no,$pds[$v]);
    //if(($rcs == 1)&&($v >= 1)&&(($cur_tm-$_tm) > 0)) $x .= time_ago($_tm);
    return $x;
}

function _countTime($tm,$lang) 
{		
	$rus = ($lang == 'ru'); 
    $cur_tm = time(); 
	$dif = $cur_tm-$tm; 
	$ago = ($dif > 0);
	$dif = abs($dif);
    if ($rus) $pds = array('секунд','минут','час','дня','недел','месяц','год','десятилетие');
		else $pds = array('second','minute','hour','day','week','month','year','decade');
    $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);
    for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); 
	if($v < 0) $v = 0; //$_tm = abs($cur_tm-($dif%$lngh[$v]));
    $no = round($no); 
	if ($rus) {
		if ($v == 0) {
			if($no == 1) $pds[$v] .='у';
			if(($no >= 2) and ($no <= 4)) $pds[$v] .='ы';
			if ($no >= 21) {
				$no1=$no%10;
				if($no1 == 1) $pds[$v] .='у';
				if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='ы';
			}
		}
		if ($v == 1) {
			if($no == 1) $pds[$v] .='у';
			if(($no >= 2) and ($no <= 4)) $pds[$v] .='ы';
			if ($no >= 21) {
				$no1=$no%10;
				if($no1 == 1) $pds[$v] .='у';
				if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='ы';
			}
		}
		if ($v == 2) {
			if(($no >= 2) and ($no <= 4)) $pds[$v] .='а';
			if(($no >= 5) and ($no <= 20))  $pds[$v] .='ов';
			if ($no >= 21) {
				$no1=$no%10;
				if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='а';
				if($no1 >= 5) $pds[$v] .='ов';
			}
		}
		if ($v == 3) {
			if($no == 1) $pds[$v] ='день';
			if(($no >= 5) and ($no <= 20)) $pds[$v] ='дней';
			if ($no >= 21) {
				$no1=$no%10;
				if($no1 == 1) $pds[$v] ='день';
				if($no1 >= 5) $pds[$v] ='дней';
			}
		}
		if ($v == 4) {
			if($no == 1) $pds[$v] .='ю';
			if(($no >= 2) and ($no <= 4)) $pds[$v] .='и';
			if(($no >= 5) and ($no <= 20)) $pds[$v] .='ь';
			if ($no >= 21) {
				$no1=$no%10;
				if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='и';
				if($no1 >= 5) $pds[$v] .='ь';
			}
		}
		if ($v == 5) {
			if(($no >= 2) and ($no <= 4)) $pds[$v] .='а';
			if(($no >= 5) and ($no <= 20)) $pds[$v] .='ев';
			if ($no >= 21) {
				$no1=$no%10;
				if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='а';
				if($no1 >= 5) $pds[$v] .='ев';
			}
		}
		if ($v == 6) {
			if(($no >= 2) and ($no <= 4)) $pds[$v] .='а';
			if(($no >= 5) and ($no <= 20)) $pds[$v] ='лет';
			if ($no >= 21) {
				$no1=$no%10;
				if(($no1 >= 2) and ($no1 <= 4)) $pds[$v] .='а';
				if($no1 >= 5) $pds[$v] ='лет';
			}
		}
	} else if($no <> 1) $pds[$v] .='s'; 
	$x=sprintf("%d %s ",$no,$pds[$v]);
    //if(($rcs == 1)&&($v >= 1)&&(($cur_tm-$_tm) > 0)) $x .= time_ago($_tm);
	if ($rus) {
		if ($ago) $l_ago = ' назад';
			else $l_after = 'через ';
	} else {
		if ($ago) $l_ago = ' ago';
			else $l_after = 'after ';
	}
    return "$l_after$x$l_ago";
}
 
/**
 *
 */
function calcHourDays($mins)
{
	$days = floor($mins / (24*60));
		$mins = $mins - ($days * 24 * 60);
	
	if ($mins > 0)
	$hours = floor($mins / 60);
		$mins = $mins - ($hours * 60);
	
	if ($days > 0) $result = $days. 'days';
	if ($hours > 0) $result .= ' '.$hours.' hours';
	if ($mins > 0) $result .= ' '.$mins.' minutes';
	
	return $result;
} 

function sec2HourDays($secs)
{
	if ($secs == 0) return $result='0m';

	$mins = round($secs / 60);
	
	$days = floor($mins / (24*60));
		$mins = $mins - ($days * 24 * 60);
	
	if ($mins > 0)
	$hours = floor($mins / 60);
		$mins = $mins - ($hours * 60);
	
	if ($days > 0) $result = $days. 'd';
	if ($hours > 0) $result .= ' '.$hours.'h';
	if ($mins > 0) $result .= ' '.$mins.'m';
	
	return $result;
} 

/**
 *
 */
function weaponShortName($id)
{
	$weapon[0] ='gauntlet';
	$weapon[1] ='machinegun';
	$weapon[2] ='shotgun';
	$weapon[3] ='grenade';
	$weapon[4] ='rocket';
	$weapon[5] ='lightning';
	$weapon[6] ='plasma';
	$weapon[7] ='railgun';
	$weapon[8] ='bfg';
	
	return $weapon[$id];
}

/**
 *
 */
function weaponFullName($id)
{
	$weapon[0] ='Gauntlet';
	$weapon[1] ='Machinegun';
	$weapon[2] ='Shotgun';
	$weapon[3] ='Grenade Launcher';
	$weapon[4] ='Rocket Launcher';
	$weapon[5] ='Shaft';
	$weapon[6] ='Plasmagun';
	$weapon[7] ='Railgun';
	$weapon[8] ='BFG';
	
	return $weapon[$id];
}

function newSID()
{
	for ($i=0;$i<16;$i++)
	{
		$way = rand(1,3);
		if ($way == 1) { $result .= chr(rand(48,57)); }
		elseif ($way == 2) { $result .= chr(rand(65,90)); }
		else $result .= chr(rand(97,122));
	}
	return $result;
}

/*
 * Shows error message, with a link to proceed page
 */
function error($str,$redir)
{
    // dummy
    return "<script>alert('ERROR: $str');</script>";
}

/*
 * Shows info message, with a link to proceed page
 */
function message($str,$redir)
{
    // dummy
    return "<script>alert('$str');</script>";
}

function clearName($playerName) {
	return htmlspecialchars($playerName);
}

function getUserName($xdata) {

	global $db;
	
	$player = $db->select("name","playerStats","WHERE `userID`='$xdata[id]' LIMIT 1");
	$player = $player[0];
	if (count($player) == 1) return clearName($player['name']);
		else return $xdata['login'];
	return "NULL";
}

function getIcons($player, $profile = true, $flag = true, $ico = true, $colored = false) {
	if (isset($player['author']) && $player['author'] <> "") $player['name'] = $player['author'];
	
	if ($colored) {
		if (isset($player['nick']) && $player['nick'] <> "") $plr_name = parseNameColor(clearName($player['nick'])); else  $plr_name = clearName($player['name']);
	} else $plr_name = clearName($player['name']);
	
	if ($profile) {
		if ($_SERVER['SCRIPT_NAME'] <> '/index.php') $scr_name = 'index.php'; else $scr_name=''; 
		$name = "<a href='/profile/$player[playerID]'>$plr_name</a>";
	} else $name = $plr_name;
	
	if ($flag) {
		global $dict;
		$c_title = $dict->data["C_".strtoupper($player['country'])];
		$flag_ico = "<img src='/images/flags/$player[country].png' title='$c_title' align='absmiddle'> ";
	} else $flag_ico = "";
	
	if ($ico) {
		$i_title = $player['model'];
		$modelname = explode('_', $player['model']);
		$arr_model = array("crashed", "doom", "doom2", "keel", "sarge", "xaero", "klesk2", "ranger");
		if (!isset($modelname[0]) || !in_array($modelname[0],$arr_model)) $player['model'] = "sarge_default";
		if ($player['model']=="") $player['model'] = "sarge_default";
		$ico_img = "<img src='/images/players/icon_15/$player[model].png' title='$i_title' align='absmiddle'> ";
	} else $ico_img = "";
	return $flag_ico.$ico_img.$name;
}

function getPlayerName($playerID, $profile = true, $flag = true, $ico = true, $colored = false) {
	if (!is_numeric($playerID)) return null;
	
	global $db;
	
	$player = $db->select("nick, name, country, model, playerID","playerStats","WHERE `playerID`='$playerID' LIMIT 1");
	$player = $player[0];
	return html_entity_decode(getIcons($player,$profile,$flag,$ico,$colored));
	//return getIcons($player['name'],$playerID,$player['country'],$profile,$flag,$ico);
}

function getMatchResult($res) {
	if (is_numeric($res)) {
		if ($res == 1) return "win";
		if ($res == 0) return "loss";
		if ($res == -1) return "quit";
	} else {
		return "NULL";
	}
}

function is_teamGame($gt) {
	if (is_numeric($gt)) {
		$gameType[2] = "tdm";
		$gameType[3] = "ctf";
		$gameTyme[7] = "dom";
		if ($gameType[$gt] <> NULL) return false;
		return true;
	} else {
		$gameType['TDM'] = "tdm";
		$gameType['CTF'] = "ctf";
		$gameTyme['DOM'] = "dom";
		if ($gameType[$gt] <> NULL) return false;
		return true;
	}
}

function getSign($num) {
	if (is_numeric($num)) {
		if ($num > 0) return '+'.$num;
		else return $num;
	} else {
		return '0';
	}
}

function parseString( $str ) {
        $str = trim( $str );
		//$str = str_replace("<","&lt;",$str);
		//$str = str_replace('>',"&gt;",$str);
        $str = preg_replace("/[^\x20-\xFF]/","",@strval($str));
        $str = strip_tags( $str );
       // $str = htmlspecialchars( $str, ENT_QUOTES );
      //  $str = mysql_real_escape_string( $str );
		$str = str_replace("'","",$str);
		$str = str_replace('"',"",$str);
        return $str;
}

function GetPID($name) {	
	global $db;
	$name = $db->clean(iconv('CP1251','UTF-8',$name));
	$player = $db->select("playerID","playerStats","WHERE `name`='$name' LIMIT 1");
	if ( count($player) == 0)
		return false;
	$playerID = $player[0]['playerID'];
	if ((is_numeric($playerID)) and ($playerID > 0)) {
		return $playerID;
	} else return false;

}

function stripTags($name) {
	for ($i = 0; $i<=strlen($name); $i++ ) {
		if (($name[$i] != '^') and ($name[$i-1] != '^')) $pure .= $name[$i];
		if (($name[$i] == '^') and ($name[$i+1] == '^')) $pure .= $name[$i];
	}	
	return $pure;
}

function getPlaceIco($place){
	global $template;
	switch ($place) {
		case 1: return $template->build('ico_first'); break;
		case 2: return $template->build('ico_second'); break;
		case 3: return $template->build('ico_third'); break;
		case 4: return '<b>4</b>'; break;
	}
	if ($place>0) return $place; else return '-';
}

function getNewMapList() {
	$mapList = array(
		'pro-dm0','tourney4','dm2',
		'tourney7','tourney0','cpm3',
		'ra_dm1','tourney1b','pro-t2',
		'HUB3T1','cpm1','cpm15',
		'bq','Ospdm10','mdm1',
	);
	shuffle($mapList);
	$cupMaps = array();
	for($i = 0; $i<=6; $i++) {
		$cupMaps[] = $mapList[$i];
	}
	return $cupMaps;
}


function objSort($a, $b) {
    return $a->objtype < $b->objtype;
}

function sf() {
    $args = func_get_args();
    return call_user_func_array('sprintf', $args);
}

/*
function getNextDayDate($dateID) {
	$ret = false;  
	for ($i=1; $i<=7; $i++) {  
		if (date('w',strtotime('+'.$i.' day'))==$dateID) {  
			$ret = date('Y-m-d',strtotime('+'.$i.' day'));  
			break;  
		}  
	}
	return $ret;  
}*/