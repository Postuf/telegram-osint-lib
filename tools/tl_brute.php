#!/usr/bin/php

<?php

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\OwnImplementation\OwnDeserializer;

require_once __DIR__.'/../vendor/autoload.php';

$exact = false;
if($argc < 2){
    // strange concat to prevent html annotations errors
    $attrs = '1|0';
    $lbr = '<';
    $rbr = '>';
    die('Usage: ./tl_brute.php '.$lbr.'tl_hex_node'.$rbr.' <exact: '.$attrs.'>'.PHP_EOL);
}
if($argc >= 2) {
    $data = hex2bin($argv[1]);
}
if($argc >= 3){
    $exact = $argv[2] === '1';
}

/**
 * @param $binary
 *
 * @throws TGException
 */
function printDeserialized($binary) {
    $serializer = new OwnDeserializer();
    $deserialized = $serializer->deserialize($binary);
    /** @noinspection ForgottenDebugOutputInspection */
    print_r($deserialized);
}

if(!$exact) {
    for ($i = 0, $iMax = strlen($data); $i < $iMax; $i++) {
        try {

            printDeserialized(substr($data, $i));
            break;

        } catch (TGException $e) {

            echo $e->getMessage().PHP_EOL;

            if ($e->getCode() === TGException::ERR_DESERIALIZER_NOT_TOTAL_READ) {
                preg_match('/([0-9a-zA-Z]+)$/', $e->getMessage(), $matches);
                $new = str_replace(hex2bin($matches[1]), '', substr($data, $i));
                /** @noinspection PhpUnhandledExceptionInspection */
                printDeserialized($new);
                break;
            }

            continue;
        }
    }
} else {
    /** @noinspection PhpUnhandledExceptionInspection */
    printDeserialized($data);
}
