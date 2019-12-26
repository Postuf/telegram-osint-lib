<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getPinnedDialogs
 */
class get_pinned_dialogs implements TLClientMessage
{
    const CONSTRUCTOR = -692498958; // 0xD6B94DF2

    /**
     * @return string
     */
    public function getName()
    {
        return 'get_pinned_dialogs';
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
