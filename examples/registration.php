<?php

use Registration\AccountRegistrar;
use Tools\Proxy;

require_once __DIR__ . '/../ClassLoader.php';

echo "Number: ";
$phone = fgets(STDIN);

// Only Europe/CIS IP/proxy allowed, Telegram DC requires that.
/** @noinspection PhpUnhandledExceptionInspection */
// $proxy = new Proxy(file_get_contents(__DIR__.'/reg_proxy.txt'));

$reg = new AccountRegistrar(/* $proxy */);
/** @noinspection PhpUnhandledExceptionInspection */
$reg->requestCodeForPhone($phone);

echo "SMS code: ";
$code = fgets(STDIN);

/** @noinspection PhpUnhandledExceptionInspection */
$authKey = $reg->confirmPhoneWithSmsCode($code);
echo "AuthKey: ".$authKey->getSerializedAuthKey()."\n";
