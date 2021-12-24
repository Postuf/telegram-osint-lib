<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getFavedStickers
 */
class get_faved_stickers implements TLClientMessage
{
    public const CONSTRUCTOR = 82946729;

    public function getName(): string
    {
        return 'get_faved_stickers';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packLong(0);
    }
}
