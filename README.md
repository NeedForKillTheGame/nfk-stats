# NFK Web Statistics

## Description
Match statistics site. Works on PHP 5-7.\
Runs in a single Docker container since it's not actively maintained and required PHP versions are considered deprecated and hard to install on modern GNU/Linux distributions.\
Docker image is based on Alpine Linux, with nginx+php-fpm running under supervisord. Latest PHP 7 version is compiled with all necessary dependencies. Dockerfile was written with one particulal server configuration in mind, meaning it still requires an external database and (preferably) a reverse-proxy.\
The suggested configuration below is more of an example, some adaptations are probably required (PRs are welcome).

## Installation
1. Clone this repository
```bash
git clone https://github.com/NeedForKillTheGame/nfk-stats
```
2. Build the docker image
```bash
cd nfk-stats
docker build [ --build-arg="OPTION=value" ] -t local/nfkstats .
```

Available build options:\
`THREADS`           — Number of CPU threads used for compiling (*Default: 1*).\
`TESTS_ENABLE`      — Run build tests after compiling (*Default: 0*).\
`UID`               — UID to match owner of the static files on the host system (*Default: 200*).

Building example on 16-core CPU (with multithreading enabled):
```bash
docker build --build-arg="THREADS=32" -t local/nfkstats .
```

## Usage
1. Create the config file using `inc/config.inc.php.example`:
```bash
mkdir -p /srv/nfkstats
cp inc/config.inc.php.example /srv/nfkstats/config.inc.php
vim /srv/nfkstats/config.inc.php
```
2. Create directories for static files (or restore it from the backup) on a host system:
```bash
mkdir /srv/nfkstats/demos
mkdir -p /srv/nfkstats/images/maps
```
3. Add a new user with desired `UID` to avoid permission issues and make him the owner of the directories:
```bash
useradd -u 200 -g www-data nfkstats
chown -R nfkstats:www-data /srv/nfkstats
```
4. Run the container:
```bash
docker run -d --rm --restart=always\
  --name=nfkstats \
  --network=host \
  --volume /srv/nfkstats/config.inc.php:/var/www/inc/config.inc.php:ro \
  --volume /srv/nfkstats/demos:/var/www/demos:rw \
  --volume /srv/nfkstats/images/maps:/var/www/images/maps:rw \
  --env OPTION=value \
  --memory=256M \
  local/nfkstats
```
Statistics site should be available via port `:80`.\
The available options are:\
`PORT`              — Nginx HTTP port (*Default: 80*).\
`REAL_IP`           — IP for nginx [set_real_ip_from](https://nginx.org/en/docs/http/ngx_http_realip_module.html) directive (*Default: disabled*).

## Additional information
### Nginx as a reverse proxy
Check the [example configuration](https://github.com/NeedForKillTheGame/nfk-stats/wiki/nginx-as-reverse-proxy-(example)). It's recommended to protect `/nfkstats.php` URL with IP whitelisting and `/do/new_seasonJGA` with [Basic Auth](https://docs.nginx.com/nginx/admin-guide/security-controls/configuring-http-basic-authentication/) as it's mentioned.
