<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/**
 * @see https://core.telegram.org/constructor/inputPeerEmpty
 */
class input_peer_empty extends input_peer
{
    const CONSTRUCTOR = 2134579434; // 0x7f3b18ea

    /**
     * @return string
     */
    public function getName()
    {
        return 'input_peer_empty';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
