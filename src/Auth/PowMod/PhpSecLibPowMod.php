<?php

namespace Auth\PowMod;

use phpseclib\Math\BigInteger;

class PhpSecLibPowMod implements PowMod
{
    const BASE = 256;

    /**
     * @param string $base
     * @param string $power
     * @param string $modulus
     *
     * @return string
     */
    public function powMod(string $base, string $power, string $modulus)
    {
        $baseB = new BigInteger($base, self::BASE);
        $powerB = new BigInteger($power, self::BASE);
        $modulusB = new BigInteger($modulus, self::BASE);

        return $baseB->powMod($powerB, $modulusB)->toBytes();
    }
}
