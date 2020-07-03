<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getDialogs
 */
class get_dialogs implements TLClientMessage
{
    private const CONSTRUCTOR = -1594999949; // 0xA0EE3B73

    public function getName(): string
    {
        return 'get_dialogs';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0b1).
            // if flag & 2 set folder_id here
            Packer::packInt(0).
            Packer::packInt(0).
            Packer::packBytes((new input_peer_empty())->toBinary()).
            Packer::packInt(100).
            Packer::packInt(0);
    }
}
