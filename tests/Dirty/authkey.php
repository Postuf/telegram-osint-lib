<?php

use Auth\Protocol\AppAuthorization;
use TGConnection\DataCentre;

require_once __DIR__ . '/../../ClassLoader.php';

/** @noinspection PhpUnhandledExceptionInspection */
$auth = new AppAuthorization(DataCentre::getDefault());
/** @noinspection PhpUnhandledExceptionInspection */
$key = $auth->createAuthKey();

echo "-----------------------------------------------------------\n";
echo "Authkey: " . bin2hex($key->getSerializedAuthKey())."\n";