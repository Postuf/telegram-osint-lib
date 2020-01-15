<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

class client_dh_inner_data implements TLClientMessage
{
    const CONSTRUCTOR = 0x6643b654;

    /**
     * @var string
     */
    private $oldClientNonce;
    /**
     * @var string
     */
    private $serverNonce;
    /**
     * @var int
     */
    private $retryId;
    /**
     * @var string
     */
    private $g_b;

    /**
     * @param  $oldClientNonce
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

    /**
     * @return string
     */
    public function getName()
    {
        return 'client_dh_inner_data';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packBytes($this->oldClientNonce).
            Packer::packBytes($this->serverNonce).
            Packer::packLong($this->retryId).
            Packer::packString($this->g_b);
    }
}
