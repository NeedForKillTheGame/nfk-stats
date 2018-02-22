<?php
class Ladder {
    const teamRed = 'RED';
    const teamBlue = 'BLUE';
    const matchDraw = -1;
    private static $teamPlayTypes = array('TDM', 'CTF', 'DOM');
    private static $scoreChangeWinner = array(4 => 20, 3 => 17, 2 => 15, 1 => 12, 0 => 10, -1 => 7, -2 => 5, -3 => 2, -4 => 1);
    private static $scoreChangeLoser = array(
        'D' => array(4 => 0, 3 => 1, 2 => 2, 1 => 4, 0 => 5, -1 => 6, -2 => 7, -3 => 8, -4 => 9),
        'C' => array(4 => 0, 3 => 2, 2 => 3, 1 => 6, 0 => 7, -1 => 9, -2 => 11, -3 => 13, -4 => 15),
        'B' => array(4 => 0, 3 => 3, 2 => 5, 1 => 8, 0 => 10, -1 => 12, -2 => 15, -3 => 17, -4 => 20),
        'A' => array(4 => 0, 3 => 4, 2 => 7, 1 => 11, 0 => 13, -1 => 16, -2 => 20, -3 => 24, -4 => 28),
    );
    private static $rankScore = array(
        0 => 84, 1 => 199, 2 => 299, 3 => 399, 4 => 499, 5 => 599, 6 => 699, 7 => 799, 8 => 899, 9 => 1049, 10 => 1199, 11 => 1499
    );
    private static $rankGroups = array(
        0 => 'D', 1 => 'D', 2 => 'D', 3 => 'C', 4 => 'C', 5 => 'C', 6 => 'B', 7 => 'B', 8 => 'B', 9 => 'A', 10 => 'A', 11 => 'A', 12 => 'A'
    );

    static function getRankID($score) {
        foreach (self::$rankScore as  $rankId => $rankScore) {
            if ($rankScore <= $score) return $rankId;
        }
        return 12;
    }

    static function getRankGroup($rankID) {
        return self::$rankGroups[$rankID];
    }

    static function isTeamPlay($gameType) {
        return isset(self::$teamPlayTypes[$gameType]);
    }

    static function isDuel($gameType) {
        return $gameType == 'DUEL';
    }

    static function matchProcess($match, &$players) {
        function cmp($a, $b) {
            if ($a['frags'] == $b['frags']) {
                if ($a['deaths'] == $b['deaths']) {
                    return 0;
                } else {
                    return ($a['deaths'] < $b['deaths']) ? -1 : 1;
                }
            }
            return ($a['frags'] > $b['frags']) ? -1 : 1;
        }
        $isTeamPlay = self::isTeamPlay($match['gametype']);
        $isDuel = self::isDuel($match['gametype']);
        $winTeamName = null;
        if ($isTeamPlay) {
            if ($match['redscore'] != $match['bluescore']) {
                $winTeamName = ($match['redscore'] > $match['bluescore']) ? self::teamRed : self::teamBlue;
            }
            foreach ($players as $player) {
                $player['win'] = 0;
                $player['lose'] = 0;
                $player['modScore'] = 0;
                if ($winTeamName == $player['team']) {
                    $player['win'] = 1;
                } elseif ($winTeamName !== null) {
                    $player['lose'] = 1;
                }
            }
        } elseif ($isDuel) {
            usort($players, "cmp");
            $players[0]['win'] = 1;
            $players[0]['lose'] = 0;
            // todo
            $players[0]['modScore'] = 0;
            $players[1]['win'] = 0;
            $players[1]['lose'] = 1;
            // todo
            $players[1]['modScore'] = 0;
        }
    }
} 