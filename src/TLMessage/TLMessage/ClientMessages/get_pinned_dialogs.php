<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getPinnedDialogs
 */
class get_pinned_dialogs implements TLClientMessage
{
    public const CONSTRUCTOR = 3602468338;

    public function getName(): string
    {
        return 'get_pinned_dialogs';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0);
    }
}
