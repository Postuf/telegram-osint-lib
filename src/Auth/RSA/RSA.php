<?php

namespace Auth\RSA;

interface RSA
{
    /**
     * @param string $data
     * @param string $key
     * @return string
     */
    public function encrypt($data, $key);
}