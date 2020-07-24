<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/constructor/inputPhotoFileLocation
 */
class input_photofilelocation implements TLClientMessage
{
    public const CONSTRUCTOR = 1075322878; // 0x40181ffe

    private int $id;
    private int $accessHash;
    private string $fileReference;
    private string $thumbSize;

    /**
     * @param int    $id
     * @param int    $accessHash
     * @param string $fileReference
     * @param string $thumbSize
     */
    public function __construct(int $id, int $accessHash, string $fileReference, string $thumbSize)
    {
        $this->id = $id;
        $this->accessHash = $accessHash;
        $this->fileReference = $fileReference;
        $this->thumbSize = $thumbSize;
    }

    public function getName(): string
    {
        return 'input_photofilelocation';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packLong($this->id).
            Packer::packLong($this->accessHash).
            Packer::packString($this->fileReference). // bytes
            Packer::packString($this->thumbSize);
    }
}
