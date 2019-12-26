<?php

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

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
    public static function getDisconnectTimeoutSec() : int
    {
        return 35;
    }

    /**
     * @return string
     */
    public static function createPingId()
    {
        return openssl_random_pseudo_bytes(8);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'ping_delay_disconnect';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBytes($this->pingId).
            Packer::packInt($this->disconnectDelay);
    }
}
