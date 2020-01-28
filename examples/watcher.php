<?php

use TelegramOSINT\Scenario\ClientGenerator;
use TelegramOSINT\Scenario\StatusWatcherScenario;
use TelegramOSINT\Scenario\UserContactsScenario;

require_once __DIR__.'/../vendor/autoload.php';

if (!isset($argv[1]) || $argv[1] == '--help' || $argv[1] == '--info') {
    $msg = <<<'MSG'
Usage: php watcher.php numbers
    numbers: 79061231231,79061231232,...
MSG;
    die($msg);
}

$numbers = explode(',', $argv[1]);

// here we get contact list and get contact online status
// avatars are saved to current directory

/** @noinspection PhpUnhandledExceptionInspection */
(new UserContactsScenario($numbers, function () use ($numbers) {
    /* @noinspection PhpUnhandledExceptionInspection */
    (new StatusWatcherScenario($numbers, [], new ClientGenerator('STATUS_KEY')))
        ->startActions(false);
}))->startActions();
