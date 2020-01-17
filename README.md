# Telegram OSINT scenario library

[![Build Status](https://travis-ci.org/postuf/telegram-osint-lib.svg?branch=master)](https://travis-ci.org/postuf/telegram-osint-lib) [![codecov](https://codecov.io/gh/Postuf/telegram-osint-lib/branch/master/graph/badge.svg)](https://codecov.io/gh/Postuf/telegram-osint-lib)

## Description

Telegram API from official mobile client.

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

* PHP 7.1+
* Composer
  * phpseclib

## Quickstart

First of all, add library to your app user composer:

```
composer require postuf/telegram-api-lib
```

To check out usage examples, go to `examples` dir.
You need auth keys generated, run `php registration.php` to get those.
Some examples require two accounts to run, save those keys to `first.authkey` and `second.authkey`, respectively.
Now you are all set, you can run any of them, for example, `php parseGroupMembers.php`, and check the output.

Verbose logging (all messages sent/received) is enabled by default, add `--info` to arguments to suppress it.

### Docker container

```
docker build -t telegram-osint-lib . 
docker run -d --name tg-osint-lib telegram-osint-lib
docker exec -it tg-osint-lib /bin/bash
php examples/registration.php
```

# Limitations

We currently use Europe/CIS DC, see `src/LibConfig.php` for details/to change your DC, see also: https://core.telegram.org/method/help.getNearestDc .
