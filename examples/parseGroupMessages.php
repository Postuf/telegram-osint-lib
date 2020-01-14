<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use TelegramOSINT\Scenario\GroupMessagesScenario;

const INFO = '--info';

/** @var int|null $groupId */
$groupId = null;
/** @var string|null $username */
$username = null;
/** @var int|null $timestamp */
$timestamp = null;
if (isset($argv[1])) {
    if ($argv[1] === '--help') {
        echo <<<'TXT'
Usage: php parseGroupMessages.php [groupId|deepLink] [username] [timestamp] [--info]
    deepLink ex.: https://t.me/vityapelevin
TXT;

        die();
    } elseif ($argv[1] !== INFO) {
        if (is_numeric($argv[1])) {
            $groupId = (int) $argv[1];
        } else {
            $deepLink = $argv[1];
        }
    }

    if (isset($argv[2]) && $argv[2] !== INFO) {
        $username = $argv[2];
    }

    if (isset($argv[3]) && $argv[3] !== INFO) {
        $timestamp = (int) $argv[3];
    }
}
/** @noinspection PhpUnhandledExceptionInspection */
$client = new GroupMessagesScenario(
    null,
    $timestamp,
    $username
);
if ($groupId) {
    $client->setGroupId($groupId);
} elseif ($deepLink) {
    $client->setDeepLink($deepLink);
}

/* @noinspection PhpUnhandledExceptionInspection */
$client->startActions();
