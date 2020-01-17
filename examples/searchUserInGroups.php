<?php

/** @noinspection SpellCheckingInspection */

declare(strict_types=1);

use TelegramOSINT\Scenario\ReusableClientGenerator;
use TelegramOSINT\Scenario\SearchUserScenario;

require_once __DIR__.'/../vendor/autoload.php';

const INFO = '--info';

if (!isset($argv[1]) || isset($argv[1]) && $argv[1] === '--help' || !isset($argv[2])) {
    echo <<<'TXT'
Usage: php searchUserInGroups.php grouplist.txt username [--info]
TXT;
    die();
}

if (!file_exists($argv[1])) {
    fwrite(STDERR, "File {$argv[1]} not found\n");
    exit(1);
}

$contents = file_get_contents($argv[1]);
$lines = explode("\n", $contents);
$groupnames = [];
foreach ($lines as $line) {
    if (!$line) {
        continue;
    }
    $groupnames[] = trim($line);
}

$username = $argv[2];

$generator = new ReusableClientGenerator();
$scenario = new SearchUserScenario($generator, $groupnames, $username);
/** @noinspection PhpUnhandledExceptionInspection */
$scenario->startActions();
