<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\LibConfig;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/langpack.getLangPack
 * note: current method has ctor 0xf2f2330a (layer 105)
 */
class get_langpack implements TLClientMessage
{
    public const CONSTRUCTOR = 4075959050;

    private string $langCode;

    /**
     * @param string $langCode
     */
    public function __construct(string $langCode)
    {
        $this->langCode = $langCode;
    }

    public function getName(): string
    {
        return 'get_langpack';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString(LibConfig::APP_DEFAULT_LANG_PACK).
            Packer::packString($this->langCode);
    }
}
