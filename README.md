# NFK Web Statistics

## Installation
### Install PHP dependencies
* PHP 5+ (works on 7)
* `apt install php-mbstring php-gd php-bz2 php7.0-xml`

### Set up the configuration
* Export the existing database or create new one
* Edit and copy the config file from `inc/config.inc.php.example` to `inc/config.inc.php`

### Schedule cron script to update graph images
`cron/update_graphs.sh`

### Set write permissions
* `demos/`
* `mods/inc/dateflag.txt`

### Deny access from outside to:
* `mods`
* `langs`
* `inc`
* `cron`
