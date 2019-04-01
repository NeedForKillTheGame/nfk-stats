<?php

/*
 * Update Discord channel with players from the NFK Planet
 * (c) 2019 HarpyWar <harpywar@gmail.com>
 */

require_once __DIR__.'/vendor/autoload.php';
require_once 'config.php';

use RestCord\DiscordClient;
use Moment\Moment;


// fetch players from the plalet
$data = file_get_contents(Config::planet_data_url);
$servers = json_decode($data);
$body = "No players online\n";
$p_count = 0;
// count players
foreach ($servers as $s)
{
	$load = explode('/', $s->load);
	foreach ($s->players as $p)
	{
		if ($p->playerID)
			$p_count++;
	}
	
}
$players = "";
// display players
if ($p_count > 0)
{
	$body =  "**$p_count players online**\n";
	$body .= '```';

	foreach ($servers as $s)
	{
		if (count($s->players) == 0)
			continue;

		$body .= '[' . $s->gametype . '] ' . $s->map . ' '  . $s->load . '
';
		foreach ($s->players as $p)
		{
			$body .= '- ' . ($p->playerID ? '' : '(bot) ') . $p->name . '
';
			$players .= $p->name . "\n";
		}
		$body .= '
';
	}
	$body .= '
```';
}

// save previous players count to decrease changes on discord
$players_prev = 0;
if (!file_exists(Config::players_file))
{
	file_put_contents(Config::players_file, $p_count);
	echo "init " .  Config::players_file;
}
else
{
	// read from cache
	$players_prev = file_get_contents(Config::players_file);
	// if previous value the same then exit script
	if ($players == $players_prev)
	{
		// if minute elapsed after last update then need to update match list (do not exit script)
		// FIXME: update in 0-15 seconds for every minute (because script run every 10 seconds)
		if (date("s") > 30)
		{
			exit;
		}
	}
	else
	{
		file_put_contents(Config::players_file, $players);	
	}
}

$moment = new Moment( filemtime(Config::players_file) );
$date = $moment->fromNow()->getRelative(); 
// bold font weight if activity in last 60 minutes
$date_str = !strpos($a, 'hour') && !strpos($a, 'day')
	? "**" . $date . "**"
	: $date;
$body .= "\n*Last activity " . $date_str . "*\n\n\n";



// get last matches
$data = file_get_contents(Config::matches_data_url);
$matches = json_decode($data);
$ebody = "\n";
foreach ($matches as $m)
{
	$moment = new Moment(strtotime($m->dateTime));
	$date = $moment->fromNow()->getRelative(); 

	$ebody .= '[`#`' . $m->matchID . '](https://stats.needforkill.ru/match/' . $m->matchID . ') ';
	$ebody .= '[' . $m->gameType . '] **' . $m->players . '** *' . $date . '*';
	$ebody .= "\n";
}
$embed = array(
	'title' => "**Last " . count($matches) . " matches**",
	'url' => "https://stats.needforkill.ru",
	'description' => $ebody
);

// send update  request to discord
$client = new DiscordClient([
	'token' => Config::discord_token
]);

try
{
	$params = array(
		"channel.id" => Config::discord_planet_channel_id
	);
	// modify channel name
	$params['name'] = Config::channel_title . ($p_count > 0
			? sprintf(Config::channel_title_playing, $p_count)
			: '');
	$client->channel->modifyChannel($params);

	// get channel messages
	$messages = $client->channel->getChannelMessages($params);
	$params['content'] = $body;
	$params['embed'] = $embed;
	if (count($messages) > 0)
	{
		$params['message.id'] = (int)$messages[count($messages) - 1]['id'];

		// edit last message
		$result = $client->channel->editMessage($params);
	}
	else
	{
		// create new message if there is no one
		$result = $client->channel->createMessage($params);
	}
}
catch (Exception $e)
{
	echo "ERROR\n" . $e->getMessage();
}



/*

// get icon file
$imagefile = "nfklogo" . DIRECTORY_SEPARATOR . 'nfk' . ($p_count > 0 ? $p_count : '') . '.png';
$img = file_get_contents($imagefile);
$img_base64 = base64_encode($img);


// set server icon
// FIXME: we rejected this idea cause there are very hard limits for server edit
// https://discordapp.com/developers/docs/topics/rate-limits
$params = array(
	"guild.id" => Config::discord_guild_id,
	"icon" => 'data:image/png;base64,' . $img_base64,
	"name" => "Need For Kill" . ($p_count > 0
			? sprintf("(%d online)", $p_count)
			: ''),
);
$result = $client->guild->modifyGuild($params);
var_dump($result);
*/










// PLANET API OUTPUT EXAMPLE
/*
[
   {
      "name":"cleanvoice.ru Teamplay",
      "hostname":"^#cleanvoice.ru ^2Teamplay",
      "map":"zef1",
      "gametype":"DM",
      "load":"1\/4",
      "ip":"5.9.10.202:29997",
      "players":[
         {
            "playerID":"3738",
            "nick":"^b^1H^n^7arpy^b^1War",
            "name":"HarpyWar",
            "country":"ru",
            "model":"sarge_default",
            "points":"427",
            "place":"0"
         }
      ]
   },
   {
      "name":"cleanvoice.ru DM",
      "hostname":"^#cleanvoice.ru ^1DM",
      "map":"pro-dm0",
      "gametype":"DM",
      "load":"0\/2",
      "ip":"5.9.10.202:29995",
      "players":[

      ]
   },
   {
      "name":"cleanvoice.ru DM",
      "hostname":"^#cleanvoice.ru ^1DM",
      "map":"cpm3",
      "gametype":"DM",
      "load":"0\/2",
      "ip":"5.9.10.202:29988",
      "players":[

      ]
   }
]

 */