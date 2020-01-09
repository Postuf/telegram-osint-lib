<?php

declare(strict_types=1);

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/constructor/inputPhotoFileLocation
 */
class input_photofilelocation implements TLClientMessage
{
    const CONSTRUCTOR = 1075322878; // 0x40181ffe

    /**
     * @var int
     */
    private $id;
    /**
     * @var int
     */
    private $accessHash;
    /**
     * @var string
     */
    private $fileReference;
    /**
     * @var string
     */
    private $thumbSize;

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

    /**
     * @return string
     */
    public function getName()
    {
        return 'input_photofilelocation';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packLong($this->id).
            Packer::packLong($this->accessHash).
            Packer::packString($this->fileReference). // bytes
            Packer::packString($this->thumbSize);
    }
}
