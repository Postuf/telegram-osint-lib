<?php

declare(strict_types=1);

use Helpers\MonitorNumbersToFile;
use TelegramOSINT\LibConfig;
use TelegramOSINT\Scenario\ClientGenerator;
use TelegramOSINT\Scenario\PresenceMonitoringScenario\PresenceMonitoringScenario;

require_once __DIR__.'/../vendor/autoload.php';

if ($argc != 3) {
    echo 'Usage: php monitorNumbers.php [numbers_comma_separated] [file_to_save]';
    exit(1);
}

$numbers = explode(',', $argv[1]);
$file = $argv[2];

$generator = new ClientGenerator(LibConfig::ENV_AUTHKEY);
$monitor = new MonitorNumbersToFile($file);
$scenario = new PresenceMonitoringScenario($numbers, $monitor, $generator);
$scenario->startActions(false);

while(true) {
    $scenario->poll();
    $monitor->tick();
    sleep(1);
}
