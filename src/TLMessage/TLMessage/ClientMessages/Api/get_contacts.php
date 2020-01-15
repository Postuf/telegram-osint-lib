<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\Api;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/method/contacts.getContacts */
class get_contacts implements TLClientMessage
{
    const CONSTRUCTOR = 0x22c6aa08; // 583445000

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
