<?php

declare(strict_types=1);

namespace TelegramOSINT\Tools;

use TelegramOSINT\Client\AuthKey\AuthKey;

interface CacheFactoryInterface
{
    public function generate(AuthKey $key): Cache;
}
