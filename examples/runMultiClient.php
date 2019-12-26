<?php

use Exception\TGException;

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/MultiClient.php';

$keysFileName = isset($argv[1]) ? $argv[1] : 'keys.txt';
$keysStr = file_get_contents($keysFileName);
$lines = explode("\n", $keysStr);
foreach ($lines as $k => &$line) {
    $line = trim($line);
    if (!$line) {
        unset($lines[$k]);
    }
}

try {
    $mc = new MultiClient($lines);
    $mc->connect();
} catch (TGException $e) {
    die($e->getMessage());
}

while(true) {
    $mc->poll();
}
