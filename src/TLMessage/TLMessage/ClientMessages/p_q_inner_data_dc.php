<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/** @see https://tl.telethon.dev/constructors/p_q_inner_data_dc.html */
class p_q_inner_data_dc implements TLClientMessage
{
    private const CONSTRUCTOR = 2851430293;

    private int $pq;
    private int $p;
    private int $q;
    private string $oldClientNonce;
    private string $serverNonce;
    private string $newClientNonce;
    private int $dcId;

    /**
     * @param int    $pq
     * @param int    $p
     * @param int    $q
     * @param string $oldClientNonce
     * @param string $serverNonce
     * @param string $newClientNonce
     * @param int    $dcId
     */
    public function __construct(int $pq, int $p, int $q, string $oldClientNonce, string $serverNonce, string $newClientNonce, int $dcId)
    {
        $this->pq = $pq;
        $this->p = $p;
        $this->q = $q;
        $this->oldClientNonce = $oldClientNonce;
        $this->serverNonce = $serverNonce;
        $this->newClientNonce = $newClientNonce;
        $this->dcId = $dcId;

        assert($this->p < $this->q);
        assert($this->p * $this->q === $this->pq);
    }

    public function getName(): string
    {
        return 'pq_inner_data_dc';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packLongAsBytes($this->pq).
            Packer::packIntAsBytesLittleEndian($this->p).
            Packer::packIntAsBytesLittleEndian($this->q).
            Packer::packBytes($this->oldClientNonce).
            Packer::packBytes($this->serverNonce).
            Packer::packBytes($this->newClientNonce).
            Packer::packInt($this->dcId);
    }
}
