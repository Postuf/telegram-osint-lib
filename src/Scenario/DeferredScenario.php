<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

abstract class DeferredScenario
{
    /** @var array */
    private $deferredQueue = [];

    protected function defer(callable $cb, int $timeOffset = 0): void
    {
        $this->deferredQueue[] = [time() + $timeOffset, $cb];
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
        $time = time();
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

    protected function hasDeferredCalls(): bool
    {
        return count($this->deferredQueue) > 0;
    }
}
