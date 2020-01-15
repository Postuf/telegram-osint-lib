<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/constructor/inputNotifyChats
 */
class input_notify_chats implements TLClientMessage
{
    const CONSTRUCTOR = 1251338318; // 0x4A95E84E

    /**
     * @return string
     */
    public function getName()
    {
        return 'input_notify_chats';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
