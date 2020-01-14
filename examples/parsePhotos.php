<?php

declare(strict_types=1);

use Helpers\DateParser;
use Scenario\GroupPhotosScenario;

require_once __DIR__.'/../vendor/autoload.php';
const INFO = '--info';

$groupId = null;
$deepLink = '';
$since = null;
$to = null;
if (isset($argv[1])) {
    if ($argv[1] === '--help') {
        /** @noinspection SpellCheckingInspection */
        echo <<<'TXT'
Usage: php parsePhotos.php [groupId|deepLink] [dateFrom] [dateTo] [--info]
    deepLink ex.: https://t.me/vityapelevin
    dateFrom/dateTo format: YYYYMMdd[ H:i:s]|YY-mm-dd H:i:s
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
        $since = $argv[2];
    }

    if (isset($argv[3]) && $argv[3] !== INFO) {
        $to = $argv[3];
    }
}

/* @noinspection PhpUnhandledExceptionInspection */
$photosClient = new GroupPhotosScenario(
    DateParser::parse($since),
    DateParser::parse($to)
);
if ($groupId) {
    $photosClient->setGroupId($groupId);
} elseif ($deepLink) {
    $photosClient->setDeepLink($deepLink);
}
/* @noinspection PhpUnhandledExceptionInspection */
$photosClient->startActions();
