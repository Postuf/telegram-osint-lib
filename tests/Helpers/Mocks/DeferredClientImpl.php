<?php

declare(strict_types=1);

namespace Helpers\Mocks;

use TelegramOSINT\Client\DeferredClient;

class DeferredClientImpl extends DeferredClient
{
    public function processDeferredQueue(): void
    {
        parent::processDeferredQueue();
    }

    public function hasDeferredCalls(): bool
    {
        return parent::hasDeferredCalls();
    }

    public function defer(callable $cb, int $timeOffset = 0): void
    {
        parent::defer($cb, $timeOffset);
    }
}
