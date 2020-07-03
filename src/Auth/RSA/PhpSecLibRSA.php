<?php

namespace TelegramOSINT\Auth\RSA;

use phpseclib\Crypt\RSA;
use TelegramOSINT\Auth\PowMod\PhpSecLibPowMod;
use TelegramOSINT\Auth\RSA\RSA as AuthRSA;

class PhpSecLibRSA implements AuthRSA
{
    public function encrypt(string $data, string $key): string
    {
        $rsa = new RSA();
        $rsa->loadKey($key);
        $n = $rsa->modulus;
        $e = $rsa->exponent;

        return (new PhpSecLibPowMod())->powMod($data, $e->toBytes(), $n->toBytes());
    }
}
