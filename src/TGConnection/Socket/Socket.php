<?php

namespace TGConnection\Socket;

use TGConnection\DataCentre;

interface Socket
{
    /**
     * @param int $length
     *
     * @return string|null
     */
    public function readBinary(int $length);

    /**
     * @return void
     */
    public function terminate();

    /**
     * @param string $payload
     *
     * @return int
     */
    public function writeBinary(string $payload);

    /**
     * @return DataCentre
     */
    public function getDCInfo();

    public function poll(): void;

    public function ready(): bool;
}
