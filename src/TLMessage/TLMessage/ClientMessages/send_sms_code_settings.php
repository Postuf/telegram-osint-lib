<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/constructor/codeSettings
 */
class send_sms_code_settings implements TLClientMessage
{
    private const CONSTRUCTOR = -557924733; // 0xDEBEBE83

    public function getName(): string
    {
        return 'send_code_settings';
    }

    public function toBinary(): string
    {
        $bitMask = 0;

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($bitMask);
    }
}
