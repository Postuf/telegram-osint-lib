<?php

declare(strict_types=1);

use TelegramOSINT\MTSerialization\OwnImplementation\OwnDeserializer;

require_once __DIR__.'/../vendor/autoload.php';

// decode hex-encoded message in TL format
// (useful for debugging)

if (!isset($argv[1]) || $argv[1] === '--help') {
    echo <<<'MSG'
Usage: php decodeMessage.php 0a0b0c0d
    0a0b0c0d - your message in bin2hex format.

MSG;
    die();
}

$deserializer = new OwnDeserializer();
/** @noinspection PhpUnhandledExceptionInspection */
$unserialized = $deserializer->deserialize(hex2bin($argv[1]));
print_r($unserialized);
