<?php

use Client\AuthKey\AuthKey;
use Registration\RegistrationFromApi;

require_once __DIR__ . '/../../ClassLoader.php';

echo "Номер: ";
$phone = fgets(STDIN);

$reg = new RegistrationFromApi();
/** @noinspection PhpUnhandledExceptionInspection */
$reg->requestCodeForPhone($phone, function() use($reg) {
    echo "SMS code: ";
    $code = fgets(STDIN);

    $reg->confirmPhoneWithSmsCode($code, function (AuthKey $authKey) {
        echo "AuthKey: ".$authKey->getSerializedAuthKey()."\n";
    });
});