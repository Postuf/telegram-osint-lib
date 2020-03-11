<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/langpack.getLanguages
 */
class get_languages implements TLClientMessage
{
    const CONSTRUCTOR = -2146445955; // 0x800FD57D

    public function getName(): string
    {
        return 'get_languages';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR);
    }
}
