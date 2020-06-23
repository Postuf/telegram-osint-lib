<?php

declare(strict_types=1);

namespace TelegramOSINT\Tools;

class DefaultClock implements Clock
{
    public function time(): int
    {
        return time();
    }

    public function microtime(bool $returnAsFloat = true): float
    {
        return microtime(true);
    }

    public function usleep(int $ms): void
    {
        usleep($ms);
    }
}
