<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

interface ScenarioInterface
{
    public function startActions(bool $pollAndTerminate = true): void;
}
