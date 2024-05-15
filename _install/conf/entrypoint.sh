#!/bin/bash
# update configuration
replaceConfigParam() {
    cfg=$1
    cvar=$2
    cvarUncomment=`echo $2 | sed 's/#//g'`
    cvarValue="$3"
    if [[ $cvarValue ]]; then
        sed -i "s/$cvar .*/$cvarUncomment $cvarValue;/g" $cfg
    fi
}

replaceConfigParam /etc/nginx/http.d/nfkstats-nginx.conf "listen" "$PORT"
replaceConfigParam /etc/nginx/http.d/nfkstats-nginx.conf "#set_real_ip_from" "$REAL_IP"

# generate graphs
source /var/www/cron/update_graphs.sh

# launch nginx+php-fpm+supercronic
supervisord -n -c /etc/supervisord.conf
