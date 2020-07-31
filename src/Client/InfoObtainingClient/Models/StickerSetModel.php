<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\InfoObtainingClient\Models;

use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Stickers\StickerSet;

class StickerSetModel
{
    /** @var int */
    public int $id;
    /** @var int */
    public int $accessHash;

    public function __construct(int $id, int $accessHash)
    {
        $this->id = $id;
        $this->accessHash = $accessHash;
    }

    public static function of(StickerSet $set): self
    {
        return new self($set->getId(), $set->getAccessHash());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAccessHash(): int
    {
        return $this->accessHash;
    }
}
