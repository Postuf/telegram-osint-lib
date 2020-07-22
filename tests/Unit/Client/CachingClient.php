<?php

declare(strict_types=1);

namespace Unit\Client;

interface CachingClient
{
    public function warmup(): void;
}
