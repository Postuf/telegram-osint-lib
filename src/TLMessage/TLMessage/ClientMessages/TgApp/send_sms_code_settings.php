<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/constructor/codeSettings
 */
class send_sms_code_settings implements TLClientMessage
{
    const CONSTRUCTOR = -557924733; // 0xDEBEBE83

    /**
     * @return string
     */
    public function getName()
    {
        return 'send_code_settings';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        $bitMask = 16;

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($bitMask);
    }
}
