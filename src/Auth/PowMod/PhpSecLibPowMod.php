<?php

namespace TelegramOSINT\Auth\PowMod;

use phpseclib\Math\BigInteger;

class PhpSecLibPowMod implements PowMod
{
    public const BASE = 256;

    public function powMod(string $base, string $power, string $modulus): string
    {
        $baseB = new BigInteger($base, self::BASE);
        $powerB = new BigInteger($power, self::BASE);
        $modulusB = new BigInteger($modulus, self::BASE);

        return $baseB->powMod($powerB, $modulusB)->toBytes();
    }
}
