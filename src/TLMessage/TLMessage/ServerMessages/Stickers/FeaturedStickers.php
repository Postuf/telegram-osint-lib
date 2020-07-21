<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Stickers;

use TelegramOSINT\Exception\TGException;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\TLServerMessage;

/**
 * @see https://core.telegram.org/type/messages.FeaturedStickers
 */
class FeaturedStickers extends TLServerMessage
{
    /**
     * @throws TGException
     *
     * @return StickerSetCoveredBase[]
     */
    public function getStickerSets(): array
    {
        $nodes = $this->getTlMessage()->getNodes('sets');
        $results = [];
        foreach ($nodes as $node) {
            if (StickerSetCovered::isIt($node)) {
                $results[] = new StickerSetCovered($node);
            } elseif (StickerSetMultiCovered::isIt($node)) {
                $results[] = new StickerSetMultiCovered($node);
            }
        }

        return $results;
    }

    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return $tlMessage->getType() === 'messages.featuredStickers';
    }
}
