#!/bin/sh

# run every 10 min

cd /var/www/cron

php graphs.php graph-7days
php graphs.php graph-62days
php graphs.php graph-month
php graphs.php graph-year-month-players
