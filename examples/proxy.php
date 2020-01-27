<?php

use TelegramOSINT\Scenario\StatusWatcherScenario;
use TelegramOSINT\Tools\Proxy;

require_once __DIR__.'/../vendor/autoload.php';

if (!isset($argv[1]) || $argv[1] == '--help' || $argv[1] == '--info') {
    $msg = <<<'MSG'
Usage: php proxy.php numbers
    numbers: 79061231231,79061231232,...
MSG;
    die($msg);
}

// here we get contact list and get contact online status
// avatars are saved to current directory using proxy

// this is a dummy proxy
// use `node ../vendor/postuf/socks-proxy-async/node/proxy.js 1080` to start
$proxy = new Proxy('127.0.0.1:1080');
$numbers = explode(',', $argv[1]);

/* @noinspection PhpUnhandledExceptionInspection */
(new StatusWatcherScenario($numbers, [], null, $proxy))->startActions();
