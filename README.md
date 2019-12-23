# Telegram API

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

## Howto

To get started, go to `examples` dir.
First of all, you need auth keys generated, run `php registration.php` to get those.
Some examples require two accounts to run, save those keys to `first.authkey` and `second.authkey`, respectively.
Now you are all set, you can run any of them, for example, `php parseGroupMembers.php`, and check the output.

# Limitations

We currently use Europe/CIS DC, see `src/LibConfig.php` for details/to change your DC, see also: https://core.telegram.org/method/help.getNearestDc .
