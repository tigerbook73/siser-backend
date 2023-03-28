- [Introduction](#markdown-header-introduction)
- [VSCode Configure](#markdown-header-vscode-configure)
  - [Plugin](#markdown-header-plugin)
  - [Setting](#markdown-header-setting)
- [Backend](#markdown-header-backend)
  - [Install packages](#markdown-header-install-packages)
  - [Configure host](#markdown-header-configure-host)
  - [Configure .env](#markdown-header-configure-env)
  - [Setup / create local DB](#markdown-header-setup-create-local-db)
  - [Setup developement mail server](#markdown-header-setup-developement-mail-server)
  - [Rebuild Database](#markdown-header-rebuild-database)
  - [Start development server](#markdown-header-start-development-server)
  - [Configure XDebug](#markdown-header-configure-xdebug)
  - [Artisan \& Artisan Tinker](#markdown-header-artisan-artisan-tinker)
  - [Static code analyze](#markdown-header-static-code-analyze)
  - [PHPUnit test](#markdown-header-phpunit-test)

## Introduction

This reposition includes the backend of Siser Software Project.

The following instructions are based on Homestead environments.

## VSCode Configure

Please use vscode as your main editor to make sure code are formatted the same way.

### Plugin

Please see .vscode/extensions.json for detailed information. The following are some examples:

+ PHP Debug
+ PHP Intelephense
+ Prettier - Code formatter
+ ...


### Setting
.vscode/settings.json has been included in this projects.

## Backend

### Install packages
```bash
composer install
```

### Configure host
```bash
# add siser.test to /etc/hosts
sudo echo "
127.0.1.1   siser.test    # default host name for develop environment
" >> /etc/hosts
```

### Configure .env

Please use .env.example as the start point of .env file.

Typically, the following field need to be updated:

```
# local database configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=siser
DB_USERNAME=homestead
DB_PASSWORD=secret

# mail configuration
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@siser.com"
MAIL_FROM_NAME="${APP_NAME}"

# AWS access
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=

# AWS coginto access
COGNITO_ACCESS_KEY_ID=
COGNITO_SECRET_ACCESS_KEY=
```

### Setup / create local DB

See Laravel Homestead or Sail document for help.

### Setup developement mail server

See Laravel MailHog for help.

### Rebuild Database
```bash
# run the follwoing command to rebuild database from scratch.
./bin/rebuild-data.sh
```

### Start development server

See Laravel Homestead or Sail document for help.

### Configure XDebug

See Laravel Homestead or Sail document for help.

### Artisan & Artisan Tinker

Please be familar with artisan command and artisan tinker. These tools will greatly help you with the development, debug and maintanence of Laraval based projects.

### Static code analyze
```bash
./vendor/bin/phpstan
```

### PHPUnit test
```bash
php artisan test
```