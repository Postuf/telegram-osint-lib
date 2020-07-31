<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/contacts.getLocated
 */
class contacts_get_located implements TLClientMessage
{
    private const CONSTRUCTOR = 171270230; // 0x0a356056;

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
        return 'contacts_get_located';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            (new geo_point($this->lat, $this->lon))->toBinary();
    }
}
