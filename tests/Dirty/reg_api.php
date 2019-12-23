<?php

use Registration\RegistrationFromApi;

require_once __DIR__ . '/../../ClassLoader.php';

echo "Номер: ";
$phone = fgets(STDIN);

$reg = new RegistrationFromApi();
/** @noinspection PhpUnhandledExceptionInspection */
$reg->requestCodeForPhone($phone);

echo "SMS код: ";
$code = fgets(STDIN);

/** @noinspection PhpUnhandledExceptionInspection */
$authKey = $reg->confirmPhoneWithSmsCode($code);
echo "AuthKey: ".$authKey->getSerializedAuthKey()."\n";
