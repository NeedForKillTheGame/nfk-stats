<?php

class Config
{
	// bot token
	const discord_token = "";
	// server id
	const discord_guild_id = 536281825077755954;
	
	// channel where to put planet scanner output
	const discord_planet_channel_id = 538874220944424970;
	// channel name when modify
	const channel_title = "nfkplanet";
	const channel_title_playing = "_%d_online";
	// file with last players count from the planet
	const players_file = "players.txt";

	
	const discord_donate_channel_id = 553143868212772874;
	const donate_channel_title = "donate";
	const donate_channel_title_sum = "_%d∕%d"; // add donation amount to the channel name
	// file with last updated donation sum
	const donate_file = "donate.txt";

	const donate_max = 2400; // max amount of the donation
	const donate_update_interval = 300; // update interval for the donation channel (min value, max depends on nfkplanet activity)

	
	
	const planet_data_url = "https://stats.needforkill.ru/api.php?action=gsl";
	const last_donations_url = "http://nfk.harpywar.com/api.php?action=donations";
	
	
	
	const quotes = array (
		"Kindness in words creates confidence. Kindness in thinking creates profoundness. Kindness in giving creates love.",
		"No one is useless in this world who lightens the burdens of another.",
		"The real destroyer of the liberties of the people is he who spreads among them bounties, donations and benefits.",
		"You have not lived today until you have done something for someone who can never repay you.     ",
		"Donation for the blind. A fee for falling into their own trap.",
		"The proper aim of giving is to put the recipients in a state where they no longer need our gifts.",
		"It is more difficult to give money away intelligently than to earn it in the first place. ",
		"It's not how much we give but how much love we put into giving.",
		"Where there is charity and wisdom, there is neither fear nor ignorance.",
		"You give but little when you give of your possessions. It is when you give of yourself that you truly give.",
		"Whatever you think the world is withholding from you, you are withholding from the world.",
		"The value of a man resides in what he gives and not in what he is capable of receiving.",
		"You're learning to be nourished by the love you give, not by the validation offered in response to your giving.",
		"Giving is better than receiving because giving starts the receiving process.",
		"By giving people the power to share, we're making the world more transparent.",
		"By donating, you will also be supporting your own community and literally saving lives with every pint donated.",
		"The life of a man consists not in seeing visions and in dreaming dreams, but in active charity and in willing service.",
		"Giving frees us from the familiar territory of our own needs by opening our mind to the unexplained worlds occupied by the needs of others.",
		
		"No one has ever become poor by giving.",
		"Think of giving not as a duty but as a privilege.",
		"The measure of life is not its duration, but its donation.",
		"Every good act is charity. A man's true wealth hereafter is the good that he does in this world to his fellows.",
		"When we give cheerfully and accept gratefully, everyone is blessed.",
		"There is no exercise better for the heart than reaching down and lifting people up.  ",
		"It’s good to be blessed. It’s better to be a blessing.",
		"To give away money is an easy matter and in any man's power. But to decide to whom to give it, and how large and when, and for what purpose and how, is neither in every man's power nor an easy matter.",
		"Giving is not just about making a donation. It is about making a difference.",
		"Though our donations are made to please ourselves, we insist, upon those who receive our alms being pleased with them.",
		"One good thing about donation, once you do it, you get addicted to it because it brings great joy and happiness to you.",
		"Value of life depends not on your possessions, but on your donation.",
		"We make a living by what we get, but we make a life by what we give.",
	);
}
