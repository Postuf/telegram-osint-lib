<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/help.getTermsOfServiceUpdate
 */
class get_terms_of_service_update implements TLClientMessage
{
    public const CONSTRUCTOR = 749019089; // 0x2CA51FD1

    public function getName(): string
    {
        return 'get_terms_of_service_update';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
