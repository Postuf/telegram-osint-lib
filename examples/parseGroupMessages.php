<?php

declare(strict_types=1);

require_once __DIR__.'/../vendor/autoload.php';

use TelegramOSINT\Logger\Logger;
use TelegramOSINT\Scenario\GroupMessagesScenario;
use TelegramOSINT\Scenario\GroupResolverScenario;
use TelegramOSINT\Scenario\Models\GroupId;
use TelegramOSINT\Scenario\Models\GroupRequest;
use TelegramOSINT\Scenario\Models\OptionalDateRange;
use TelegramOSINT\Scenario\ReusableClientGenerator;

$argsOrFalse = getopt('g:l:f:t:u:h', ['group-id:', 'deep-link:', 'timestamp-from:', 'timestamp-to:', 'user:', 'help']);
$hasGroupId = array_key_exists('g', $argsOrFalse) || array_key_exists('group-id', $argsOrFalse);
$hasLink = array_key_exists('l', $argsOrFalse) || array_key_exists('deep-link', $argsOrFalse);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
    || (!$hasGroupId && !$hasLink)
) {
    echo <<<'EOT'
Usage:
    php parseGroupMessages.php -g groupID | -l link
                               [-f timestampFrom] [-t timestampTo] [-u username]
    php parseGroupMessages.php --group-id groupID | --deep-link link
                               [--timestamp-from timestampFrom] [--timestamp-to timestampTo] [--user username]

    Note: Group ID and Deep Link are mutually exclusive. Please specify only one of them.

   -g, --group-id               Group identifier.
   -l, --deep-link              Deep link (e.g. https://t.me/vityapelevin).
   -f, --timestamp-from         Optional start timestamp.
   -t, --timestamp-to           Optional end timestamp.
   -u, --user                   Optional user name.
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

if ($hasGroupId && $hasLink) {
    echo 'Group ID and Deep Link are mutually exclusive. Please specify only one of them.'.PHP_EOL;

    exit(1);
}

$groupId = $argsOrFalse['g'] ?? $argsOrFalse['group-id'] ?? null;
$deepLink = $argsOrFalse['l'] ?? $argsOrFalse['deep-link'] ?? '';
$username = $argsOrFalse['u'] ?? $argsOrFalse['user'] ?? null;
$timestampStart = $argsOrFalse['f'] ?? $argsOrFalse['timestamp-from'] ?? null;
$timestampEnd = $argsOrFalse['t'] ?? $argsOrFalse['timestamp-to'] ?? null;

$timestampStart = $timestampStart === null ? null : (int) $timestampStart;
$timestampEnd = $timestampEnd === null ? null : (int) $timestampEnd;

$generator = new ReusableClientGenerator();
$request = $groupId !== null
    ? GroupRequest::ofGroupId((int) $groupId)
    : GroupRequest::ofUserName($deepLink);

/**
 * @param int|null $groupId
 * @param int|null $accessHash
 */
$onGroupReady = function (
    ?int $groupId,
    ?int $accessHash = null
) use ($timestampStart, $timestampEnd, $username, $generator): void {
    if (!$groupId || !$accessHash) {
        Logger::log('parseGroupMessages', 'Group not found');

        return;
    }

    /** @var int $accessHash */
    $client = new GroupMessagesScenario(
        new GroupId($groupId, $accessHash),
        $generator,
        new OptionalDateRange(
            $timestampStart,
            $timestampEnd,
        ),
        null,
        $username,
        100,
        true
    );

    $client->startActions(false);
};

Logger::log(__FILE__, 'starting group resolver for '.$request);
/** @noinspection PhpUnhandledExceptionInspection */
$resolver = new GroupResolverScenario($request, $generator, $onGroupReady);
/** @noinspection PhpUnhandledExceptionInspection */
$resolver->startActions();
