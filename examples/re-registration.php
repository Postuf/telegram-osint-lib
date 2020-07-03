<?php

declare(strict_types=1);

use TelegramOSINT\Client\AuthKey\AuthKey;
use TelegramOSINT\Exception\MigrateException;
use TelegramOSINT\Registration\AccountRegistrar;
use TelegramOSINT\TGConnection\DataCentre;

require_once __DIR__.'/../vendor/autoload.php';

echo 'Number: ';
$phone = fgets(STDIN);

$reg = new AccountRegistrar(
    null,
    null,
    null,
    DataCentre::getDefault()
);
$performReg = static function (AccountRegistrar $reg) use ($phone, &$performReg) {
    $reg->requestCodeForPhone($phone, static function (bool $reReg) use ($reg) {
        echo 'SMS code: ';
        $code = fgets(STDIN);

        $reg->confirmPhoneWithSmsCode($code, static function (AuthKey $authKey) {
            echo 'AuthKey: '.$authKey->getSerializedAuthKey().PHP_EOL;
            die();
        }, $reReg);
    }, true);

    try {
        $reg->pollMessages();
    } catch(MigrateException $e) {
        $reg->terminate();
        if ($e->getDc()) {
            echo "restarting with DC {$e->getDcId()}".PHP_EOL;
            $reg = new AccountRegistrar(
                null,
                null,
                null,
                $e->getDC()
            );
            $performReg($reg);
        } else {
            throw $e;
        }
    }
};
$performReg($reg);
