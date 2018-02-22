<?php

/*
 * This file is part of the NFK Planet Scaner package.
 *
 * (c) PQR <pqr@outlook.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

//namespace NFK\PlanetScaner;

class PlanetScaner
{
    private $planetAddress;
    private $planetPort;

    /**
     * @param string $planetAddress
     * @param int $planetPort
     */
    public function __construct($planetAddress, $planetPort)
    {
        $this->planetAddress = $planetAddress;
        $this->planetPort = $planetPort;
    }

    /**
     * @return array return games info array
     * @throws \Exception
     */
    public function getServers()
    {
        // check port availability with timeout (timeout doesn't work on windows using socket_set_option)
        if(!@fsockopen($this->planetAddress, $this->planetPort, $errCode, $errStr, 2)) {
            throw new \Exception('Host is not responding (' . $errCode . '): ' . $errStr, $errCode);
        }


        $socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            $errCode = socket_last_error();
            throw new \Exception('Socket Create Error (' . $errCode . '): ' . socket_strerror($errCode), $errCode);
        }

        // connect to NFK Planet
        if (!@socket_connect($socket, $this->planetAddress, $this->planetPort)) {
            $errCode = socket_last_error();
            throw new \Exception('Socket Connect Error (' . $errCode . '): ' . socket_strerror($errCode), $errCode);
        }

        // asking for version
        $buf = "?V077\x0d\x0a";
        $writtenBytes = $this->socketWrite($socket, $buf);
        if ($writtenBytes === false) {
            $errCode = socket_last_error();
            throw new \Exception('Ask version error (' . $errCode . '): ' . socket_strerror($errCode), $errCode);
        }

        // reading version number
        $version = $this->socketRead($socket, 6);
        if ($version === false) {
            $errCode = socket_last_error();
            throw new \Exception('Read version error (' . $errCode . '): ' . socket_strerror($errCode), $errCode);
        }

        if ($version != "V077\x0A\x00") {
            throw new \Exception('Bad version: ' . $version);
        }

        // requesting games list
        $buf = "?G\x0d\x0a";
        $writtenBytes = $this->socketWrite($socket, $buf);
        if ($writtenBytes === false) {
            $errCode = socket_last_error();
            throw new \Exception('Request games list error (' . $errCode . '): ' . socket_strerror($errCode), $errCode);
        }

        $result = array();

        // reading games info. Each game info packet should start with "L" char
        $firstPacketChar = $this->socketRead($socket, 1);
        while ($firstPacketChar == "L") {

            //TODO: check why only 128 bytes max? What if game info contains more than 128 bytes? e.g. many players with long names
            $packet = $this->socketReadTok($socket, 128, "\x0a\x00");
            if ($packet === false) {
                $errCode = socket_last_error();
                throw new \Exception('Socket error while read games list error (' . $errCode . '): ' . socket_strerror($errCode), $errCode);
            }

            $servArray = explode("\x0d", $packet);
            $result[] = array(
                "IP" => $servArray[0],
                "Hostname" => $servArray[1],
                "Map" => $servArray[2],
                "Gametype" => $this->getGameType($servArray[3]),
                "Players" => $servArray[4],
                "Maxplayers" => $servArray[5],
                "Port" => $servArray[6]
            );

            $firstPacketChar = $this->socketRead($socket, 1);
        }

        return $result;
    }

    /**
     * writes *exactly* strlen($buf) bytes in socket
     * @param resource $socket
     * @param string $buf
     * @return bool|int return false on write error
     */
    private function socketWrite($socket, $buf)
    {
        $size = strlen($buf);
        $len = 0;
        do {
            $res = @socket_write($socket, $buf, $size - $len);
            if ($res === false || $res === 0) {
                return false;
            }
            $buf = substr($buf, $res);
            $len += $res;
        } while ($len < $size);

        return $size;
    }

    /**
     * reads *exactly* $size bytes from socket
     * @param resource $socket
     * @param int $size
     * @return bool|string return false on read error
     */
    private function socketRead($socket, $size)
    {
        $buf = '';
        $len = 0;
        do {
            $res = @socket_read($socket, $size - $len, PHP_BINARY_READ);
            if ($res === false || $res === 0) {
                return false;
            }

            $buf .= $res;
            $len = strlen($buf);
        } while ($len < $size);

        return $buf;
    }

    /**
     * reads no more than $size of data from socket, ending with a given $token
     * @param resource $socket
     * @param int $size
     * @param string $token
     * @param bool $includeTok
     * @return bool|string return false on read error
     */
    private function socketReadTok($socket, $size, $token, $includeTok = true)
    {
        $len = 0;
        $buf = "";
        $found = false;
        $tokSize = strlen($token);

        do {
            $temp = "";
            //peeking $size-$len bytes from socket
            $res = @socket_recv($socket, $temp, $size - $len, MSG_PEEK);
            if ($res === false || $res === 0) return false;

            $buf .= $temp;

            //looking if we have $token in the buffer
            $pos = strpos($buf, $token);
            if ($pos === false) {
                //if not found, reading (not peeking) $res bytes from socket
                $res2 = @socket_recv($socket, $temp, $res, 0);
                if ($res2 === false || $res2 != $res) return false;
                $len += $res;
                continue;
            } else {
                //if found, mark the flag and read from socket everything before token
                $found = true;

                //if $includeTok is set, increasing $pos by $tokSize bytes to read it from socket too
                if ($includeTok) {
                    $pos += $tokSize;
                }

                $res = @socket_recv($socket, $temp, $pos - $len, 0);
                if ($res === false || $res != ($pos - $len)) return false;

                $buf = substr($buf, 0, $pos);
            }
        } while (!$found && $len < $size);

        return $buf;
    }

    /**
     * returns gametype string from its code
     * @param string $code
     * @return string
     */
    private function getGameType($code)
    {
        switch ($code) {
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
}