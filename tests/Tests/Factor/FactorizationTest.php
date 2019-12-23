<?php

use Auth\Factorization\GmpFactorizer;
use PHPUnit\Framework\TestCase;

class FactorizationTest extends TestCase
{

    const PRIME = 1550767997241791113;
    const P = 1033421369;
    const Q = 1500615377;


    public function test_gmp_factorizer()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $pq = (new GmpFactorizer())->factorize(self::PRIME);
        $this->assertEquals($pq->getP(), self::P);
        $this->assertEquals($pq->getQ(), self::Q);
    }
}
