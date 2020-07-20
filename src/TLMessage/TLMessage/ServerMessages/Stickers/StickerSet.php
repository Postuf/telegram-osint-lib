<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Stickers;

use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

/**
 * @see https://core.telegram.org/constructor/stickerSet
 */
class StickerSet extends TLServerMessage
{
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return $tlMessage->getType() === 'stickerSet';
    }

    public function getId(): int
    {
        return $this->getTlMessage()->getValue('id');
    }

    public function getAccessHash(): int
    {
        return $this->getTlMessage()->getValue('access_hash');
    }
}
