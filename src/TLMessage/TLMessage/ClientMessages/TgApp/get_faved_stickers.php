<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getFavedStickers
 */
class get_faved_stickers implements TLClientMessage
{

    const CONSTRUCTOR = 567151374; // 0x21CE0B0E


    /**
     * @return string
     */
    public function getName()
    {
        return 'get_faved_stickers';
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