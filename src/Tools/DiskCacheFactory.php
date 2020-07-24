<?php

/** @noinspection PhpDocMissingThrowsInspection */
declare(strict_types=1);

namespace TelegramOSINT\Tools;

use TelegramOSINT\Client\AuthKey\AuthKey;

class DiskCacheFactory implements CacheFactoryInterface
{
    private const PREFIX = '/tmp/';
    private const SUFFIX = '.txt';

    /** @var string */
    private string $prefix;

    public function __construct(string $prefix = self::PREFIX)
    {
        $this->prefix = $prefix;
    }

    /**
     * @param AuthKey $key
     *
     * @return Cache
     */
    public function generate(AuthKey $key): Cache
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return new CacheMap($this->prefix.md5($key->getSerializedAuthKey()).self::SUFFIX);
    }
}
