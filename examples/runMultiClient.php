<?php

use Exception\TGException;
use Logger\Logger;
use SocksProxyAsync\Proxy;
use SocksProxyAsync\SocksException;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/MultiClient.php';

$keysFileName = isset($argv[1]) ? $argv[1] : 'keys.txt';
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

$logLabel = "multiClient";

try {
    $proxy = $proxyStr
        ? new Proxy($proxyStr)
        : null;
} catch (SocksException $e) {
    Logger::log($logLabel, "proxy: " . $e->getMessage());
}
try {
    $mc = new MultiClient($lines);
    $mc->connect($proxy);
} catch (TGException $e) {
    die($e->getMessage()."\n");
}

$errorCount = 0;
$errorLimit = 100;
while(true) {
    try {
        $mc->poll();
    } catch (TGException $e) {
        Logger::log($logLabel, "poll: " . $e->getMessage());
        ++$errorCount;
        if ($errorCount >= $errorLimit) {
            die("too many errors\n");
        }
    }
}
