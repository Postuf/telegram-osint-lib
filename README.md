# Telegram OSINT scenario library

[![Gitpod ready-to-code](https://img.shields.io/badge/Gitpod-ready--to--code-blue?logo=gitpod)](https://gitpod.io/#https://github.com/Postuf/telegram-osint-lib)

[![codecov](https://codecov.io/gh/Postuf/telegram-osint-lib/branch/master/graph/badge.svg)](https://codecov.io/gh/Postuf/telegram-osint-lib)

## Description

A lot of Telegram API libraries around, but none of them demonstrates how to execute complex scenarios like draining all photos from channels, monitor users presence or register new account. This projects aims to correct situation: now you can easily create new scenarios and one-click execute existing ones.

Using Telegram API from official Android client.

## Rationale

Telegram protocol https://core.telegram.org/ has technically thorough and detailed documentation, but does not cover usage scenarios.
Our goal is to make a library that implements some real-life OSINT usage scenarios, including:
* searching user in specific groups;
* parsing group members;
* monitoring user online status;
* downloading photos from channel;
* monitoring user profile changes (photo/bio/etc.);
* fetching messages by specific user.

## Requirements

* PHP 7.4+
* Composer
  * phpseclib

## Docs
* [Create scenario](docs/create-scenario.md)

## QuickStart

First of all, add library to your app user composer:

```
composer require postuf/telegram-api-lib
```

To check out usage examples, go to `examples` dir.
You need auth keys generated, run `php registration.php` to get this.
Now you are all set, you can run any of examples, for example, `php parseGroupMembers.php`, and check the output.

Verbose logging (all messages sent/received) is enabled by default, add `--info` to arguments to suppress it.

### Docker container

```
docker build -t telegram-osint-lib .
docker run -d -t --name tg-osint-lib telegram-osint-lib
docker exec -it tg-osint-lib /bin/bash
php examples/registration.php
```

When you get AuthKey in registration script, you can use it the following way:
```
docker exec  --env BOT=your-auth-key -i tg-osint-lib php examples/monitorNumbers.php -n 123123123
# if you save key to file
docker exec  --env BOT=@auth-key-filename-in-docker -i tg-osint-lib php examples/monitorNumbers.php -n 123123123
```

# Limitations

2FA not supported.
