<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class req_pq_multi implements TLClientMessage
{
    const CONSTRUCTOR = 0xBE7E8EF1;

    /**
     * @var string
     */
    private $nonce;

    /**
     * req_pq_multi constructor.
     *
     * @param string $nonce
     */
    public function __construct(string $nonce)
    {
        $this->nonce = $nonce;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'req_pq_multi';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBytes($this->nonce);
    }
}
