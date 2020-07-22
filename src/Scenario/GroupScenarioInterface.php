<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario;

interface GroupScenarioInterface
{
    public function setGroupId(int $groupId): void;

    public function setDeepLink(string $deepLink): void;
}
