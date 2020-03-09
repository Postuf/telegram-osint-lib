<?php

use TelegramOSINT\Scenario\ClientGenerator;
use TelegramOSINT\Scenario\StatusWatcherScenario;
use TelegramOSINT\Scenario\UserContactsScenario;

require_once __DIR__.'/../vendor/autoload.php';

$argsOrFalse = getopt('n:h', ['numbers:', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
    || (!array_key_exists('n', $argsOrFalse) && !array_key_exists('numbers', $argsOrFalse))
) {
    echo <<<'EOT'
Usage:
    php watcher.php -n numbers
    php watcher.php --numbers numbers

   -n, --numbers                Comma separated phone number list (e.g. 79061231231,79061231232).
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$numbers = explode(',', $argsOrFalse['n'] ?? $argsOrFalse['numbers']);

// here we get contact list and get contact online status
// avatars are saved to current directory

/** @noinspection PhpUnhandledExceptionInspection */
(new UserContactsScenario($numbers, function () use ($numbers) {
    (new StatusWatcherScenario($numbers, [], new ClientGenerator()))
        ->startActions(false);
}))->startActions();
