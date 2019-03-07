#!/bin/sh

# add to cron for every 5 seconds
cd /home/harpywar/discordbot && php update_nfkplanet.php && php update_donate.php
