<?php

declare(strict_types=1);

namespace Helpers;

use TelegramOSINT\Scenario\PresenceMonitoringScenario\PresenceMonitoringCallbacks;

class MonitorNumbersToFile implements PresenceMonitoringCallbacks
{
    private const STATUS_ONLINE = 1;
    private const STATUS_OFFLINE = 0;

    /**
     * @var string[]
     * */
    private $lines = [];
    /**
     * @var int[]
     * */
    private $statuses = [];
    /**
     * @var string
     * */
    private $fileName;

    public function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    public function onOnline(string $number): void
    {
        $this->setupNumber($number, self::STATUS_ONLINE);
    }

    public function onOffline(string $number, int $wasOnlineTimestamp): void
    {
        $this->setupNumber($number, self::STATUS_OFFLINE);
    }

    public function onHidden(string $number): void
    {
        $this->setupNumber($number, self::STATUS_OFFLINE);
    }

    public function tick(): void
    {
        foreach ($this->statuses as $number => $status) {
            switch ($status) {
                case self::STATUS_OFFLINE:
                    $this->lines[$number] .= '.';
                    break;
                case self::STATUS_ONLINE:
                    $this->lines[$number] .= '+';
                    break;
            }
        }

        file_put_contents($this->fileName, implode(PHP_EOL, $this->lines));
    }

    private function setupNumber(string $number, int $status): void
    {
        if (!isset($this->lines[$number])) {
            $this->lines[$number] = str_pad($number, 15).': ';
        }
        $this->statuses[$number] = $status;
    }
}
