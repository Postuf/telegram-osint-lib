<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use TelegramOSINT\Scenario\GroupMembersScenario;

$argsOrFalse = getopt('g:l:h', ['group-id:', 'deep-link:', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
) {
    echo <<<'EOT'
Usage:
    php parseGroupMembers.php [-g groupID | -l link]
    php parseGroupMembers.php [--group-id groupID | --deep-link link]

    Note: Group ID and Deep Link are mutually exclusive. Please specify only one of them.

   -g, --group-id               Optional group identifier.
   -l, --deep-link              Optional deep link (e.g. https://t.me/vityapelevin).
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$groupId = $argsOrFalse['g'] ?? $argsOrFalse['group-id'] ?? null;
$deepLink = $argsOrFalse['l'] ?? $argsOrFalse['deep-link'] ?? '';
if ($groupId !== null && $deepLink !== '') {
    echo 'Group ID and Deep Link are mutually exclusive. Please specify only one of them.'.PHP_EOL;

    exit(1);
}

$client = new GroupMembersScenario();
if ($groupId !== null) {
    $client->setGroupId((int) $groupId);
} elseif ($deepLink !== '') {
    $client->setDeepLink($deepLink);
}
/* @noinspection PhpUnhandledExceptionInspection */
$client->startActions();
