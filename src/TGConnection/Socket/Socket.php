<?php

namespace TelegramOSINT\TGConnection\Socket;

use TelegramOSINT\TGConnection\DataCentre;

interface Socket
{
    /**
     * @param int $length
     *
     * @return string
     */
    public function readBinary(int $length);

    public function terminate(): void;

    /**
     * @param string $payload
     *
     * @return int
     */
    public function writeBinary(string $payload);

    public function getDCInfo(): DataCentre;

    public function poll(): void;

    public function ready(): bool;
}
