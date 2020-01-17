<?php

declare(strict_types=1);

use TelegramOSINT\Client\MultiClient;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\Tools\Proxy;

require_once __DIR__.'/../vendor/autoload.php';

// multiple clients scenario
// proxy is highly recommended for 3+ clients

if (isset($argv[1]) && $argv[1] === '--help') {
    $msg = <<<'MSG'
Usage: php runMultiClient.php [keys.txt] [proxy] [--info]

MSG;

    die($msg);
}

$keysFileName = isset($argv[1]) && strpos($argv[1], '--') !== 0 ? $argv[1] : './keys.txt';
/** @var string|null $proxyStr */
$proxyStr = isset($argv[2]) && strpos($argv[2], '--') !== 0
    ? $argv[2] : null;
$limit = isset($argv[3]) && strpos($argv[3], '--') !== 0
    ? ((int) $argv[3]) : 1000;
$keysStr = file_get_contents($keysFileName);
$lines = explode("\n", $keysStr);
$lines = array_slice($lines, 0, $limit);
foreach ($lines as $k => &$line) {
    $line = trim($line);
    if (!$line) {
        unset($lines[$k]);
    }
}

$logLabel = 'multiClient';

$proxy = null;
if ($proxyStr) {
    try {
        $proxy = new Proxy($proxyStr);
    } catch (TGException $e) {
        Logger::log($logLabel, 'proxy: '.$e->getMessage());
        exit(1);
    }
}

try {
    $mc = new MultiClient($lines);
    $mc->connect($proxy);
} catch (TGException $e) {
    Logger::log($logLabel, $e->getMessage());
    exit(1);
}

$errorCount = 0;
$errorLimit = 100;
while(true) {
    try {
        $mc->poll();
        usleep(1000);
    } catch (TGException $e) {
        Logger::log($logLabel, 'poll: '.$e->getMessage());
        $errorCount++;
        if ($errorCount >= $errorLimit) {
            Logger::log($logLabel, 'too many errors');
            exit(1);
        }
    }
}
