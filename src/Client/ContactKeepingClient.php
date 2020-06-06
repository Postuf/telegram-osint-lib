<?php

declare(strict_types=1);

namespace TelegramOSINT\Client;

interface ContactKeepingClient
{
    public function addNumbers(array $numbers, callable $onComplete): void;

    public function delNumbers(array $numbers, callable $onComplete): void;
}
