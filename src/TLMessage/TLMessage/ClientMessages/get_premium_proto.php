<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/help.getPremiumPromo
 */
class get_premium_proto implements TLClientMessage
{
    private const CONSTRUCTOR = 3088815060;

    public function getName(): string
    {
        return 'get_premium_proto';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
