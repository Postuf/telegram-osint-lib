<?php

declare(strict_types=1);

namespace TelegramOSINT\Client;

interface CachingClient
{
    public function warmup(): void;
}
