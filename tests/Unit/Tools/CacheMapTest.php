<?php

declare(strict_types=1);

namespace Unit\Tools;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Tools\CacheMap;

class CacheMapTest extends TestCase
{
    private const PATH = '/tmp/cachemap.test';

    public function test_set_and_get(): void
    {
        $cacheMap = new CacheMap(self::PATH);
        $key = 'key';
        $value = 1;
        $cacheMap->set($key, $value);
        $this->assertEquals($value, $cacheMap->get($key));

        @unlink(self::PATH);
    }
}
