<?php

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
     * ping constructor.
     *
     * @param string|null $pingId
     */
    public function __construct(string $pingId = null)
    {
        $this->pingId = $pingId ? $pingId : self::createPingId();
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
        return 'ping';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBytes($this->pingId);
    }
}
