<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/upload.getFile
 */
class get_file implements TLClientMessage
{
    const CONSTRUCTOR = -1319462148; // 0xB15A9AFC

    /**
     * @var TLClientMessage
     */
    private $fileLocation;
    /**
     * @var int
     */
    private $offset;
    /**
     * @var int
     */
    private $limit;

    /**
     * @param TLClientMessage $fileLocation
     * @param int             $offset
     * @param int             $limit
     */
    public function __construct(TLClientMessage $fileLocation, $offset, $limit)
    {
        $this->fileLocation = $fileLocation;
        $this->offset = $offset;
        $this->limit = $limit;

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'get_file';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0b1). //precise
            Packer::packBytes($this->fileLocation->toBinary()).
            Packer::packInt($this->offset).
            Packer::packInt($this->limit);
    }
}
