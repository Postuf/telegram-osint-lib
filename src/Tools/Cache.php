<?php

declare(strict_types=1);

namespace TelegramOSINT\Tools;

interface Cache
{
    public function set(string $key, $value): void;

    public function get(string $key);

    public function del(): void;
}
