<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/method/updates.getState */
class get_state implements TLClientMessage
{
    const CONSTRUCTOR = -304838614; // 0xEDD4882A

    /**
     * @return string
     */
    public function getName()
    {
        return 'get_state';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
