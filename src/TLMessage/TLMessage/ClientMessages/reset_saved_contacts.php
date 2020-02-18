<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/method/contacts.resetSaved */
class reset_saved_contacts implements TLClientMessage
{
    const CONSTRUCTOR = -2020263951; // 0x879537f1

    /**
     * @return string
     */
    public function getName()
    {
        return 'reset_saved';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
