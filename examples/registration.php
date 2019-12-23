<?php

use Registration\AccountRegistrar;
use Tools\Proxy;

require_once __DIR__ . '/../ClassLoader.php';

echo "Номер: ";
$phone = fgets(STDIN);

// разрешено использовать только европейские прокси и
// прокси стран ближнего СНГ - это обусловлено спецификой взаимодействия с датацентрами Telegram
/** @noinspection PhpUnhandledExceptionInspection */
$proxy = new Proxy(file_get_contents(__DIR__.'/reg_proxy.txt'));

$reg = new AccountRegistrar();
/** @noinspection PhpUnhandledExceptionInspection */
$reg->requestCodeForPhone($phone);

echo "SMS код: ";
$code = fgets(STDIN);

/** @noinspection PhpUnhandledExceptionInspection */
$authKey = $reg->confirmPhoneWithSmsCode($code);
echo "AuthKey: ".$authKey->getSerializedAuthKey()."\n";
