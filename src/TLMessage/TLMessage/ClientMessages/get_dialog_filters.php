<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getDialogFilters
 */
class get_dialog_filters implements TLClientMessage
{
    private const CONSTRUCTOR = 4053719405;

    public function getName(): string
    {
        return 'get_dialog_filters';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR);
    }
}
