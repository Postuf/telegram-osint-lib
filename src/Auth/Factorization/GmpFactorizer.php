<?php

namespace TelegramOSINT\Auth\Factorization;

use GMP;
use TelegramOSINT\Exception\TGException;

class GmpFactorizer implements Factorizer
{
    /**
     * @param int $bigNumber
     *
     * @throws TGException
     *
     * @return PQ
     */
    public function factorize($bigNumber)
    {
        if(!extension_loaded('gmp'))
            throw new TGException(TGException::ERR_ASSERT_EXTENSION_MISSING, 'gmp');
        $gmp_p = $this->gmp_pollard_rho($bigNumber);
        $p = gmp_strval($gmp_p);
        $gmp_q = gmp_div_qr(gmp_init($bigNumber), gmp_init($p));
        $q = gmp_strval($gmp_q[0]);
        $r = gmp_strval($gmp_q[1]);

        if((int) $r != 0)
            throw new TGException(TGException::ERR_AUTH_GMP_FACTOR_WRONG_RESULT);

        return new PQ($p, $q);
    }

    /**
     * @param int $number
     *
     * @return GMP
     */
    private function gmp_pollard_rho($number) {

        $one = gmp_init(1);
        $two = gmp_init(2);

        $n = $number instanceof GMP ? $number : gmp_init($number);
        if(gmp_mod($n, $two) == 0) {
            return $two;
        }

        $x = gmp_random_range($one, gmp_sub($n, $one));
        $c = gmp_random_range($one, gmp_sub($n, $one));
        $y = $x;
        $g = $one;

        while($g == $one) {
            $x = gmp_mod(gmp_add(gmp_mod(gmp_mul($x, $x), $n), $c), $n);
            $y = gmp_mod(gmp_add(gmp_mod(gmp_mul($y, $y), $n), $c), $n);
            $y = gmp_mod(gmp_add(gmp_mod(gmp_mul($y, $y), $n), $c), $n);
            $g = gmp_gcd(gmp_abs(gmp_sub($x, $y)), $n);
        }

        return $g;
    }
}
