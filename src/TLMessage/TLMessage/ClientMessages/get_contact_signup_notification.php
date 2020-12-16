<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/help.getConfig
 */
class get_contact_signup_notification implements TLClientMessage
{
    private const CONSTRUCTOR = -1626880216;

    public function getName(): string
    {
        return 'get_contact_signup_notification';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
