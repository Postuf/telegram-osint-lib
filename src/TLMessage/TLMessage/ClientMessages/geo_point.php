<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/constructor/inputGeoPoint */
class geo_point implements TLClientMessage
{
    private const CONSTRUCTOR = 1210199983; // 0x48222faf

    /** @var float */
    private float $lat;
    /** @var float */
    private float $lon;

    public function __construct(float $lat, float $lon)
    {
        $this->lat = $lat;
        $this->lon = $lon;
    }

    public function getName(): string
    {
        return 'geo_point';
    }

    public function toBinary(): string
    {
        $flags = Packer::packInt(0);
        $l1 = Packer::packDouble($this->lat);
        $l2 = Packer::packDouble($this->lon);

        return Packer::packConstructor(self::CONSTRUCTOR).$flags.$l1.$l2;
    }
}
