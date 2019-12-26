<?php

use Client\AuthKey\AuthKey;
use Registration\RegistrationFromTgApp;

require_once __DIR__.'/../../ClassLoader.php';

echo 'Номер: ';
$phone = fgets(STDIN);

$reg = new RegistrationFromTgApp();
/* @noinspection PhpUnhandledExceptionInspection */
$reg->requestCodeForPhone($phone, function () use ($reg) {
    echo 'SMS код: ';
    $code = fgets(STDIN);

    $reg->confirmPhoneWithSmsCode($code, function (AuthKey $authKey) {
        echo 'AuthKey: '.$authKey->getSerializedAuthKey()."\n";
    });
});
