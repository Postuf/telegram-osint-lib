<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class ping_delay_disconnect implements TLClientMessage
{
    const CONSTRUCTOR = 0xf3427b8c;

    /**
     * @var string
     */
    private $pingId;
    /**
     * @var int
     */
    private $disconnectDelay;

    /**
     * ping constructor.
     *
     * @param string|null $pingId
     */
    public function __construct(string $pingId = null)
    {
        $this->pingId = $pingId ? $pingId : self::createPingId();
        $this->disconnectDelay = self::getDisconnectTimeoutSec();
    }

    /**
     * Timeout when server disconnect client without pings
     * (taken from official client, better not touch)
     *
     * @return int
     */
    public static function getDisconnectTimeoutSec(): int
    {
        return 35;
    }

    public static function createPingId(): string
    {
        return openssl_random_pseudo_bytes(8);
    }

    public function getName(): string
    {
        return 'ping_delay_disconnect';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBytes($this->pingId).
            Packer::packInt($this->disconnectDelay);
    }
}
