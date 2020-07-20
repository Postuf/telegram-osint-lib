<?php

declare(strict_types=1);

namespace TelegramOSINT\Scenario\Models;

use TelegramOSINT\Client\InfoObtainingClient\Models\StickerSetModel;

class StickerSetId
{
    /** @var int */
    private $id;
    /** @var int */
    private $accessHash;

    public function __construct(int $id, int $accessHash)
    {
        $this->id = $id;
        $this->accessHash = $accessHash;
    }

    public static function of(StickerSetModel $model): self
    {
        return new self($model->getId(), $model->getAccessHash());
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
