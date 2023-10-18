<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/upload.getFile
 */
class get_file implements TLClientMessage
{
    public const CONSTRUCTOR = 3193124286;

    private TLClientMessage $fileLocation;
    private int $offset;
    private int $limit;

    /**
     * @param TLClientMessage $fileLocation
     * @param int             $offset
     * @param int             $limit
     */
    public function __construct(TLClientMessage $fileLocation, int $offset, int $limit)
    {
        $this->fileLocation = $fileLocation;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function getName(): string
    {
        return 'upload.getFile';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt(0b1). // flags, precise = true
            Packer::packBytes($this->fileLocation->toBinary()).
            Packer::packLong($this->offset).
            Packer::packInt($this->limit);
    }
}
