<?php

namespace TelegramOSINT\Auth\Factorization;

interface Factorizer
{
    /**
     * @param int $bigNumber
     *
     * @return PQ
     */
    public function factorize($bigNumber);
}
