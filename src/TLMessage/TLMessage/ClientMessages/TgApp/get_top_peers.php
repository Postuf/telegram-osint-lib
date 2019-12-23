<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.getTopPeers
 */
class get_top_peers implements TLClientMessage
{

    const CONSTRUCTOR = -728224331; // 0xD4982DB5


    /**
     * @return string
     */
    public function getName()
    {
        return 'get_top_peers';
    }


    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0b101).
            Packer::packInt(0).
            Packer::packInt(20).
            Packer::packInt(0);
    }

}