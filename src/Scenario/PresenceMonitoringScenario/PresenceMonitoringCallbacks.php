<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario\PresenceMonitoringScenario;

interface PresenceMonitoringCallbacks
{
    /**
     * @param string $number
     */
    public function onOnline(string $number): void;

    /**
     * @param string $number
     * @param int    $wasOnlineTimestamp
     */
    public function onOffline(string $number, int $wasOnlineTimestamp): void;

    /**
     * @param string $number
     */
    public function onHidden(string $number): void;
}
