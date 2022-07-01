<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\LibConfig;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/account.getThemes
 */
class get_themes implements TLClientMessage
{
    public const CONSTRUCTOR = 1913054296;

    public function getName(): string
    {
        return 'get_themes';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString(LibConfig::APP_DEFAULT_LANG_PACK).
            Packer::packLong(0);
    }
}
