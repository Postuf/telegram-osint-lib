<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getFeaturedStickers
 */
class get_featured_stickers implements TLClientMessage
{

    const CONSTRUCTOR = 766298703; // 0x2DACCA4F


    /**
     * @return string
     */
    public function getName()
    {
        return 'get_featured_stickers';
    }


    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0);
    }

}