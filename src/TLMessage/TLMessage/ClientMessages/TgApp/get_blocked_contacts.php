<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.getBlocked
 */
class get_blocked_contacts implements TLClientMessage
{

    const CONSTRUCTOR = -176409329; // 0xF57C350F


    /**
     * @return string
     */
    public function getName()
    {
        return 'get_blocked_contacts';
    }


    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0).
            Packer::packInt(200);
    }

}