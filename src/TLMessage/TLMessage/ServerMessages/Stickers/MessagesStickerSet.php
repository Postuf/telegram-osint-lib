<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Stickers;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

/**
 * @see https://core.telegram.org/method/messages.getStickerSet
 */
class MessagesStickerSet extends TLServerMessage
{
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return $tlMessage->getType() === 'messages.stickerSet';
    }

    /**
     * @throws TGException
     *
     * @return StickerSet
     */
    public function getSet(): StickerSet
    {
        return new StickerSet($this->getTlMessage()->getNode('set'));
    }
}
