<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class ping implements TLClientMessage
{
    private const CONSTRUCTOR = 2059302892;

    /**
     * @var string
     */
    private ?string $pingId;

    /**
     * @param string|null $pingId
     *
     * @throws TGException
     */
    public function __construct(string $pingId = null)
    {
        $this->pingId = $pingId ?: self::createPingId();
    }

    /**
     * @throws TGException
     *
     * @return string
     */
    public static function createPingId(): string
    {
        /** @noinspection CryptographicallySecureRandomnessInspection */
        $id = openssl_random_pseudo_bytes(8, $strong);
        if ($id === false || $strong === false) {
            throw new TGException(TGException::ERR_CRYPTO_INVALID);
        }

        return $id;
    }

    public function getName(): string
    {
        return 'ping';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBytes($this->pingId);
    }
}
