<?php

declare(strict_types=1);

use Scenario\GroupPhotosClient;

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
    dateFrom/dateTo format: YYYYMMdd
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
$photosClient = new GroupPhotosClient($groupId, $since, $to);
if ($deepLink) {
    $photosClient->setDeepLink($deepLink);
}
/* @noinspection PhpUnhandledExceptionInspection */
$photosClient->startActions();
