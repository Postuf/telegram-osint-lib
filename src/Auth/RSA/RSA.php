<?php

declare(strict_types=1);

namespace TelegramOSINT\Auth\RSA;

interface RSA
{
    public function encrypt(string $data, string $key): string;
}
