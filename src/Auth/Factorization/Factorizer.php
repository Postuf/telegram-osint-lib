<?php

namespace Auth\Factorization;

interface Factorizer
{

    /**
     * @param integer $bigNumber
     * @return PQ
     */
    public function factorize($bigNumber);

}