<?php

declare(strict_types=1);

namespace Unit\Client;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Client\BasicClient\BasicClient;
use TelegramOSINT\Client\BasicClient\BasicClientImpl;
use TelegramOSINT\Client\BasicClient\BasicClientWithStatusReportingImpl;

class BasicClientTest extends TestCase
{
    /**
     * Check basic client can be constructed
     */
    public function test_constructor(): void
    {
        $basicClient = new BasicClientImpl();
        $this->assertInstanceOf(BasicClient::class, $basicClient);
    }

    /**
     * Check basic client can be constructed
     */
    public function test_constructor_with_status(): void
    {
        $basicClient = new BasicClientWithStatusReportingImpl();
        $this->assertInstanceOf(BasicClient::class, $basicClient);
    }
}
