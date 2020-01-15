<?php

declare(strict_types=1);

namespace TelegramOSINT\Client\InfoObtainingClient\Models;

class FileModel
{
    /** @var int */
    private $id;
    /** @var int */
    private $accessHash;
    /** @var string binary */
    private $fileReference;
    /** @var string */
    private $sizeId;
    /** @var int */
    private $dcId;

    public function __construct(
        int $id,
        int $accessHash,
        string $fileReference,
        string $sizeId,
        int $dcId
    ) {
        $this->id = $id;
        $this->accessHash = $accessHash;
        $this->fileReference = $fileReference;
        $this->sizeId = $sizeId;
        $this->dcId = $dcId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAccessHash(): int
    {
        return $this->accessHash;
    }

    public function getDcId(): int
    {
        return $this->dcId;
    }

    public function getFileReference(): string
    {
        return $this->fileReference;
    }

    public function getSizeId(): string
    {
        return $this->sizeId;
    }
}
