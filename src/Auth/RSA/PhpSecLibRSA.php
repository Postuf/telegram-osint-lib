<?php

namespace Auth\RSA;

use Auth\PowMod\PhpSecLibPowMod;
use Auth\RSA\RSA as AuthRSA;
use phpseclib\Crypt\RSA;

class PhpSecLibRSA implements AuthRSA
{
    /**
     * @param string $data
     * @param string $key
     *
     * @return string
     */
    public function encrypt($data, $key)
    {
        $rsa = new RSA();
        $rsa->loadKey($key);
        $n = $rsa->modulus;
        $e = $rsa->exponent;

        return (new PhpSecLibPowMod())->powMod($data, $e->toBytes(), $n->toBytes());
    }
}
