<?php

namespace TelegramOSINT\Logger;

use TelegramOSINT\LibConfig;

class Logger
{
    /**
     * @var ClientDebugLogger
     */
    private static $logger;

    private const DEBUG_LABELS = [
        'Read_Message_Binary',
        'Read_Message_TL',
        'Write_Message_Binary',
        'Write_Message_TL',
        'Write_Message_ID',
    ];

    /**
     * @param string $label
     * @param string $log
     */
    public static function log($label, $log): void
    {
        global $argv;
        if (LibConfig::LOGGER_ENABLED) {
            $hasDebugFlag = isset($argv[count($argv) - 1]) && $argv[count($argv) - 1] === '--debug';
            $hasInfoFlag = isset($argv[count($argv) - 1]) && $argv[count($argv) - 1] === '--info';
            $isDebug = (LibConfig::LOG_LEVEL === 'debug' || $hasDebugFlag) && !$hasInfoFlag;
            if (!$isDebug && in_array($label, self::DEBUG_LABELS, true)) {
                return;
            }
            self::getLogger()->debugLibLog($label, $log);
        }
    }

    private static function getLogger(): ClientDebugLogger
    {
        if (!(self::$logger instanceof ClientDebugLogger)) {
            self::setupLogger(new DefaultLogger());
        }

        return  self::$logger;
    }

    /**
     * @param ClientDebugLogger $logger
     */
    public static function setupLogger(ClientDebugLogger $logger): void
    {
        self::$logger = $logger;
    }
}
