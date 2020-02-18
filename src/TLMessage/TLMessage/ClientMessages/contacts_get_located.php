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
        return 'contacts_get_located';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            (new geo_point($this->lat, $this->lon))->toBinary();
    }
}
