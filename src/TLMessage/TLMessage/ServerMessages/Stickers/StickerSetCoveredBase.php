<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Stickers;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

abstract class StickerSetCoveredBase extends TLServerMessage
{
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
