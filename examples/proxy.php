<?php

use SocksProxyAsync\Proxy;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__ . '/MyTgClientDebug.php';

// here we get contact list and get contact online status
// avatars are saved to current directory using proxy

// this is a dummy proxy
// use `node ../vendor/postuf/socks-proxy-async/node/proxy.js 1080` to start
$proxy = new Proxy('127.0.0.1:1080');

/** @noinspection PhpUnhandledExceptionInspection */
(new MyTgClientDebug($proxy))->startActions();
