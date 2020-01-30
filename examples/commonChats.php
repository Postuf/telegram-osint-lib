<?php

declare(strict_types=1);

use TelegramOSINT\Scenario\CommonChatsScenario;
use TelegramOSINT\Scenario\ReusableClientGenerator;

require_once __DIR__.'/../vendor/autoload.php';

if (!isset($argv[1])){
    echo "please specify number: 79001234567\n";
    exit(1);
}

$phone = $argv[1];

$groups = [
    'rhymestg',
    'Muzik',
    'muzyka_muzika',
    'ateo_chat',
];

$generator = new ReusableClientGenerator();
$scenario = new CommonChatsScenario(
    $generator,
    $groups,
    $phone
);
$scenario->startActions();