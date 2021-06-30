<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/**
 * @see https://core.telegram.org/constructor/inputPeerPhotoFileLocation
 */
class input_peer_photofilelocation extends input_peer
{
    public const CONSTRUCTOR = 925204121;

    private input_peer $location;
    private int $photoId;
    private bool $bigPhoto;

    /**
     * @param input_peer $location
     * @param int        $photoId
     * @param bool        $bigPhoto
     */
    public function __construct(input_peer $location, int $photoId, bool $bigPhoto)
    {
        $this->location = $location;
        $this->photoId = $photoId;
        $this->bigPhoto = $bigPhoto;
    }

    public function getName(): string
    {
        return 'input_peer_photofilelocation';
    }

    public function toBinary(): string
    {
        $flags = $this->bigPhoto ? 0b1 : 0b0;

        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($flags).
            Packer::packBytes($this->location->toBinary()).
            Packer::packLong($this->photoId);
    }
}
