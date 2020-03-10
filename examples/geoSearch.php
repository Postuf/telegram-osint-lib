<?php

declare(strict_types=1);

use TelegramOSINT\Client\InfoObtainingClient\Models\GeoChannelModel;
use TelegramOSINT\Scenario\GeoSearchScenario;
use TelegramOSINT\Scenario\GroupMembersScenario;
use TelegramOSINT\Scenario\ReusableClientGenerator;

require_once __DIR__.'/../vendor/autoload.php';

$argsOrFalse = getopt('c:u:l:gh', ['coordinates:', 'user:', 'limit:', 'groups-only', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
    || (!array_key_exists('c', $argsOrFalse) && !array_key_exists('coordinates', $argsOrFalse))
) {
    echo <<<'EOT'
Usage:
    php geoSearch.php -c lat1,lon1,lat2,lon2 [-u username] [-l limit] [-g]
    php geoSearch.php --coordinates lat1,lon1,lat2,lon2 [--user username] [--limit limit] [--groups-only]

   -c, --coordinates            Comma separated list of latitudes and longitudes.
   -u, --user                   Optional user name. If specified it searches the user in selected groups,
                                otherwise prints group list only.
   -l, --limit                  Optional limit (default 100).
   -g, --groups-only            Optional groups only flag.
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$coordinates = $argsOrFalse['c'] ?? $argsOrFalse['coordinates'];
// explode => convert each value to float => chunk into pairs
$points = array_chunk(array_map(function (string $value): float {
    return (float) $value;
}, explode(',', $coordinates)), 2);

$username = $argsOrFalse['u'] ?? $argsOrFalse['user'] ?? null;
$groupsOnly = array_key_exists('g', $argsOrFalse) || array_key_exists('groups-only', $argsOrFalse);
$limit = (int) ($argsOrFalse['l'] ?? $argsOrFalse['limit'] ?? 100);

$generator = new ReusableClientGenerator();

$finders = [];
$groupHandler = function (GeoChannelModel $model) use (&$generator, &$finders, $username, $limit) {
    $membersFinder = new GroupMembersScenario(
        $model->getGroupId(),
        null,
        $generator,
        $limit,
        $username
    );

    $membersFinder->startActions(false);
    $finders[] = $membersFinder;
};

/* @noinspection PhpUnhandledExceptionInspection */
$search = new GeoSearchScenario($points, $groupsOnly ? null : $groupHandler, $generator, $limit);
/* @noinspection PhpUnhandledExceptionInspection */
$search->startActions();
