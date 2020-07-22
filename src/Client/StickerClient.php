<?php

declare(strict_types=1);

namespace TelegramOSINT\Client;

interface StickerClient
{
    /**
     * @param callable $onComplete function(FeaturedStickers $stickers)
     */
    public function getFeaturedStickers(callable $onComplete): void;

    /**
     * @param int      $id
     * @param int      $accessHash
     * @param callable $onComplete function(StickerSetModel $model)
     */
    public function getStickerSet(int $id, int $accessHash, callable $onComplete): void;
}
