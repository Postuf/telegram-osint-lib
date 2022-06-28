<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/method/contacts.resetSaved */
class reset_saved_contacts implements TLClientMessage
{
    public const CONSTRUCTOR = 2274703345; // 0x879537f1

    public function getName(): string
    {
        return 'reset_saved';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
