<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.getContacts
 */
class get_contacts implements TLClientMessage
{
    const CONSTRUCTOR = -1071414113; // 0xC023849F

    public function getName(): string
    {
        return 'get_contacts';
    }

    public function toBinary(): string
    {
        $contactsHash = '';

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($contactsHash);
    }
}
