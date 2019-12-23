<?php

namespace Logger;

use LibConfig;

class Logger
{

    /**
     * @var ClientDebugLogger
     */
    private static $logger;


    /**
     * @param string $label
     * @param string $log
     */
    public static function log($label, $log)
    {
        if(LibConfig::LOGGER_ENABLED)
            self::getLogger()->debugLibLog($label, $log);
    }


    /**
     * @return ClientDebugLogger
     */
    private static function getLogger()
    {
        if(!(self::$logger instanceof ClientDebugLogger))
            self::setupLogger(new DefaultLogger());

        return  self::$logger;
    }


    /**
     * @param ClientDebugLogger $logger
     */
    public static function setupLogger(ClientDebugLogger $logger)
    {
        self::$logger = $logger;
    }

}