<?php

declare(strict_types=1);

namespace TelegramOSINT\Client;

use TelegramOSINT\Tools\Clock;
use TelegramOSINT\Tools\DefaultClock;

abstract class DeferredClient
{
    protected $clock;

    public function __construct(?Clock $clock = null)
    {
        if (!$clock) {
            $clock = new DefaultClock();
        }

        $this->clock = $clock;
    }

    /** @var array */
    private $deferredQueue = [];

    protected function defer(callable $cb, int $timeOffset = 0): void
    {
        $this->deferredQueue[] = [$this->clock->time() + $timeOffset, $cb];
        $this->sortDeferredQueue();
    }

    private function sortDeferredQueue(): void
    {
        usort($this->deferredQueue, static function (array $item1, array $item2) {
            return $item1[0] - $item2[0];
        });
    }

    protected function processDeferredQueue(): void
    {
        if (empty($this->deferredQueue)) {
            return;
        }

        $time = $this->clock->time();
        /** @noinspection LoopWhichDoesNotLoopInspection */
        foreach ($this->deferredQueue as $key => $item) {
            $timeStart = $item[0];
            if ($time >= $timeStart) {
                $cb = $item[1];
                unset($this->deferredQueue[$key]);
                $cb();
            }
            break;
        }
    }

    public function hasDeferredCalls(): bool
    {
        return count($this->deferredQueue) > 0;
    }
}
