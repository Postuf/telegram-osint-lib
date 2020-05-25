<?php

declare(strict_types=1);

use Helpers\MonitorNumbersToFile;
use TelegramOSINT\LibConfig;
use TelegramOSINT\Scenario\ClientGenerator;
use TelegramOSINT\Scenario\PresenceMonitoringScenario\PresenceMonitoringScenario;
use TelegramOSINT\Tools\Proxy;

require_once __DIR__.'/../vendor/autoload.php';

$argsOrFalse = getopt('n:f:p:h', ['numbers:', 'output-file:', 'proxy:', 'help']);
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
   -p, --proxy                  Proxy to use.
   -h, --help                   Display this help message.

EOT;
    exit(1);
}

$numbers = explode(',', $argsOrFalse['n'] ?? $argsOrFalse['numbers']);
$count = count($numbers);
echo "$count numbers to monitor".PHP_EOL;
$file = $argsOrFalse['f'] ?? $argsOrFalse['output-file'];
$proxyStr = $argsOrFalse['p'] ?? $argsOrFalse['proxy'] ?? null;
if ($proxyStr) {
    $proxyStr = trim($proxyStr, "'");
}

/** @noinspection PhpUnhandledExceptionInspection */
$generator = new ClientGenerator(LibConfig::ENV_AUTHKEY, $proxyStr ? new Proxy($proxyStr) : null);
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
