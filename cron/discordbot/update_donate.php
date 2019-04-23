<?php

/*
 * Update Discord channel with donations
 * (c) 2019 HarpyWar <harpywar@gmail.com>
 */

require_once __DIR__.'/vendor/autoload.php';
require_once 'config.php';

use RestCord\DiscordClient;


// if file modification time less update_interval seconds
if (file_exists(Config::donate_file) && filemtime(Config::donate_file) + Config::donate_update_interval > time())
{
	return;
}


// add donations
$embed = array();
$sum = 0;

try
{
	// get players count from the planet
	$data = file_get_contents(Config::last_donations_url);
	$json = json_decode($data);
	$donations = $json->result;
	// get total sum
	foreach ($donations as $d)
	{
		$sum += $d->order_sum;
	}
	$embed['title'] = "DONATE FOR THE NFK SERVER <<";
	$embed['url'] = "http://nfk.harpywar.com/donate.php";
	$embed['description'] = '';
	$procents = round($sum / (Config::donate_max / 100));
	$embed['description'] .= "**" . $sum . " / " . Config::donate_max . " ₽** (" . $procents . "% for this year" . (($sum >= Config::donate_max) ? ", goal reached!" : "") . ")\n";
		
	$embed['description'] .= "\n**Respect these guys**\n";
	$embed['description'] .= "---------------------->\n\n";
	for ($i = count($donations) - 1; $i >= 0; $i--)
	{
		$d = $donations[$i];
		$embed['description'] .= ":skull_crossbones:  **" . $d->order_sum . " ₽** from " . $d->username . "\n";
		if ($d->message) {
			$embed['description'] .= "```\n" . $d->message . "```\n";	
		} else {
			$embed['description'] .= "\n";
		}
	}
}
catch (Exception $e) {
	echo $e->getMessage();
}

// update sym
file_put_contents(Config::donate_file, $sum);





// get random citate
$idx = rand(0, count(Config::quotes) - 1);
$body = '> ' . Config::quotes[$idx] . "\n\n";






// send update  request to discord
$client = new DiscordClient([
	'token' => Config::discord_token
]);

try
{
	$params = array(
		"channel.id" => Config::discord_donate_channel_id
	);
	// modify channel name
	$params['name'] = Config::donate_channel_title;
	if ($sum < Config::donate_max)
		$params['name'] .= sprintf(Config::donate_channel_title_sum, $sum, Config::donate_max);
	
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

