<?php

declare(strict_types=1);

namespace Helpers\Mocks;

use TelegramOSINT\Tools\Clock;

class ControllableClock implements Clock
{
    public const SECONDS_MS = 1000000;

    /** @var int */
    private $currentTime;
    /** @var int microseconds */
    private $currentAdvanceMs = 0;

    public function __construct()
    {
        $this->currentTime = time();
    }

    /**
     * @noinspection PhpUnused
     * @noinspection UnknownInspectionInspection
     *
     * @param int $time
     */
    public function setTime(int $time): void
    {
        $this->currentTime = $time;
    }

    public function time(): int
    {
        return $this->currentTime;
    }

    /**
     * @param int $ms microseconds
     */
    public function usleep(int $ms): void
    {
        $this->currentAdvanceMs += $ms;
        $million = 1000000;
        if ($this->currentAdvanceMs >= $million) {
            $sec = intdiv($this->currentAdvanceMs, $million);
            $this->currentAdvanceMs %= $million;
            $this->currentTime += $sec;
        }
    }

    public function microtime(bool $returnAsFloat = true): float
    {
        return $this->currentTime * 1.0;
    }
}
