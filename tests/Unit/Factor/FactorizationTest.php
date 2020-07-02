<?php

declare(strict_types=1);

namespace Unit\Factor;

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Auth\Factorization\GmpFactorizer;

class FactorizationTest extends TestCase
{
    private const PRIME = 1550767997241791113;
    private const P = 1033421369;
    private const Q = 1500615377;

    /** @noinspection SpellCheckingInspection */
    public function test_gmp_factorizer(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $pq = (new GmpFactorizer())->factorize(self::PRIME);
        $this->assertEquals(self::P, $pq->getP());
        $this->assertEquals(self::Q, $pq->getQ());
    }
}
