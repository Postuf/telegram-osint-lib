<?php

declare(strict_types=1);

namespace Unit\Client;

use Helpers\Mocks\ControllableClock;
use Helpers\Mocks\DeferredClientImpl;
use PHPUnit\Framework\TestCase;

class DeferredClientTest extends TestCase
{
    /** @var ControllableClock */
    private $clock;
    /** @var DeferredClientImpl */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->clock = new ControllableClock();
        $this->client = new DeferredClientImpl($this->clock);
    }

    /**
     * Check that deferred call is called at given time
     */
    public function test_deferred_is_called(): void
    {
        $called = false;
        $cb = static function () use (&$called) { $called = true; };
        $this->client->defer($cb);
        $this->client->processDeferredQueue();
        $this->assertTrue($called);
    }

    /**
     * Check that deferred call is not called if time not passed
     */
    public function test_deferred_is_not_called(): void
    {
        $called = false;
        $cb = static function () use (&$called) { $called = true; };
        $this->client->defer($cb, 10);
        $this->client->processDeferredQueue();
        $this->assertFalse($called);
    }

    /**
     * Check that deferred call is not called if time not passed
     */
    public function test_deferred_sequence(): void
    {
        $called1 = false;
        $cb1 = static function () use (&$called1) { $called1 = true; };
        $called2 = false;
        $cb2 = static function () use (&$called2) { $called2 = true; };
        $this->client->defer($cb2, 2);
        $this->client->defer($cb1, 1);

        $this->clock->usleep(ControllableClock::SECONDS_MS);
        $this->client->processDeferredQueue();
        $this->assertTrue($called1);
        $this->assertFalse($called2);

        $this->clock->usleep(ControllableClock::SECONDS_MS);
        $this->client->processDeferredQueue();
        $this->assertTrue($called2);
    }
}
