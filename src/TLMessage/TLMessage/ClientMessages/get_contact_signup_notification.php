<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/account.getContactSignUpNotification
 */
class get_contact_signup_notification implements TLClientMessage
{
    private const CONSTRUCTOR = 2668087080;

    public function getName(): string
    {
        return 'get_contact_signup_notification';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
