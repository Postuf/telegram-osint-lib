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
    private const CONSTRUCTOR = 1574346258; // 0xC023849F

    public function getName(): string
    {
        return 'get_contacts';
    }

    public function toBinary(): string
    {
        $contactsHash = 0;

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packLong($contactsHash);
    }
}
