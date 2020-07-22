<?php

declare(strict_types=1);

namespace Unit\Tools;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\Tools\BanInvalidator;
use TelegramOSINT\Tools\Cache;

class BanInvalidatorTest extends TestCase
{
    private const METHOD_DEL = 'del';

    /** @var BanInvalidator */
    private $invalidator;
    /** @var MockObject|Cache */
    private $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invalidator = new BanInvalidator();
        $this->cache = $this->createMock(Cache::class);
    }

    /**
     * Check cache is invalidated on ban exception
     */
    public function test_invalidate(): void
    {
        $this->cache
            ->expects(self::once())
            ->method(self::METHOD_DEL);
        $this->invalidator->invalidateIfNeeded(new TGException(TGException::ERR_MSG_USER_BANNED), $this->cache);
    }

    /**
     * Check cache is not invalidated on regular exception
     */
    public function test_invalidate_skip(): void
    {
        $this->cache
            ->expects(self::never())
            ->method(self::METHOD_DEL);
        $this->invalidator->invalidateIfNeeded(
            new TGException(TGException::ERR_CLIENT_PICTURE_ON_UNREACHABLE_DC),
            $this->cache
        );
    }
}
