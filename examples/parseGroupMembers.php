<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use TelegramOSINT\Scenario\GroupMembersScenario;

$argsOrFalse = getopt('g:l:u:h', ['group-id:', 'deep-link:', 'username:', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
) {
    echo <<<'EOT'
Usage:
    php parseGroupMembers.php [-g groupID | -l link]
    php parseGroupMembers.php [--group-id groupID | --deep-link link]

    Note: Group ID and Deep Link are mutually exclusive. Please specify only one of them.

   -g, --group-id               Optional group identifier.
   -u, --username               Search by username.
   -l, --deep-link              Optional deep link (e.g. https://t.me/vityapelevin).
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$groupId = $argsOrFalse['g'] ?? $argsOrFalse['group-id'] ?? null;
$deepLink = $argsOrFalse['l'] ?? $argsOrFalse['deep-link'] ?? '';
$username = $argsOrFalse['u'] ?? $argsOrFalse['username'] ?? null;
if ($username) {
    echo "searching usernames like: $username".PHP_EOL;
}
if ($groupId !== null && $deepLink !== '') {
    echo 'Group ID and Deep Link are mutually exclusive. Please specify only one of them.'.PHP_EOL;

    exit(1);
}

/** @noinspection PhpUnhandledExceptionInspection */
$client = new GroupMembersScenario(
    null,
    null,
    null,
    100,
    $username
);
if ($groupId !== null) {
    $client->setGroupId((int) $groupId);
} elseif ($deepLink !== '') {
    $client->setDeepLink($deepLink);
}
/* @noinspection PhpUnhandledExceptionInspection */
$client->startActions();
