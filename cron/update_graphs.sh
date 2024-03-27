#!/bin/sh

# run every 1 hour

cd /var/www/stats.needforkill.ru/cron

/usr/bin/php7.2 graphs.php graph-7days
/usr/bin/php7.2 graphs.php graph-62days
/usr/bin/php7.2 graphs.php graph-month
/usr/bin/php7.2 graphs.php graph-year-month-players
