<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/help.getDeepLinkInfo
 */
class get_deeplink_info implements TLClientMessage
{
    private const CONSTRUCTOR = 1072547679;

    /** @var string */
    private string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getName(): string
    {
        return 'get_deeplink_info';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packString($this->path);
    }
}
