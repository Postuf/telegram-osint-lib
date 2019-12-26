<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;

/**
 * @see https://core.telegram.org/constructor/inputPeerPhotoFileLocation
 */
class input_peer_photofilelocation extends input_peer
{
    const CONSTRUCTOR = 668375447; // 0x27D69997

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

    /**
     * @return string
     */
    public function getName()
    {
        return 'input_peer_user';
    }

    /**
     * @return string
     */
    public function toBinary()
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
