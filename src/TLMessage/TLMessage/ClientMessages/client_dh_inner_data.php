<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class client_dh_inner_data implements TLClientMessage
{
    private const CONSTRUCTOR = 1715713620; //0x6643B654

    private string $oldClientNonce;
    private string $serverNonce;
    private int $retryId;
    private string $g_b;

    /**
     * @param string $oldClientNonce
     * @param string $serverNonce
     * @param int    $retry_id
     * @param string $g_b
     */
    public function __construct(string $oldClientNonce, string $serverNonce, int $retry_id, string $g_b)
    {
        $this->oldClientNonce = $oldClientNonce;
        $this->serverNonce = $serverNonce;
        $this->retryId = $retry_id;
        $this->g_b = $g_b;
    }

    public function getName(): string
    {
        return 'client_dh_inner_data';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBytes($this->oldClientNonce).
            Packer::packBytes($this->serverNonce).
            Packer::packLong($this->retryId).
            Packer::packString($this->g_b);
    }
}
