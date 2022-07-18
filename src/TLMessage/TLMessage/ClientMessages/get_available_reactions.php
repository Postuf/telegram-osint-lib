<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/method/messages.getAvailableReactions */
class get_available_reactions implements TLClientMessage
{
    public const CONSTRUCTOR = 417243308;

    public function getName(): string
    {
        return 'get_available_reactions';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0);
    }
}
