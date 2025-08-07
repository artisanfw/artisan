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
Currently, this project is in **alpha**, so you will need to run:
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

### Database
Add this two tables to your database:
```sql
CREATE TABLE tokens (
   id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
   entity_name VARCHAR(32) NOT NULL,
   entity_id INT(32) UNSIGNED NOT NULL,
   code VARCHAR(32) NOT NULL,
   type VARCHAR(16) NOT NULL,
   behavior VARCHAR(16) NOT NULL,
   remaining_uses TINYINT UNSIGNED NULL DEFAULT '1',
   expiration_at DATETIME NOT NULL,
   created_at DATETIME NOT NULL,
   PRIMARY KEY (id),
   INDEX idx_entity_type (entity_name, entity_id, type),
   INDEX idx_code_type (code, type)
) ENGINE = InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE users (
  id int(11) NOT NULL,
  email varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  verified tinyint(1) NOT NULL DEFAULT 0,
  name varchar(255) NOT NULL,
  surname varchar(50) NOT NULL,
  country_code varchar(3) DEFAULT NULL,
  timezone varchar(64) DEFAULT NULL,
  created_at datetime NOT NULL COMMENT 'UTC',
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

