<?php

declare(strict_types=1);

use TelegramOSINT\MTSerialization\OwnImplementation\OwnDeserializer;

require_once __DIR__.'/../vendor/autoload.php';

// decode hex-encoded message in TL format
// (useful for debugging)

$argsOrFalse = getopt('m:h', ['message:', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
    || (!array_key_exists('m', $argsOrFalse) && !array_key_exists('message', $argsOrFalse))
) {
    echo <<<'EOT'
Usage:
    php decodeMessage.php -m message
    php decodeMessage.php --message message

   -m, --message                Message to decode in bin2hex format (e.g. 0a0b0c0d).
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$message = $argsOrFalse['m'] ?? $argsOrFalse['message'];

$deserializer = new OwnDeserializer();
/** @noinspection PhpUnhandledExceptionInspection */
$unserialized = $deserializer->deserialize(hex2bin($message));
print_r($unserialized);
