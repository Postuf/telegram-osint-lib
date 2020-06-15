<?php

declare(strict_types=1);

use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Registration\AccountRegistrar;

require_once __DIR__.'/../vendor/autoload.php';

echo 'Number: ';
$phone = fgets(STDIN);

// Only Europe/CIS IP/proxy allowed, Telegram DC requires that.
// $proxy = new Proxy(file_get_contents(__DIR__.'/reg_proxy.txt'));

$reg = new AccountRegistrar(/* $proxy */);
/* @noinspection PhpUnhandledExceptionInspection */
$reg->requestCodeForPhone($phone, static function (bool $reReg) use ($reg) {
    echo 'SMS code: ';
    $code = fgets(STDIN);

    $reg->confirmPhoneWithSmsCode($code, static function (AuthKey $authKey) {
        echo 'AuthKey: '.$authKey->getSerializedAuthKey().PHP_EOL;
        die();
    }, $reReg);
}, true);
$reg->pollMessages();
