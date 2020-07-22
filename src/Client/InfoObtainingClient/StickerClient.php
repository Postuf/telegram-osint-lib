<?php

/** @noinspection NullPointerExceptionInspection */
declare(strict_types=1);

namespace TelegramOSINT\Client\InfoObtainingClient;

use TelegramOSINT\Client\InfoObtainingClient\Models\StickerSetModel;
use TelegramOSINT\Client\StickerClient as StickerClientInterface;
use TelegramOSINT\MTSerialization\AnonymousMessage;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_featured_stickers;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\get_sticker_set;
use TelegramOSINT\TLMessage\TLMessage\ClientMessages\input_sticker_set_set_id;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Stickers\FeaturedStickers;
use TelegramOSINT\TLMessage\TLMessage\ServerMessages\Stickers\MessagesStickerSet;

class StickerClient extends InfoClient implements StickerClientInterface
{
    /**
     * @noinspection NullPointerExceptionInspection
     *
     * @param callable $onComplete function(FeaturedStickers $stickers)
     */
    public function getFeaturedStickers(callable $onComplete): void
    {
        $this->basicClient->getConnection()->getResponseAsync(
            new get_featured_stickers(),
            static function (AnonymousMessage $message) use ($onComplete) {
                if (FeaturedStickers::isIt($message)) {
                    $featuredStickers = new FeaturedStickers($message);
                    $onComplete($featuredStickers->getStickerSets());
                }
            }
        );
    }

    /**
     * @param int      $id
     * @param int      $accessHash
     * @param callable $onComplete function(StickerSetModel $model)
     */
    public function getStickerSet(int $id, int $accessHash, callable $onComplete): void
    {
        $this->basicClient->getConnection()->getResponseAsync(
            new get_sticker_set(new input_sticker_set_set_id($id, $accessHash)),
            static function (AnonymousMessage $message) use ($onComplete) {
                if (MessagesStickerSet::isIt($message)) {
                    $set = (new MessagesStickerSet($message))->getSet();
                    $onComplete(new StickerSetModel($set->getId(), $set->getAccessHash()));
                }
            }
        );
    }
}
