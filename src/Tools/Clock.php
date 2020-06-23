<?php

declare(strict_types=1);

namespace TelegramOSINT\Tools;

/**
 * Clock abstraction
 */
interface Clock
{
    /**
     * Return UNIX timestamp
     *
     * @return int
     */
    public function time(): int;

    /**
     * Return UNIX timestamp with microseconds
     *
     * @param bool $returnAsFloat
     *
     * @return float
     */
    public function microtime(bool $returnAsFloat = true): float;

    public function usleep(int $ms): void;
}
