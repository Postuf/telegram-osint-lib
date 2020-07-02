<?php

namespace TelegramOSINT\Auth\PowMod;

interface PowMod
{
    public function powMod(string $base, string $power, string $modulus): string;
}
