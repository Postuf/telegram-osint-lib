<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Auth;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

class ResPQ extends TLServerMessage
{
    /**
     * @param AnonymousMessage $tlMessage
     *
     * @return bool
     */
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return self::checkType($tlMessage, 'resPQ');
    }

    public function getClientNonce(): string
    {
        return $this->getTlMessage()->getValue('nonce');
    }

    public function getServerNonce(): string
    {
        return $this->getTlMessage()->getValue('server_nonce');
    }

    /**
     * @noinspection PhpUnused
     */
    public function getPQ(): int
    {
        $pqBin = $this->getTlMessage()->getValue('pq');

        return unpack('J', $pqBin)[1];
    }

    public function getFingerprints(): array
    {
        return $this->getTlMessage()->getValue('server_public_key_fingerprints');
    }
}
