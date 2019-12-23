<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

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
     * @param int $layerVersion
     * @param TLClientMessage $query
     */
    public function __construct(int $layerVersion, TLClientMessage $query)
    {
        $this->layerVersion = $layerVersion;
        $this->query = $query;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'invoke_with_layer';
    }


    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($this->layerVersion).
            Packer::packBytes($this->query->toBinary());
    }

}