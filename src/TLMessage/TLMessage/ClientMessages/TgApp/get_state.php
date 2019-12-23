<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/method/updates.getState */
class get_state implements TLClientMessage
{

    const CONSTRUCTOR = -304838614; // 0xEDD4882A


    /**
     * @return string
     */
    public function getName()
    {
        return 'get_state';
    }


    /**
     * @return string
     */
    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }

}