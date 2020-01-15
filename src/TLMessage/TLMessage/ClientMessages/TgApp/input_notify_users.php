<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/constructor/inputNotifyUsers */
class input_notify_users implements TLClientMessage
{
    const CONSTRUCTOR = 423314455; // 0x193B4417

    /**
     * @return string
     */
    public function getName()
    {
        return 'input_notify_users';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
