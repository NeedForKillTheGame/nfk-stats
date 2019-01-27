<?php

class Config
{
	// bot token
	// make sure this bot has entered Discord gateway at least once
	const discord_token = "";
	// server id
	const discord_guild_id = 536281825077755954;
	
	// channel where to put planet scanner output
	const discord_channel_id = 538874220944424970;
	// channel name when modify
	const channel_title = "nfkplanet";
	const channel_title_playing = "_%d_online";
	
	// file with last players count from the planet
	const players_file = "p_count.txt";
	
	const planet_data_url = "http://stats.needforkill.ru/api.php?action=gsl";
}
