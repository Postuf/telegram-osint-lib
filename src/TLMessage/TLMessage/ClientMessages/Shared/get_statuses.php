<?php

namespace TLMessage\TLMessage\ClientMessages\Shared;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.getStatuses
 */
class get_statuses implements TLClientMessage
{
    const CONSTRUCTOR = -995929106; // 0xC4A353EE

    /**
     * @return string
     */
    public function getName()
    {
        return 'get_statuses';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR);
    }
}
