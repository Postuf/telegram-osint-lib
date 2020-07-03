<?php

declare(strict_types=1);

namespace TelegramOSINT\Auth\Factorization;

class PQ
{
    /** @var int */
    private $p;
    /** @var int */
    private $q;

    public function __construct(int $p, int $q)
    {
        $this->p = $p <= $q ? $p : $q;
        $this->q = $p >= $q ? $p : $q;

        assert($this->p <= $this->q);
    }

    /**
     * @return mixed
     */
    public function getP()
    {
        return $this->p;
    }

    /**
     * @return mixed
     */
    public function getQ()
    {
        return $this->q;
    }
}
