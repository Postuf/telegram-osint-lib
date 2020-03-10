<?php

use TelegramOSINT\Scenario\StatusWatcherScenario;
use TelegramOSINT\Tools\Proxy;

require_once __DIR__.'/../vendor/autoload.php';

$argsOrFalse = getopt('n:h', ['numbers:', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
    || (!array_key_exists('n', $argsOrFalse) && !array_key_exists('numbers', $argsOrFalse))
) {
    echo <<<'EOT'
Usage:
    php proxy.php -n numbers 
    php proxy.php --numbers numbers

   -n, --numbers                Comma separated phone number list (e.g. 79061231231,79061231232).
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$numbers = explode(',', $argsOrFalse['n'] ?? $argsOrFalse['numbers']);

// here we get contact list and get contact online status
// avatars are saved to current directory using proxy

// this is a dummy proxy
// use `node ../vendor/postuf/socks-proxy-async/node/proxy.js 1080` to start
$proxy = new Proxy('127.0.0.1:1080');

/* @noinspection PhpUnhandledExceptionInspection */
(new StatusWatcherScenario($numbers, [], null, $proxy))->startActions();
