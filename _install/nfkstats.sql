/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 5.1.36-community-log : Database - nfkstats
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `altstat_gameres` */

DROP TABLE IF EXISTS `altstat_gameres`;

CREATE TABLE `altstat_gameres` (
  `MatchId` int(11) NOT NULL,
  `PlayerId` int(11) NOT NULL,
  `Place` int(11) NOT NULL,
  `Reiting` int(11) NOT NULL,
  `Result` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `altstat_numgame` */

DROP TABLE IF EXISTS `altstat_numgame`;

CREATE TABLE `altstat_numgame` (
  `DUEL` int(11) NOT NULL,
  `DOM` int(11) NOT NULL,
  `DM` int(11) NOT NULL,
  `RAIL` int(11) NOT NULL,
  `PRAC` int(11) NOT NULL,
  `CTF` int(11) NOT NULL,
  `TDM` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `altstat_options` */

DROP TABLE IF EXISTS `altstat_options`;

CREATE TABLE `altstat_options` (
  `LastNum` int(11) NOT NULL,
  `LastDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `altstat_players` */

DROP TABLE IF EXISTS `altstat_players`;

CREATE TABLE `altstat_players` (
  `PlayerId` int(11) NOT NULL,
  `CtfReiting` int(11) NOT NULL,
  `TdmReiting` int(11) NOT NULL,
  `DmReiting` int(11) NOT NULL,
  `DuelReiting` int(11) NOT NULL,
  `DomReiting` int(11) NOT NULL,
  `RailReiting` int(11) NOT NULL,
  `PracReiting` int(11) NOT NULL,
  `CtfGame` int(11) NOT NULL,
  `TdmGame` int(11) NOT NULL,
  `DmGame` int(11) NOT NULL,
  `DuelGame` int(11) NOT NULL,
  `DomGame` int(11) NOT NULL,
  `RailGame` int(11) NOT NULL,
  `PracGame` int(11) NOT NULL,
  `CtfWin` int(11) NOT NULL,
  `TdmWin` int(11) NOT NULL,
  `DmWin` int(11) NOT NULL,
  `DuelWin` int(11) NOT NULL,
  `DomWin` int(11) NOT NULL,
  `RailWin` int(11) NOT NULL,
  `PracWin` int(11) NOT NULL,
  `AllRating` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`PlayerId`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `altstat_players_bk` */

DROP TABLE IF EXISTS `altstat_players_bk`;

CREATE TABLE `altstat_players_bk` (
  `PlayerId` int(11) NOT NULL,
  `CtfReiting` int(11) NOT NULL,
  `TdmReiting` int(11) NOT NULL,
  `DmReiting` int(11) NOT NULL,
  `DuelReiting` int(11) NOT NULL,
  `DomReiting` int(11) NOT NULL,
  `RailReiting` int(11) NOT NULL,
  `PracReiting` int(11) NOT NULL,
  `CtfGame` int(11) NOT NULL,
  `TdmGame` int(11) NOT NULL,
  `DmGame` int(11) NOT NULL,
  `DuelGame` int(11) NOT NULL,
  `DomGame` int(11) NOT NULL,
  `RailGame` int(11) NOT NULL,
  `PracGame` int(11) NOT NULL,
  `CtfWin` int(11) NOT NULL,
  `TdmWin` int(11) NOT NULL,
  `DmWin` int(11) NOT NULL,
  `DuelWin` int(11) NOT NULL,
  `DomWin` int(11) NOT NULL,
  `RailWin` int(11) NOT NULL,
  `PracWin` int(11) NOT NULL,
  `AllRating` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

/*Table structure for table `nfklive_bans` */

DROP TABLE IF EXISTS `nfklive_bans`;

CREATE TABLE `nfklive_bans` (
  `bID` int(5) NOT NULL AUTO_INCREMENT,
  `banIP` varchar(15) NOT NULL,
  `banMaskStart` varchar(16) DEFAULT NULL,
  `banMaskEnd` varchar(16) DEFAULT NULL,
  `banStart` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `banEnd` datetime NOT NULL,
  `banReas` varchar(255) NOT NULL,
  `banLevel` int(1) NOT NULL DEFAULT '1' COMMENT '1 - full ban, 2 - add ban',
  PRIMARY KEY (`bID`),
  KEY `ix_banIP` (`banIP`)
) ENGINE=InnoDB AUTO_INCREMENT=656 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_clanlist` */

DROP TABLE IF EXISTS `nfklive_clanlist`;

CREATE TABLE `nfklive_clanlist` (
  `clanID` int(8) NOT NULL AUTO_INCREMENT,
  `clanName` varchar(50) NOT NULL,
  `clanTag` varchar(10) NOT NULL,
  `leaderID` int(8) NOT NULL,
  `score` int(8) NOT NULL DEFAULT '0',
  `players` int(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`clanID`),
  KEY `fk_leaderID` (`leaderID`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_comments` */

DROP TABLE IF EXISTS `nfklive_comments`;

CREATE TABLE `nfklive_comments` (
  `cmtID` int(8) NOT NULL AUTO_INCREMENT,
  `moduleID` int(2) NOT NULL DEFAULT '2',
  `materialID` int(8) NOT NULL,
  `author` varchar(50) NOT NULL,
  `country` varchar(8) NOT NULL DEFAULT 'ru',
  `playerID` int(8) NOT NULL,
  `comment` longtext NOT NULL,
  `orig_cmt` longtext,
  `postTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `editTime` timestamp NULL DEFAULT NULL,
  `userIP` varchar(15) NOT NULL,
  PRIMARY KEY (`cmtID`),
  KEY `fk_matchID` (`materialID`),
  KEY `fk_playerID` (`playerID`)
) ENGINE=MyISAM AUTO_INCREMENT=5437 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_geoipdb` */

DROP TABLE IF EXISTS `nfklive_geoipdb`;

CREATE TABLE `nfklive_geoipdb` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip_from` varchar(255) NOT NULL,
  `ip_to` varchar(255) NOT NULL,
  `cc2` varchar(255) NOT NULL,
  `cc3` varchar(255) NOT NULL,
  `ccFull` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ip_from` (`ip_from`),
  KEY `ip_to` (`ip_to`)
) ENGINE=MyISAM AUTO_INCREMENT=111800 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_ladderctf` */

DROP TABLE IF EXISTS `nfklive_ladderctf`;

CREATE TABLE `nfklive_ladderctf` (
  `tableID` int(8) NOT NULL AUTO_INCREMENT,
  `playerID` int(8) NOT NULL,
  `frags` int(6) NOT NULL DEFAULT '0',
  `deaths` int(6) NOT NULL DEFAULT '0',
  `games` int(6) NOT NULL DEFAULT '0',
  `wins` int(6) NOT NULL DEFAULT '0',
  `losses` int(6) NOT NULL DEFAULT '0',
  `lastGame` timestamp NULL DEFAULT NULL,
  `time` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tableID`),
  KEY `fk_playerID` (`playerID`)
) ENGINE=MyISAM AUTO_INCREMENT=4448 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_ladderdm` */

DROP TABLE IF EXISTS `nfklive_ladderdm`;

CREATE TABLE `nfklive_ladderdm` (
  `tableID` int(8) NOT NULL AUTO_INCREMENT,
  `playerID` int(8) NOT NULL,
  `frags` int(6) NOT NULL DEFAULT '0',
  `deaths` int(6) NOT NULL DEFAULT '0',
  `games` int(6) NOT NULL DEFAULT '0',
  `wins` int(6) NOT NULL DEFAULT '0',
  `losses` int(6) NOT NULL DEFAULT '0',
  `lastGame` timestamp NULL DEFAULT NULL,
  `time` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tableID`),
  KEY `fk_playerID` (`playerID`)
) ENGINE=MyISAM AUTO_INCREMENT=4453 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_ladderdom` */

DROP TABLE IF EXISTS `nfklive_ladderdom`;

CREATE TABLE `nfklive_ladderdom` (
  `tableID` int(8) NOT NULL AUTO_INCREMENT,
  `playerID` int(8) NOT NULL,
  `frags` int(6) NOT NULL DEFAULT '0',
  `deaths` int(6) NOT NULL DEFAULT '0',
  `games` int(6) NOT NULL DEFAULT '0',
  `wins` int(6) NOT NULL DEFAULT '0',
  `losses` int(6) NOT NULL DEFAULT '0',
  `lastGame` timestamp NULL DEFAULT NULL,
  `time` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tableID`),
  KEY `fk_playerID` (`playerID`)
) ENGINE=MyISAM AUTO_INCREMENT=4448 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_ladderduel` */

DROP TABLE IF EXISTS `nfklive_ladderduel`;

CREATE TABLE `nfklive_ladderduel` (
  `tableID` int(8) NOT NULL AUTO_INCREMENT,
  `playerID` int(8) NOT NULL,
  `frags` int(6) NOT NULL DEFAULT '0',
  `deaths` int(6) NOT NULL DEFAULT '0',
  `games` int(6) NOT NULL DEFAULT '0',
  `wins` int(6) NOT NULL DEFAULT '0',
  `losses` int(6) NOT NULL DEFAULT '0',
  `lastGame` timestamp NULL DEFAULT NULL,
  `score` int(6) NOT NULL DEFAULT '100',
  `rank` int(3) NOT NULL DEFAULT '1',
  `time` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tableID`),
  KEY `fk_playerID` (`playerID`)
) ENGINE=MyISAM AUTO_INCREMENT=4448 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_laddertdm` */

DROP TABLE IF EXISTS `nfklive_laddertdm`;

CREATE TABLE `nfklive_laddertdm` (
  `tableID` int(8) NOT NULL AUTO_INCREMENT,
  `playerID` int(8) NOT NULL,
  `frags` int(6) NOT NULL DEFAULT '0',
  `deaths` int(6) NOT NULL DEFAULT '0',
  `games` int(6) NOT NULL DEFAULT '0',
  `wins` int(6) NOT NULL DEFAULT '0',
  `losses` int(6) NOT NULL DEFAULT '0',
  `lastGame` timestamp NULL DEFAULT NULL,
  `time` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tableID`),
  KEY `fk_playerID` (`playerID`)
) ENGINE=MyISAM AUTO_INCREMENT=4448 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_maplist` */

DROP TABLE IF EXISTS `nfklive_maplist`;

CREATE TABLE `nfklive_maplist` (
  `mapID` int(6) NOT NULL AUTO_INCREMENT,
  `mapName` varchar(32) NOT NULL,
  `gamesNum` int(6) NOT NULL DEFAULT '1',
  `ladderMap` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mapID`),
  UNIQUE KEY `mapName` (`mapName`),
  FULLTEXT KEY `ft_mapName` (`mapName`)
) ENGINE=MyISAM AUTO_INCREMENT=61 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_maps` */

DROP TABLE IF EXISTS `nfklive_maps`;

CREATE TABLE `nfklive_maps` (
  `map_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hash` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '0',
  `data` varchar(255) NOT NULL,
  PRIMARY KEY (`map_id`),
  UNIQUE KEY `hash` (`hash`)
) ENGINE=InnoDB AUTO_INCREMENT=1761 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_matchdata` */

DROP TABLE IF EXISTS `nfklive_matchdata`;

CREATE TABLE `nfklive_matchdata` (
  `dataID` int(9) NOT NULL AUTO_INCREMENT,
  `matchID` int(5) NOT NULL,
  `playerID` int(5) NOT NULL,
  `frags` int(5) NOT NULL,
  `kills` int(5) NOT NULL DEFAULT '0',
  `deaths` int(5) NOT NULL,
  `team` varchar(5) DEFAULT NULL,
  `win` int(2) DEFAULT NULL,
  `score` int(5) DEFAULT NULL,
  `ping` int(3) DEFAULT NULL,
  `time` int(9) DEFAULT NULL,
  `IP` varchar(15) DEFAULT NULL,
  `dmggiven` int(5) NOT NULL DEFAULT '0',
  `dmgrecvd` int(4) NOT NULL DEFAULT '0',
  `suisides` int(4) NOT NULL DEFAULT '0',
  `impressives` int(3) NOT NULL DEFAULT '0',
  `excellents` int(3) NOT NULL DEFAULT '0',
  `humiliations` int(3) NOT NULL DEFAULT '0',
  `gaun_hits` int(5) NOT NULL DEFAULT '0',
  `mach_hits` int(5) NOT NULL DEFAULT '0',
  `shot_hits` int(5) NOT NULL DEFAULT '0',
  `gren_hits` int(5) NOT NULL DEFAULT '0',
  `rocket_hits` int(5) NOT NULL DEFAULT '0',
  `shaft_hits` int(5) NOT NULL DEFAULT '0',
  `plasma_hits` int(5) NOT NULL DEFAULT '0',
  `rail_hits` int(5) NOT NULL DEFAULT '0',
  `bfg_hits` int(5) NOT NULL DEFAULT '0',
  `mach_fire` int(5) NOT NULL DEFAULT '0',
  `shot_fire` int(5) NOT NULL DEFAULT '0',
  `gren_fire` int(5) NOT NULL DEFAULT '0',
  `rocket_fire` int(5) NOT NULL DEFAULT '0',
  `shaft_fire` int(5) NOT NULL DEFAULT '0',
  `plasma_fire` int(5) NOT NULL DEFAULT '0',
  `rail_fire` int(5) NOT NULL DEFAULT '0',
  `bfg_fire` int(5) NOT NULL DEFAULT '0',
  `gaun_kills` int(5) NOT NULL DEFAULT '0',
  `mach_kills` int(5) NOT NULL DEFAULT '0',
  `shot_kills` int(5) NOT NULL DEFAULT '0',
  `gren_kills` int(5) NOT NULL DEFAULT '0',
  `rocket_kills` int(5) NOT NULL DEFAULT '0',
  `shaft_kills` int(5) NOT NULL DEFAULT '0',
  `plasma_kills` int(5) NOT NULL DEFAULT '0',
  `rail_kills` int(5) NOT NULL DEFAULT '0',
  `bfg_kills` int(5) NOT NULL DEFAULT '0',
  `redArmors` int(3) DEFAULT '0',
  `yellowArmors` int(3) DEFAULT '0',
  `megaHealthes` int(3) DEFAULT '0',
  `powerUps` int(3) DEFAULT '0',
  PRIMARY KEY (`dataID`),
  KEY `fk_matchID` (`matchID`),
  KEY `fk_playerID` (`playerID`)
) ENGINE=MyISAM AUTO_INCREMENT=154140 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_matchlist` */

DROP TABLE IF EXISTS `nfklive_matchlist`;

CREATE TABLE `nfklive_matchlist` (
  `matchID` int(9) NOT NULL AUTO_INCREMENT,
  `hostName` varchar(50) NOT NULL,
  `map` varchar(30) NOT NULL,
  `gameType` varchar(10) NOT NULL,
  `gameTypeID` int(1) unsigned DEFAULT NULL,
  `timeLimit` int(3) NOT NULL,
  `players` varchar(6) NOT NULL,
  `maxPlayers` int(3) unsigned DEFAULT NULL,
  `numPlayers` int(3) unsigned DEFAULT NULL,
  `redScore` int(5) DEFAULT NULL,
  `blueScore` int(5) DEFAULT NULL,
  `dateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `gameTime` int(8) DEFAULT NULL,
  `demo` varchar(255) DEFAULT NULL,
  `dlnum` int(6) DEFAULT '0',
  `comments` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`matchID`)
) ENGINE=MyISAM AUTO_INCREMENT=50846 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_medals` */

DROP TABLE IF EXISTS `nfklive_medals`;

CREATE TABLE `nfklive_medals` (
  `medalID` int(3) NOT NULL AUTO_INCREMENT,
  `medalName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `medalDescription` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`medalID`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `nfklive_mod_news_types` */

DROP TABLE IF EXISTS `nfklive_mod_news_types`;

CREATE TABLE `nfklive_mod_news_types` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `alias` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'undefined',
  `icon` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'undefined',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `nfklive_news` */

DROP TABLE IF EXISTS `nfklive_news`;

CREATE TABLE `nfklive_news` (
  `news_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `content` text,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comments` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`news_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_nodes` */

DROP TABLE IF EXISTS `nfklive_nodes`;

CREATE TABLE `nfklive_nodes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `alias` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(5) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'rus',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `poster_id` int(5) NOT NULL,
  `posted` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `nfklive_onservers` */

DROP TABLE IF EXISTS `nfklive_onservers`;

CREATE TABLE `nfklive_onservers` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `serverName` varchar(50) NOT NULL,
  `address` varchar(25) DEFAULT NULL,
  `playerName` varchar(50) NOT NULL,
  `dxid` int(6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dxid` (`dxid`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_playerrewards` */

DROP TABLE IF EXISTS `nfklive_playerrewards`;

CREATE TABLE `nfklive_playerrewards` (
  `statRewardID` int(10) NOT NULL AUTO_INCREMENT,
  `playerID` int(5) NOT NULL,
  `medalID` int(3) NOT NULL,
  `rewardTime` datetime NOT NULL,
  UNIQUE KEY `statRewardID` (`statRewardID`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `nfklive_playerstats` */

DROP TABLE IF EXISTS `nfklive_playerstats`;

CREATE TABLE `nfklive_playerstats` (
  `playerID` int(8) NOT NULL AUTO_INCREMENT,
  `userID` int(8) NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `nick` varchar(50) DEFAULT NULL,
  `model` varchar(50) NOT NULL DEFAULT 'sarge_default',
  `country` varchar(8) NOT NULL DEFAULT 'ru',
  `clanID` int(5) NOT NULL DEFAULT '0',
  `ClanScore` int(8) NOT NULL DEFAULT '0',
  `ClanGames` int(8) NOT NULL DEFAULT '0',
  `lastIP` varchar(15) DEFAULT NULL,
  `regIP` varchar(15) DEFAULT NULL,
  `lastGame` timestamp NULL DEFAULT NULL,
  `regDate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `games` int(8) NOT NULL DEFAULT '0',
  `time` int(9) NOT NULL DEFAULT '0',
  `frags` int(8) NOT NULL DEFAULT '0',
  `kills` int(8) NOT NULL DEFAULT '0',
  `deaths` int(8) NOT NULL DEFAULT '0',
  `wins` int(8) NOT NULL DEFAULT '0',
  `losses` int(8) NOT NULL DEFAULT '0',
  `quits` int(8) NOT NULL DEFAULT '0',
  `hits` int(9) NOT NULL DEFAULT '0',
  `shots` int(9) NOT NULL DEFAULT '0',
  `favWeapon` int(2) NOT NULL DEFAULT '0',
  `favGameType` int(2) NOT NULL DEFAULT '0',
  `gaun_hits` int(8) NOT NULL DEFAULT '0',
  `gaun_kills` int(8) NOT NULL DEFAULT '0',
  `mach_hits` int(8) NOT NULL DEFAULT '0',
  `mach_fire` int(8) NOT NULL DEFAULT '0',
  `mach_kills` int(8) NOT NULL DEFAULT '0',
  `shot_hits` int(8) NOT NULL DEFAULT '0',
  `shot_fire` int(8) NOT NULL DEFAULT '0',
  `shot_kills` int(8) NOT NULL DEFAULT '0',
  `gren_hits` int(8) NOT NULL DEFAULT '0',
  `gren_fire` int(8) NOT NULL DEFAULT '0',
  `gren_kills` int(8) NOT NULL DEFAULT '0',
  `rocket_hits` int(8) NOT NULL DEFAULT '0',
  `rocket_fire` int(8) NOT NULL DEFAULT '0',
  `rocket_kills` int(8) NOT NULL DEFAULT '0',
  `shaft_hits` int(8) NOT NULL DEFAULT '0',
  `shaft_fire` int(8) NOT NULL DEFAULT '0',
  `shaft_kills` int(8) NOT NULL DEFAULT '0',
  `plasma_hits` int(8) NOT NULL DEFAULT '0',
  `plasma_fire` int(8) NOT NULL DEFAULT '0',
  `plasma_kills` int(8) NOT NULL DEFAULT '0',
  `rail_hits` int(8) NOT NULL DEFAULT '0',
  `rail_fire` int(8) NOT NULL DEFAULT '0',
  `rail_kills` int(8) NOT NULL DEFAULT '0',
  `bfg_hits` int(8) NOT NULL DEFAULT '0',
  `bfg_fire` int(8) NOT NULL DEFAULT '0',
  `bfg_kills` int(8) NOT NULL DEFAULT '0',
  `humiliations` int(8) NOT NULL DEFAULT '0',
  `impressives` int(8) NOT NULL DEFAULT '0',
  `excellents` int(8) NOT NULL DEFAULT '0',
  `DM` int(6) NOT NULL DEFAULT '0',
  `DUEL` int(6) NOT NULL DEFAULT '0',
  `TDM` int(6) NOT NULL DEFAULT '0',
  `CTF` int(6) NOT NULL DEFAULT '0',
  `DOM` int(6) NOT NULL DEFAULT '0',
  `changeDate` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`playerID`),
  UNIQUE KEY `name` (`name`),
  KEY `fk_userID` (`userID`),
  KEY `fk_clanID` (`clanID`)
) ENGINE=MyISAM AUTO_INCREMENT=4456 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_seasons` */

DROP TABLE IF EXISTS `nfklive_seasons`;

CREATE TABLE `nfklive_seasons` (
  `seasID` int(4) NOT NULL AUTO_INCREMENT,
  `seasNum` int(4) NOT NULL DEFAULT '0',
  `dateStart` datetime NOT NULL,
  `dateEnd` datetime NOT NULL,
  PRIMARY KEY (`seasID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

/*Table structure for table `nfklive_serverlist` */

DROP TABLE IF EXISTS `nfklive_serverlist`;

CREATE TABLE `nfklive_serverlist` (
  `serverID` int(5) NOT NULL AUTO_INCREMENT,
  `ssid` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `dedicated` tinyint(1) NOT NULL,
  `ttl` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `serverIP` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `port` int(5) NOT NULL,
  `hostname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `gameType` int(1) NOT NULL,
  `mapName` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `timeLimit` int(2) NOT NULL,
  `timeLeft` int(3) NOT NULL,
  `playerCount` int(2) NOT NULL,
  `playerMax` int(2) NOT NULL,
  PRIMARY KEY (`serverID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `nfklive_sessions` */

DROP TABLE IF EXISTS `nfklive_sessions`;

CREATE TABLE `nfklive_sessions` (
  `psid` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `playerID` int(5) NOT NULL,
  `sessionIP` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `ttl` datetime NOT NULL,
  UNIQUE KEY `ssID` (`psid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

/*Table structure for table `nfklive_users` */

DROP TABLE IF EXISTS `nfklive_users`;

CREATE TABLE `nfklive_users` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `login` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `password` varchar(32) NOT NULL,
  `email` varchar(50) NOT NULL,
  `access` int(1) NOT NULL DEFAULT '1',
  `playerID` int(8) NOT NULL DEFAULT '0',
  `regIP` varchar(15) DEFAULT NULL,
  `loginIP` varchar(15) DEFAULT NULL,
  `regDate` timestamp NULL DEFAULT NULL,
  `loginDate` timestamp NULL DEFAULT NULL,
  `full_name` varchar(50) DEFAULT NULL,
  `cfg` text NOT NULL,
  `model` varchar(50) NOT NULL DEFAULT 'sarge_default',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=257 DEFAULT CHARSET=utf8 COMMENT='User Accounts';

/*Table structure for table `st_seasons_clan` */

DROP TABLE IF EXISTS `st_seasons_clan`;

CREATE TABLE `st_seasons_clan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clanID` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `playersNum` int(11) NOT NULL,
  `place` int(11) NOT NULL,
  `seasonID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `clanID` (`clanID`),
  KEY `seasonID` (`seasonID`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

/*Table structure for table `st_seasons_ctf` */

DROP TABLE IF EXISTS `st_seasons_ctf`;

CREATE TABLE `st_seasons_ctf` (
  `id` int(11) NOT NULL,
  `playerID` int(8) NOT NULL,
  `frags` int(11) NOT NULL,
  `deaths` int(11) NOT NULL,
  `games` int(11) NOT NULL,
  `wins` int(11) NOT NULL,
  `losses` int(11) NOT NULL,
  `time` int(10) NOT NULL,
  `season` int(10) NOT NULL DEFAULT '0',
  `score` int(10) NOT NULL,
  `place` int(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `st_seasons_dm` */

DROP TABLE IF EXISTS `st_seasons_dm`;

CREATE TABLE `st_seasons_dm` (
  `id` int(11) NOT NULL,
  `playerID` int(8) NOT NULL,
  `frags` int(11) NOT NULL,
  `deaths` int(11) NOT NULL,
  `games` int(11) NOT NULL,
  `wins` int(11) NOT NULL,
  `losses` int(11) NOT NULL,
  `time` int(10) NOT NULL,
  `season` int(10) NOT NULL,
  `score` int(10) NOT NULL,
  `place` int(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `st_seasons_dom` */

DROP TABLE IF EXISTS `st_seasons_dom`;

CREATE TABLE `st_seasons_dom` (
  `id` int(11) NOT NULL,
  `playerID` int(8) NOT NULL,
  `frags` int(11) NOT NULL,
  `deaths` int(11) NOT NULL,
  `games` int(11) NOT NULL,
  `wins` int(11) NOT NULL,
  `losses` int(11) NOT NULL,
  `time` int(10) NOT NULL,
  `season` int(10) NOT NULL DEFAULT '0',
  `score` int(10) NOT NULL,
  `place` int(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `st_seasons_duel` */

DROP TABLE IF EXISTS `st_seasons_duel`;

CREATE TABLE `st_seasons_duel` (
  `id` int(11) NOT NULL,
  `playerID` int(8) NOT NULL,
  `frags` int(11) NOT NULL,
  `deaths` int(11) NOT NULL,
  `games` int(11) NOT NULL,
  `wins` int(11) NOT NULL,
  `losses` int(11) NOT NULL,
  `time` int(10) NOT NULL,
  `season` int(10) NOT NULL DEFAULT '0',
  `score` int(10) NOT NULL,
  `place` int(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `st_seasons_tdm` */

DROP TABLE IF EXISTS `st_seasons_tdm`;

CREATE TABLE `st_seasons_tdm` (
  `id` int(11) NOT NULL,
  `playerID` int(8) NOT NULL,
  `frags` int(11) NOT NULL,
  `deaths` int(11) NOT NULL,
  `games` int(11) NOT NULL,
  `wins` int(11) NOT NULL,
  `losses` int(11) NOT NULL,
  `time` int(10) NOT NULL,
  `season` int(10) NOT NULL DEFAULT '0',
  `score` int(10) NOT NULL,
  `place` int(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*Table structure for table `tr_ladder` */

DROP TABLE IF EXISTS `tr_ladder`;

CREATE TABLE `tr_ladder` (
  `dataID` int(8) NOT NULL AUTO_INCREMENT COMMENT 'Первичный ключ',
  `playerID` int(8) NOT NULL COMMENT 'ИД игрока. Ключ кандидат',
  `games` int(6) NOT NULL DEFAULT '0' COMMENT 'Всего игр на турнирах',
  `wins` int(6) NOT NULL DEFAULT '0' COMMENT 'Всего побед',
  `losses` int(6) NOT NULL DEFAULT '0' COMMENT 'Всего поражений',
  `tourWins` int(6) NOT NULL DEFAULT '0' COMMENT 'Кол-во выигранных турниров',
  `tourNum` int(6) NOT NULL DEFAULT '1' COMMENT 'Сколько раз участвовал на турнирах',
  `lastGame` datetime DEFAULT NULL COMMENT 'Когда играл последний раз',
  `score` int(6) NOT NULL DEFAULT '0' COMMENT 'Очки набранные на турнирах',
  PRIMARY KEY (`dataID`),
  UNIQUE KEY `playerID` (`playerID`)
) ENGINE=MyISAM AUTO_INCREMENT=30 DEFAULT CHARSET=utf8 COMMENT='Рейтинг игроков, которые играли на турнирах';

/*Table structure for table `tr_matchdata` */

DROP TABLE IF EXISTS `tr_matchdata`;

CREATE TABLE `tr_matchdata` (
  `dataID` int(8) NOT NULL AUTO_INCREMENT,
  `matchID` int(6) DEFAULT NULL,
  `playerID` int(8) DEFAULT NULL,
  `greedPos` enum('top','bottom') NOT NULL DEFAULT 'top',
  `score` int(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`dataID`),
  KEY `fk_matchData_matches1` (`matchID`),
  KEY `fk_tr_matchData_tr_playersTour1` (`playerID`)
) ENGINE=MyISAM AUTO_INCREMENT=243 DEFAULT CHARSET=utf8;

/*Table structure for table `tr_matches` */

DROP TABLE IF EXISTS `tr_matches`;

CREATE TABLE `tr_matches` (
  `matchID` int(6) NOT NULL AUTO_INCREMENT,
  `tourID` int(5) DEFAULT NULL,
  `stage` int(2) DEFAULT NULL,
  `game` int(3) DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `gameIDs` varchar(80) NOT NULL DEFAULT '',
  `mapList` varchar(120) NOT NULL DEFAULT '',
  PRIMARY KEY (`matchID`),
  KEY `fk_matches_tourneys1` (`tourID`)
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8;

/*Table structure for table `tr_playersreg` */

DROP TABLE IF EXISTS `tr_playersreg`;

CREATE TABLE `tr_playersreg` (
  `regID` int(8) NOT NULL AUTO_INCREMENT,
  `tourID` int(5) NOT NULL,
  `playerID` int(8) NOT NULL,
  `dateReg` datetime DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`regID`),
  KEY `fk_playersReg_tourneys` (`tourID`)
) ENGINE=InnoDB AUTO_INCREMENT=178 DEFAULT CHARSET=utf8;

/*Table structure for table `tr_tourneys` */

DROP TABLE IF EXISTS `tr_tourneys`;

CREATE TABLE `tr_tourneys` (
  `tourID` int(5) NOT NULL AUTO_INCREMENT,
  `title` varchar(45) DEFAULT NULL,
  `tourNum` int(5) NOT NULL DEFAULT '0',
  `status` int(1) NOT NULL DEFAULT '0',
  `stages` int(2) NOT NULL DEFAULT '0',
  `playersNum` int(3) NOT NULL DEFAULT '0',
  `regNum` int(3) NOT NULL DEFAULT '0',
  `checkNum` int(3) NOT NULL DEFAULT '0',
  `dateStart` datetime DEFAULT NULL,
  `dateCheckin` datetime DEFAULT NULL,
  `dateReg` datetime DEFAULT NULL,
  `dateEnd` datetime DEFAULT NULL,
  `winnerID` int(8) NOT NULL DEFAULT '0',
  `mapList` varchar(120) NOT NULL DEFAULT '',
  `comments` int(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`tourID`),
  KEY `ix_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8;

/* Procedure structure for procedure `sp_getLadderPlace` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_getLadderPlace` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_getLadderPlace`(pts INTEGER(11),gType VARCHAR(5))
BEGIN
	CASE gType
		WHEN 'DUEL' THEN SELECT COUNT(*) AS place FROM nfkLive_ladderDUEL WHERE score > pts;
		WHEN 'DM' THEN SELECT COUNT(*) AS place FROM AltStat_Players WHERE DmReiting > pts;
		WHEN 'TDM' THEN SELECT COUNT(*) AS place FROM AltStat_Players WHERE TdmReiting > pts;
		WHEN 'CTF' THEN SELECT COUNT(*) AS place FROM AltStat_Players WHERE CtfReiting > pts;
		WHEN 'DOM' THEN SELECT COUNT(*) AS place FROM AltStat_Players WHERE DomReiting > pts;
	END CASE;
END */$$
DELIMITER ;

/* Procedure structure for procedure `sp_test` */

/*!50003 DROP PROCEDURE IF EXISTS  `sp_test` */;

DELIMITER $$

/*!50003 CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_test`(OUT t INTEGER(11))
BEGIN
	select 2+3 into t;
	
END */$$
DELIMITER ;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
