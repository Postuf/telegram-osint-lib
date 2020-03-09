<?php

declare(strict_types=1);

use Helpers\MonitorNumbersToFile;
use TelegramOSINT\Scenario\ClientGenerator;
use TelegramOSINT\Scenario\PresenceMonitoringScenario\PresenceMonitoringScenario;

require_once __DIR__.'/../vendor/autoload.php';

$argsOrFalse = getopt('n:f:h', ['numbers:', 'output-file:', 'help']);
if ($argsOrFalse === false
    || (array_key_exists('h', $argsOrFalse) || array_key_exists('help', $argsOrFalse))
    || (!array_key_exists('n', $argsOrFalse) && !array_key_exists('numbers', $argsOrFalse))
    || (!array_key_exists('f', $argsOrFalse) && !array_key_exists('output-file', $argsOrFalse))
) {
    echo <<<'EOT'
Usage:
    php monitorNumbers.php -n numbers -f output-file
    php monitorNumbers.php --numbers numbers --output-file output-file

   -n, --numbers                Comma separated phone number list (e.g. 79061231231,79061231232).
   -f, --output-file            Output file to save the result.
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$numbers = explode(',', $argsOrFalse['n'] ?? $argsOrFalse['numbers']);
$file = $argsOrFalse['f'] ?? $argsOrFalse['output-file'];

$generator = new ClientGenerator();
$monitor = new MonitorNumbersToFile($file);
/** @noinspection PhpUnhandledExceptionInspection */
$scenario = new PresenceMonitoringScenario($numbers, $monitor, $generator);
/** @noinspection PhpUnhandledExceptionInspection */
$scenario->startActions(false);

while(true) {
    /** @noinspection PhpUnhandledExceptionInspection */
    $scenario->poll();
    $monitor->tick();
    sleep(1);
}
