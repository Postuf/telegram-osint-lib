<?php

declare(strict_types=1);

namespace TelegramOSINT\Tools;

use TelegramOSINT\Exception\TGException;

interface CacheInvalidator
{
    public function invalidateIfNeeded(TGException $e, Cache $cache): void;
}
