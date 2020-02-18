<?php

/** @noinspection PhpUnused */

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/account.registerDevice
 * current has ctor 0x68976c6f (layer 105)
 */
class register_device_push implements TLClientMessage
{
    const CONSTRUCTOR = 1669245048; // 0x637EA878

    /**
     * @return string
     */
    public function getName()
    {
        return 'register_device_push';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(7).
            Packer::packString(rand(1000, 9999).rand(1000, 9999).rand(1000, 9999).rand(1000, 9999).rand(100, 999));
    }
}
