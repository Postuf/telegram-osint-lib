<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\LibConfig;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/langpack.getLanguages
 */
class get_languages implements TLClientMessage
{
    public const CONSTRUCTOR = 1120311183;

    public function getName(): string
    {
        return 'get_languages';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString(LibConfig::APP_DEFAULT_LANG_PACK);
    }
}
