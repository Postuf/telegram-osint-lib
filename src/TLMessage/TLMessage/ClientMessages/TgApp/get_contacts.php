<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.getContacts
 */
class get_contacts implements TLClientMessage
{
    const CONSTRUCTOR = -1071414113; // 0xC023849F

    /**
     * @return string
     */
    public function getName()
    {
        return 'get_contacts';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        $contactsHash = '';

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($contactsHash);
    }
}
