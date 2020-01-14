<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use Scenario\GroupMembersScenario;

$groupId = null;
if (isset($argv[1])) {
    if ($argv[1] === '--help') {
        echo <<<'TXT'
Usage: php parseGroupMembers.php [groupId|deepLink] [--info]
    deepLink ex.: https://t.me/vityapelevin
TXT;

        die();
    } elseif ($argv[1] !== '--info') {
        if (is_numeric($argv[1])) {
            $groupId = (int) $argv[1];
        } else {
            $deepLink = $argv[1];
        }
    }
}
$client = new GroupMembersScenario();
if ($groupId) {
    $client->setGroupId($groupId);
} elseif ($deepLink) {
    $client->setDeepLink($deepLink);
}
/* @noinspection PhpUnhandledExceptionInspection */
$client->startActions();
