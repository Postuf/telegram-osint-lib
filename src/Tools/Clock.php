<?php

declare(strict_types=1);

namespace TelegramOSINT\Tools;

/**
 * Абстракция часов для удобства тестирования
 */
interface Clock
{
    /**
     * Вернуть UNIX timestamp
     *
     * @return int
     */
    public function time(): int;

    /**
     * Вернуть UNIX timestamp с микросекундами
     *
     * @param bool $returnAsFloat
     *
     * @return float
     */
    public function microtime(bool $returnAsFloat = true): float;

    public function usleep(int $ms): void;
}
