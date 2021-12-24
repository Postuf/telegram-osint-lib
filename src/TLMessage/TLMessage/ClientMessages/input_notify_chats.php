<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/constructor/inputNotifyChats
 */
class input_notify_chats implements TLClientMessage
{
    public const CONSTRUCTOR = 1251338318;

    public function getName(): string
    {
        return 'input_notify_chats';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
