<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class invoke_with_layer implements TLClientMessage
{
    const CONSTRUCTOR = 0xda9b0d0d;

    /**
     * @var int
     */
    private $layerVersion;
    /**
     * @var TLClientMessage
     */
    private $query;

    /**
     * @param int             $layerVersion
     * @param TLClientMessage $query
     */
    public function __construct(int $layerVersion, TLClientMessage $query)
    {
        $this->layerVersion = $layerVersion;
        $this->query = $query;
    }

    public function getName(): string
    {
        return 'invoke_with_layer';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($this->layerVersion).
            Packer::packBytes($this->query->toBinary());
    }
}
