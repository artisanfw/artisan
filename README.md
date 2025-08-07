# Artisan
A lightweight framework designed for quickly launching small projects.

This version includes a complete solution with:
* Account management, including token-based authorization and short codes sent via email.
* Expiration handling for tokens and codes.
* IP geolocation detection (requires GeoLite2-City database).
* Translation support.
* Doctrine integration for database management.


## Installation
```bash
composer create-project artisanfw/artisanfw my_project_dir

cd my_project_dir
composer install
```

## Setup
### .config.php
1. Copy the configuration template
```bash
cp config_example.php .config.php
```
2. Edit `.config.php`, paying special attention to
3. Remove the config_example

>'geoip' => license_key

>'accounts' => token_salt

*License key:* To obtain a valid license, you must register on MaxMind’s website.

### MaxMind GeoLite2
Run the cron job to download the GeoLite2-City database:
```bash
mkdir geodb
php src/Crons/geodb_downloader.php
```
### Bootstrap
Review the Bootstrap configuration if you need to modify anything.
You can find more details about bootstraps and other API-specific behaviors in the [artisanfw/api](https://github.com/artisanfw/api) repository.

### Configure your web server (Apache2, Nginx, etc).
A default `.htaccess` file is provided for convenience, but it is strongly recommended to review and properly configure your web server settings.


