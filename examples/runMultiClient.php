<?php

declare(strict_types=1);

use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Client\MultiClient;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Logger\Logger;
use TelegramOSINT\Scenario\BasicClientGenerator;
use TelegramOSINT\Tools\Proxy;

require_once __DIR__.'/../vendor/autoload.php';

// multiple clients scenario
// proxy is highly recommended for 3+ clients

$argsOrFalse = getopt('k:p:l:h', ['keys-file:', 'proxy:', 'limit:', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
) {
    echo <<<'EOT'
Usage:
    php runMultiClient.php [-k keys.txt] [-p proxy] [-l limit]
    php runMultiClient.php [--keys-file keys.txt] [--proxy proxy] [--limit limit]

   -k, --keys-file              Optional keys file (default `keys.txt`).
   -p, --proxy                  Optional proxy (e.g. `host:port` or `host:port|login:password`).
   -l, --limit                  Optional limit (default 1000).
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$keysFileName = $argsOrFalse['k'] ?? $argsOrFalse['keys-file'] ?? './keys.txt';
$proxyStr = $argsOrFalse['p'] ?? $argsOrFalse['proxy'] ?? null;
$limit = (int) ($argsOrFalse['l'] ?? $argsOrFalse['limit'] ?? 1000);

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
if ($proxyStr !== null) {
    try {
        $proxy = new Proxy((string) $proxyStr);
    } catch (TGException $e) {
        Logger::log($logLabel, 'proxy: '.$e->getMessage());
        exit(1);
    }
}

try {
    $clientCreator = function () {
        return new InfoClient(new BasicClientGenerator());
    };
    $mc = new MultiClient($lines, $clientCreator);
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
