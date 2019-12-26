<?php

namespace Auth\Factorization;

class PQ
{
    private $p;
    private $q;

    public function __construct(int $p, int $q)
    {
        $this->p = $p <= $q ? $p : $q;
        $this->q = $p >= $q ? $p : $q;

        assert((int) $this->p <= (int) $this->q);
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
