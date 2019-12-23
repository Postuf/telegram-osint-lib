<?php

use Exception\TGException;
use MTSerialization\OwnImplementation\OwnDeserializer;

require_once __DIR__ . '/../../ClassLoader.php';

$exact = false;
$data = "1ce55564debee6310300000094c911f9d92d300b379779bc3d0f4b6b";
if($argc == 2)
    $data = $argv[1];
$data = hex2bin($data);

/**
 * @param string $binary
 * @throws TGException
 */
function printDeserialized($binary) {
    $serializer = new OwnDeserializer();
    $deserialized = $serializer->deserialize($binary);
    print_r($deserialized);
}

if(!$exact) {
    for ($i = 0; $i < strlen($data); $i++) {
        try {

            printDeserialized(substr($data, $i));
            break;

        } catch (TGException $e) {

            echo $e->getMessage() . PHP_EOL;

            if ($e->getCode() == TGException::ERR_DESERIALIZER_NOT_TOTAL_READ) {
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