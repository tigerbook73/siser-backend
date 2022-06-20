- [Introduction](#introduction)
- [FrontEnd](#frontend)
  - [Prerequisite](#prerequisite)
  - [Folder](#folder)
  - [update packages](#update-packages)
  - [start development server](#start-development-server)
  - [build](#build)
- [Backend](#backend)
  - [update packages](#update-packages-1)
  - [configure host](#configure-host)
- [VSCode Configure](#vscode-configure)
  - [Plugin](#plugin)
  - [Setting](#setting)

## Introduction

This reposition includes the frontend and backend of Siser Software Project.

## FrontEnd

### Prerequisite

```bash
# install yarn
npm install -g yarn

# install quasar
npm i -g @quasar/cli

```

### Folder
```bash
# cd front end directory
cd frontend
```

### update packages
```bash
yarn
```

### start development server
```bash
yarn dev
```

### build
```bash
yarn build
```

## Backend

### update packages
```bash
composer install
```

### configure host
```bash
# add siser.test to /etc/hosts
sudo echo "
127.0.1.1   siser.test
" >> /etc/hosts
```

## VSCode Configure

### Plugin
+ PHP Debug
+ PHP Intelephense
+ Prettier - Code formatter

### Setting
.vscode/settings.json has been included in this projects.
