<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class input_messages_filter_url implements TLClientMessage
{
    const CONSTRUCTOR = 2129714567; //0x7EF0DD87

    public function getName()
    {
        return 'input_messages_filter_url';
    }

    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
