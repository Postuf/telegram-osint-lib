<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/method/messages.getAttachMenuBots */
class get_attach_menu_bots implements TLClientMessage
{
    public const CONSTRUCTOR = 385663691;

    public function getName(): string
    {
        return 'get_attach_menu_bots';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packLong(0);
    }
}
