<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getStickerSet
 */
class get_sticker_set implements TLClientMessage
{
    private const CONSTRUCTOR = 3365989492;

    private input_sticker_set $sticker;

    public function __construct(input_sticker_set $sticker)
    {
        $this->sticker = $sticker;
    }

    public function getName(): string
    {
        return 'messages.getStickerSet';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            $this->sticker->toBinary().
            Packer::packInt(0);
    }
}
