<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getAllDrafts
 */
class get_all_drafts implements TLClientMessage
{
    private const CONSTRUCTOR = 1782549861;

    public function getName(): string
    {
        return 'get_all_drafts';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR);
    }
}
