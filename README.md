# Artisan
A lightweight framework designed for quickly launching small projects.

This version includes a complete solution with:
* Account management
* Emails powered by Twig.
* Short code authentication for account validation and login. [See artisanfw/token-manager](https://github.com/artisanfw/token-manager)
* Expiration handling for tokens and codes.
* IP geolocation detection. [See artisanfw/where](https://github.com/artisanfw/where).
* Translation support [See artisanfw/i18n](https://github.com/artisanfw/i18n)
* Doctrine integration for database management. [See artisanfw/doctrine](https://github.com/artisanfw/doctrine)


## Installation
Currently, this project is in **beta**, so you will need to run:
```bash
composer create-project artisanfw/artisan my_project_dir dev-main --repository='{"type": "vcs", "url": "https://github.com/artisanfw/artisan"}'
```
You only need to change `my_project_dir` to your project folder name.


## Setup
### .config.php
1. Copy the configuration template
```bash
cp config_example.php .config.php
```
2. Edit `.config.php`, paying special attention to

>'geoip' => license_key

>'accounts' => token_salt

*License key:* To obtain a valid license, you must register on MaxMind’s website.

3. Remove the config_example

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


