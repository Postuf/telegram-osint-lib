<?php

use TelegramOSINT\LibConfig;
use TelegramOSINT\Scenario\ClientGenerator;
use TelegramOSINT\Scenario\StatusWatcherScenario;
use TelegramOSINT\Scenario\UserContactsScenario;
use TelegramOSINT\Tools\Proxy;

require_once __DIR__.'/../vendor/autoload.php';

$argsOrFalse = getopt('n:p:f:h', ['numbers:', 'proxy:', 'photo:', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
    || (!array_key_exists('n', $argsOrFalse) && !array_key_exists('numbers', $argsOrFalse))
) {
    echo <<<'EOT'
Usage:
    php watcher.php -n numbers
    php watcher.php --numbers numbers

   -n, --numbers                Comma separated phone number list (e.g. 79061231231,79061231232).
   -p, --proxy                  Proxy to use.
   -f, --photo                  Fetch photos (off by default).
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$numbers = explode(',', $argsOrFalse['n'] ?? $argsOrFalse['numbers']);
$proxyStr = $argsOrFalse['p'] ?? $argsOrFalse['proxy'] ?? null;
if ($proxyStr) {
    $proxyStr = trim($proxyStr, "'");
}
$fetchPhoto = (bool) ($argsOrFalse['f'] ?? $argsOrFalse['photo'] ?? false);
/** @noinspection PhpUnhandledExceptionInspection */
$generator = new ClientGenerator(LibConfig::ENV_AUTHKEY, $proxyStr ? new Proxy($proxyStr) : null);

// here we get contact list and get contact online status
// avatars are saved to current directory

/** @noinspection PhpUnhandledExceptionInspection */
(new UserContactsScenario($numbers, [], static function () use ($numbers) {
    (new StatusWatcherScenario($numbers, [], new ClientGenerator()))
        ->startActions(false);
}, $generator, $fetchPhoto))->startActions();
