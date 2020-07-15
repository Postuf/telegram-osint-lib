<?php

/** @noinspection PhpDocMissingThrowsInspection */
declare(strict_types=1);

namespace TelegramOSINT\Tools;

use TelegramOSINT\Client\AuthKey\AuthKey;

class DiskCacheFactory implements CacheFactoryInterface
{
    private const PREFIX = '/tmp/';
    private const SUFFIX = '.txt';

    /**
     * @param AuthKey $key
     *
     * @return Cache
     */
    public function generate(AuthKey $key): Cache
    {
        return new CacheMap(self::PREFIX.md5($key->getSerializedAuthKey()).self::SUFFIX);
    }
}
