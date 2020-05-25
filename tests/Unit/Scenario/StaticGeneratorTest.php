<?php

declare(strict_types=1);

namespace Unit\Scenario;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Client\InfoObtainingClient\InfoClient;
use TelegramOSINT\Scenario\StaticGenerator;

class StaticGeneratorTest extends TestCase
{
    public function test_construct_static_generator(): void
    {
        $sg = new StaticGenerator('some key');
        /** @noinspection UnnecessaryAssertionInspection */
        $this->assertInstanceOf(InfoClient::class, $sg->getInfoClient());
    }
}
