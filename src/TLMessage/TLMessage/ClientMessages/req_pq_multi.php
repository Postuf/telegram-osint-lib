<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class req_pq_multi implements TLClientMessage
{
    public const CONSTRUCTOR = 0xBE7E8EF1;

    /**
     * @var string
     */
    private $nonce;

    /**
     * @param string $nonce
     */
    public function __construct(string $nonce)
    {
        $this->nonce = $nonce;
    }

    public function getName(): string
    {
        return 'req_pq_multi';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBytes($this->nonce);
    }
}
