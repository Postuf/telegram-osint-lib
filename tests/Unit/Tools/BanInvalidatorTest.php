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
     * Проверка что кеш инвалидируется при бане бота
     */
    public function test_invalidate(): void
    {
        $this->cache
            ->expects(self::once())
            ->method(self::METHOD_DEL);
        $this->invalidator->invalidateIfNeeded(new TGException(TGException::ERR_MSG_USER_BANNED), $this->cache);
    }

    /**
     * Проверка что кеш не инвалидируется при прочих ошибках
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
