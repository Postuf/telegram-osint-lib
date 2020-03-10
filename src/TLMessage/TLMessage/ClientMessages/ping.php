<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class ping implements TLClientMessage
{
    const CONSTRUCTOR = 0x7ABE77EC;

    /**
     * @var string
     */
    private $pingId;

    /**
     * @param string|null $pingId
     */
    public function __construct(string $pingId = null)
    {
        $this->pingId = $pingId ? $pingId : self::createPingId();
    }

    public static function createPingId(): string
    {
        return openssl_random_pseudo_bytes(8);
    }

    public function getName(): string
    {
        return 'ping';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBytes($this->pingId);
    }
}
