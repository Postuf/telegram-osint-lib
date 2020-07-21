<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ServerMessages\Stickers;

use TelegramOSINT\MTSerialization\AnonymousMessage;

/**
 * @see https://core.telegram.org/constructor/stickerSetMultiCovered
 */
class StickerSetMultiCovered extends StickerSetCoveredBase
{
    public static function isIt(AnonymousMessage $tlMessage): bool
    {
        return $tlMessage->getType() === 'stickerSetMultiCovered';
    }
}
