#!/bin/sh

# set it up to run every 1 hour
cd /var/www/cron
echo "$(date +'%Y/%m/%d %H:%M:%S') Generating graphs..."

php graphs.php graph-7days
php graphs.php graph-62days
php graphs.php graph-month
php graphs.php graph-year-month-players

echo "$(date +'%Y/%m/%d %H:%M:%S') Finished generating graphs."
