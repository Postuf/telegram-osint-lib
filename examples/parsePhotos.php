<?php

declare(strict_types=1);

use Helpers\DateParser;
use TelegramOSINT\Scenario\GroupPhotosScenario;
use TelegramOSINT\Scenario\Models\OptionalDateRange;

require_once __DIR__.'/../vendor/autoload.php';

$argsOrFalse = getopt('g:l:f:t:u:h', ['group-id:', 'deep-link:', 'date-from:', 'date-to:', 'user:', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
) {
    /** @noinspection SpellCheckingInspection */
    echo <<<'EOT'
Usage:
    php parsePhotos.php [-g groupID | -l link] [-f dateFrom] [-t dateTo] [-u username]
    php parsePhotos.php [--group-id groupID | --deep-link link]
                        [--date-from dateFrom] [--date-to dateTo] [--user username]

    Note: Group ID and Deep Link are mutually exclusive. Please specify only one of them.

   -g, --group-id               Optional group identifier.
   -l, --deep-link              Optional deep link (e.g. https://t.me/vityapelevin).
   -f, --date-from              Optional start date in `YYYYMMdd[ H:i:s]` or `YY-mm-dd H:i:s` format.
   -t, --date-to                Optional end date in `YYYYMMdd[ H:i:s]` or `YY-mm-dd H:i:s` format.
   -u, --user                   Optional user name.
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

$since = $argsOrFalse['f'] ?? $argsOrFalse['date-from'] ?? null;
$to = $argsOrFalse['t'] ?? $argsOrFalse['date-to'] ?? null;
$username = $argsOrFalse['u'] ?? $argsOrFalse['user'] ?? null;

$photosClient = new GroupPhotosScenario(
    new OptionalDateRange(
        DateParser::parse($since),
        DateParser::parse($to)
    ),
    $username
);
if ($groupId !== null) {
    $photosClient->setGroupId((int) $groupId);
} elseif ($deepLink !== '') {
    $photosClient->setDeepLink($deepLink);
}
/* @noinspection PhpUnhandledExceptionInspection */
$photosClient->startActions();
