<?php

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

use TelegramOSINT\Scenario\ReusableClientGenerator;
use TelegramOSINT\Scenario\SearchUserScenario;

require_once __DIR__.'/../vendor/autoload.php';

$argsOrFalse = getopt('g:u:h', ['group-list-file:', 'user:', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
    || (!array_key_exists('g', $argsOrFalse) && !array_key_exists('group-list-file', $argsOrFalse))
    || (!array_key_exists('u', $argsOrFalse) && !array_key_exists('user', $argsOrFalse))
) {
    echo <<<'EOT'
Usage:
    php searchUserInGroups.php -g filename -u username
    php searchUserInGroups.php --group-list-file filename --user username

   -g, --group-list-file        Text input file with groups.
   -u, --user                   User name.
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$groupsFile = $argsOrFalse['g'] ?? $argsOrFalse['group-list-file'];
$username = $argsOrFalse['u'] ?? $argsOrFalse['user'];

if (!file_exists($groupsFile)) {
    fwrite(STDERR, "File $groupsFile not found.".PHP_EOL);
    exit(1);
}

$contents = file_get_contents($groupsFile);
$lines = explode("\n", $contents);
$groupnames = [];
foreach ($lines as $line) {
    if (!$line) {
        continue;
    }
    $groupnames[] = trim($line);
}

$generator = new ReusableClientGenerator();
/** @noinspection PhpUnhandledExceptionInspection */
$scenario = new SearchUserScenario($generator, $groupnames, $username);
/** @noinspection PhpUnhandledExceptionInspection */
$scenario->startActions();
