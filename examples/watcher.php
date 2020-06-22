<?php

use TelegramOSINT\LibConfig;
use TelegramOSINT\Scenario\ClientGenerator;
use TelegramOSINT\Scenario\StatusWatcherScenario;
use TelegramOSINT\Tools\Proxy;

require_once __DIR__.'/../vendor/autoload.php';

$argsOrFalse = getopt('n:u:p:f:h', ['numbers:', 'users:', 'proxy:', 'photo:', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
    || ((!array_key_exists('n', $argsOrFalse) && !array_key_exists('numbers', $argsOrFalse))
        && (!array_key_exists('u', $argsOrFalse) && !array_key_exists('users', $argsOrFalse)))
) {
    echo <<<'EOT'
Usage:
    php watcher.php -n numbers [ -u users]
    php watcher.php --numbers numbers
    php watcher.php --users users

   -n, --numbers                Comma separated phone number list (e.g. 79061231231,79061231232).
   -u, --users                  Comma separated username list (e.g. aaa,bbb).
   -p, --proxy                  Proxy to use.
   -f, --photo                  Fetch photos (off by default).
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$numbers = array_filter(explode(',', $argsOrFalse['n'] ?? $argsOrFalse['numbers'] ?? ''));
$users = array_filter(explode(',', $argsOrFalse['u'] ?? $argsOrFalse['users'] ?? ''));
$proxyStr = $argsOrFalse['p'] ?? $argsOrFalse['proxy'] ?? null;
if ($proxyStr) {
    $proxyStr = trim($proxyStr, "'");
}
$fetchPhoto = (bool) ($argsOrFalse['f'] ?? $argsOrFalse['photo'] ?? false);
/** @noinspection PhpUnhandledExceptionInspection */
$generator = new ClientGenerator(LibConfig::ENV_AUTHKEY, $proxyStr ? new Proxy($proxyStr) : null);

// here we get contact list and get contact online status

/** @noinspection PhpUnhandledExceptionInspection */
(new StatusWatcherScenario($numbers, $users, new ClientGenerator()))
    ->startActions();
