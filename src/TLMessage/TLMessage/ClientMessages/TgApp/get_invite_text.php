<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\TgApp;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/method/help.getInviteText */
class get_invite_text implements TLClientMessage
{
    const CONSTRUCTOR = 1295590211; // 0x4D392343

    /**
     * @return string
     */
    public function getName()
    {
        return 'get_invite_text';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
