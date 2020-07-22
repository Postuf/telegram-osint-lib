<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

interface TimeoutScenarioInterface
{
    public function setTimeout(float $timeout): void;
}
