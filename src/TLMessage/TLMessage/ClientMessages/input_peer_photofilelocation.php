<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/**
 * @see https://core.telegram.org/constructor/inputPeerPhotoFileLocation
 */
class input_peer_photofilelocation extends input_peer
{
    public const CONSTRUCTOR = 668375447; // 0x27D69997

    /**
     * @var input_peer
     */
    private $location;
    /**
     * @var int
     */
    private $volumeId;
    /**
     * @var int
     */
    private $localId;
    /**
     * @var bool
     */
    private $bigPhoto;

    /**
     * @param input_peer $location
     * @param int        $volumeId
     * @param int        $localId
     * @param int        $bigPhoto
     */
    public function __construct(input_peer $location, int $volumeId, int $localId, int $bigPhoto)
    {
        $this->location = $location;
        $this->volumeId = $volumeId;
        $this->localId = $localId;
        $this->bigPhoto = $bigPhoto;
    }

    public function getName(): string
    {
        return 'input_peer_user';
    }

    public function toBinary(): string
    {
        $flags = $this->bigPhoto ? 0b1 : 0b0;

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($flags).
            Packer::packBytes($this->location->toBinary()).
            Packer::packLong($this->volumeId).
            Packer::packInt($this->localId);
    }
}
