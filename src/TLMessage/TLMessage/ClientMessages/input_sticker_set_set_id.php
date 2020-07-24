<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/**
 * @see https://core.telegram.org/constructor/inputStickerSetID
 */
class input_sticker_set_set_id implements input_sticker_set
{
    private const CONSTRUCTOR = -1645763991; // 0x9de7a269

    /** @var int */
    private int $id;
    /** @var int */
    private int $accessHash;

    public function __construct(int $id, int $accessHash)
    {
        $this->id = $id;
        $this->accessHash = $accessHash;
    }

    public function getName(): string
    {
        return 'inputStickerSetID';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packLong($this->id).
            Packer::packLong($this->accessHash);
    }
}
