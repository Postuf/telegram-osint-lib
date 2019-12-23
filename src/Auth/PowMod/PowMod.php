<?php


namespace Auth\PowMod;


interface PowMod
{
    /**
     * @param string $base
     * @param string $power
     * @param string $modulus
     * @return string
     */
    public function powMod(string $base, string $power, string $modulus);

}