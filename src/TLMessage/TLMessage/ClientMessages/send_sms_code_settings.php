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
    private const CONSTRUCTOR = 2321836482; // 0x8a6469c2

    public function getName(): string
    {
        return 'send_code_settings';
    }

    public function toBinary(): string
    {
        // allow_flashcall = false
        // current_number = false
        // allow_app_hash = true, include sms-token in sms text
        // allow_missed_call = false
        // logout_tokens = false
        $flags = 0b00010000;

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($flags);
    }
}
