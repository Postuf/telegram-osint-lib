<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TelegramOSINT\Auth\Factorization\GmpFactorizer;

class FactorizationTest extends TestCase
{
    const PRIME = 1550767997241791113;
    const P = 1033421369;
    const Q = 1500615377;

    /** @noinspection SpellCheckingInspection */
    public function test_gmp_factorizer(): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $pq = (new GmpFactorizer())->factorize(self::PRIME);
        $this->assertEquals($pq->getP(), self::P);
        $this->assertEquals($pq->getQ(), self::Q);
    }
}
