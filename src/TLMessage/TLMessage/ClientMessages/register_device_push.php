<?php

/** @noinspection PhpUnused */

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use Exception;
use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/account.registerDevice
 * current has ctor 0x68976c6f (layer 105)
 */
class register_device_push implements TLClientMessage
{
    private const CONSTRUCTOR = 1669245048; // 0x637EA878

    public function getName(): string
    {
        return 'register_device_push';
    }

    /**
     * @throws TGException
     *
     * @return string
     */
    public function toBinary(): string
    {
        try {
            return
                Packer::packConstructor(self::CONSTRUCTOR).
                Packer::packInt(7).
                Packer::packString(random_int(1000, 9999).random_int(1000, 9999).random_int(1000, 9999).random_int(1000, 9999).random_int(100, 999));
        } catch (Exception $e) {
            throw new TGException(TGException::ERR_CRYPTO_INVALID);
        }
    }
}
