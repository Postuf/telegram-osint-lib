<?php

declare(strict_types=1);

use TelegramOSINT\Scenario\CommonChatsScenario;

require_once __DIR__.'/../vendor/autoload.php';

if (!isset($argv[1])){
    echo "please specify number: 79001234567\n";
    exit(1);
}

$phone = $argv[1];

$groups = [
    '@muzyka_muzika',
    '@Muzik',
    '@rhymestg'
];

$client = new CommonChatsScenario();
$client->login();
$client->getCommonChats($phone, $groups, function(){

});
$client->pollAndTerminate();