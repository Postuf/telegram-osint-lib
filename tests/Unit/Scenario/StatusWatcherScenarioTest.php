<?php

declare(strict_types=1);

namespace Unit\Scenario;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Scenario\ClientGeneratorInterface;
use TelegramOSINT\Scenario\StatusWatcherScenario;

class StatusWatcherScenarioTest extends TestCase
{
    public function test_construct(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $scenario = new StatusWatcherScenario([], [], $this->createMock(ClientGeneratorInterface::class));
        self::assertInstanceOf(StatusWatcherScenario::class, $scenario);
    }
}
