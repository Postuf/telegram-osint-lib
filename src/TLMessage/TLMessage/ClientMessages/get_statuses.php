<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.getStatuses
 */
class get_statuses implements TLClientMessage
{
    public const CONSTRUCTOR = 3299038190;

    public function getName(): string
    {
        return 'get_statuses';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR);
    }
}
