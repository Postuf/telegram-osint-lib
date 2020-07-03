<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class input_messages_filter_url implements TLClientMessage
{
    public const CONSTRUCTOR = 2129714567; //0x7EF0DD87

    public function getName(): string
    {
        return 'input_messages_filter_url';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
