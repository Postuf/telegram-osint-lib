<?php

declare(strict_types=1);

namespace Helpers;

use TelegramOSINT\TGConnection\DataCentre;
use TelegramOSINT\TGConnection\Socket\Socket;

/**
 * Class NullSocket
 *
 * Used for testing, all methods do nothing.
 */
class NullSocket implements Socket
{
    /**
     * {@inheritdoc}
     */
    public function readBinary(int $length)
    {
        return '';
    }

    public function terminate(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function writeBinary(string $payload)
    {
        return 0;
    }

    public function getDCInfo(): DataCentre
    {
        return DataCentre::getDefault();
    }

    public function poll(): void
    {

    }

    public function ready(): bool
    {
        return true;
    }
}
