<?php

use Client\AuthKey\AuthKey;
use Registration\AccountRegistrar;

require_once __DIR__ . '/../vendor/autoload.php';

echo "Number: ";
$phone = fgets(STDIN);

// Only Europe/CIS IP/proxy allowed, Telegram DC requires that.
// $proxy = new Proxy(file_get_contents(__DIR__.'/reg_proxy.txt'));

$reg = new AccountRegistrar(/* $proxy */);
/** @noinspection PhpUnhandledExceptionInspection */
$reg->requestCodeForPhone($phone, function () use($reg) {
    echo "SMS code: ";
    $code = fgets(STDIN);

    $reg->confirmPhoneWithSmsCode($code, function (AuthKey $authKey) {
        echo "AuthKey: ".$authKey->getSerializedAuthKey()."\n";
        die();
    });
});
$reg->pollMessages();
