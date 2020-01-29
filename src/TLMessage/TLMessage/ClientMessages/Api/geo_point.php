<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\Api;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/constructor/inputGeoPoint */
class geo_point implements TLClientMessage
{
    private const CONSTRUCTOR = -206066487; // 0xf3b7acc9

    /** @var float */
    private $lat;
    /** @var float */
    private $lon;

    public function __construct(float $lat, float $lon)
    {
        $this->lat = $lat;
        $this->lon = $lon;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'geo_point';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        $l1 = Packer::packDouble($this->lat);
        $l2 = Packer::packDouble($this->lon);

        return Packer::packConstructor(self::CONSTRUCTOR).$l1.$l2;
    }
}
