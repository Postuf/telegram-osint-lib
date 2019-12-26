<?php

use Auth\Protocol\AppAuthorization;
use Client\AuthKey\AuthKey;
use TGConnection\DataCentre;

require_once __DIR__.'/../../ClassLoader.php';

/** @noinspection PhpUnhandledExceptionInspection */
$auth = new AppAuthorization(DataCentre::getDefault());
/* @noinspection PhpUnhandledExceptionInspection */
$auth->createAuthKey(function (AuthKey $key) {
    echo "-----------------------------------------------------------\n";
    echo 'Authkey: '.bin2hex($key->getSerializedAuthKey())."\n";
});
