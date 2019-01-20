<?php
	define("NFKPL_ADDRESS", NFKPLANET_HOST);
	define("NFKPL_PORT", 10003);
	
	//ob_implicit_flush();
	//writes *exactly* $size bytes in socket
	function ex_socket_write($socket, $buf, $size)
	{
		$len = 0;
		do
		{			
			$res = socket_write($socket, $buf, $size - $len);

			if ($res === false || $res === 0)
			{
				return false;
			}
			$buf = substr($buf, $res);
			$len += $res;
		} while ($len < $size);
		
		return true;
	}
	
	//reads *exactly* $size bytes from socket
	function ex_socket_read($sock, $size)
	{
		$buf = "";
		$len = 0;
		
		do
		{
			$res = socket_read($sock, $size - $len, PHP_BINARY_READ);
			if ($res === false || $res === 0)
				return false;
				
			$buf .= $res;
			$len = strlen($buf);
		} while ($len < $size);
		return $buf;
	}
	
	//reads no more than $size of data from socket, ending with a given $token
	function ex_socket_readTok($sock, $size, $token, $includeTok = true)
	{
		$len = 0;
		$buf = "";
		$found = false;
		$tokSize = strlen($token);
		
		do
		{
			$temp = "";
			//peeking $size-$len bytes from socket
			$res = socket_recv($sock, $temp, $size - $len, MSG_PEEK);
			if ($res === false || $res === 0)
				return false;

			$buf .= $temp;
			
			//looking if we have $token in the buffer
			$pos = strpos($buf, $token);
			if ($pos === false)
			{
				//if not found, reading (not peeking) $res bytes from socket
				$res2 = socket_recv($sock, $temp, $res, 0);
				if ($res2 === false || $res2 != $res)
					return false;
				$len += $res;
				continue;
			}
			else
			{
				//if found, mark the flag and read from socket everything before token
				$found = true;
				
				//if $includeTok is set, increasing $pos by $tokSize bytes to read it from socket too
				if ($includeTok)
				{
					$pos += $tokSize;
				}
				
				$res = socket_recv($sock, $temp, $pos - $len, 0);
				if ($res === false || $res != ($pos - $len))
					return false;
					
				$buf = substr($buf, 0, $pos);
			}
		} while (!$found && $len < $size);
		
		return $buf;
	}
	
	//reads NFK Planet packet from socket
	function nfkpl_readPacket($sock)
	{
		//receive first byte, the type of a packet;
		$type = ex_socket_read($sock, 1);
		
		switch ($type)
		{
			//version info, 5 bytes
			case "V":
				$buf = ex_socket_read($sock, 5);
				if ($buf === false)
					return false;
				break;
				
			//game server info
			case "L":
				//print("received serv info
				$buf = ex_socket_readTok($sock, 128, "\x0a\x00");
				if ($buf === false)
					return false;
				break;
				
			//end of servers list
			case "E":
			//ping
			case "K":
				$buf = ex_socket_read($sock, 2);
				if ($buf === false)
					return false;
				break;
			
			default:
				//print("unknown packet type, ".$type);
		}
		
		return $type.$buf;
	}
	
	//returns gametype string from its code
	function nfkpl_getGameType($code)
	{
		switch($code)
		{
			case "0":
				return "DM";
				break;
			case "1":
				return "PRAC";
				break;
			case "2":
				return "TDM";
				break;
			case "3":
				return "CTF";
				break;
			case "4":
				return "RAIL";
				break;
			case "5":
				return "DOM";
				break;
				
			case "6":
				return "PRAC";
				break;
				
			case "7":
				return "DOM";
				break;
				
			default:
				return "Unknown";
		}
	}
	
	//returns array with servers currently on NFK Planet
	function nfkpl_getServers()
	{
// check port availability with timeout (timeout doesn't work on windows using socket_set_option)
if(!@fsockopen(NFKPL_ADDRESS, NFKPL_PORT, $errCode, $errStr, 2)) {
	throw new \Exception('Host ' . NFKPL_ADDRESS . ' is not responding (' . $errCode . '): ' . $errStr, $errCode);
}
		$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		
		if ($sock === false)
		{
			return "Sorry mate, cant create socket";
		}
		
		if (!socket_connect($sock, NFKPL_ADDRESS, NFKPL_PORT))
		{
			return "Cant connect to planet";
		}
		
		socket_set_option($sock, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 2, "usec" => 0));
		
		//asking for version
		$buf = "?V077\x0d\x0a";
		if (ex_socket_write($sock, $buf, 7) === false)
			return "oh shi (V)";
			
		$version = nfkpl_readPacket($sock);
		if ($version === false)
			return "read failed (V)";
//		echo 'got version'.$version.'<br>';
		
		if ($version != "V077\x0A\x00")
			return "bad version";
	
		//requesting games list
		$buf = "?G\x0d\x0a";
		if (ex_socket_write($sock, $buf, 4) === false)
			return "oh shi (G)";
			
		$result = array();
		
		$packet = nfkpl_readPacket($sock);
		while ($packet[0] == "L")
		{	
			$packet = substr($packet, 1);
			$servArray = explode("\x0d", $packet);
			$result[] = array("IP" => $servArray[0],
				"Hostname" => $servArray[1],
				"Map" => $servArray[2],
				"Gametype" => nfkpl_getGameType($servArray[3]),
				"Players" => $servArray[4],
				"Maxplayers" => $servArray[5],
				"Port" => $servArray[6]);
			$packet = nfkpl_readPacket($sock);
		}
		
		return $result;
	}
?>
