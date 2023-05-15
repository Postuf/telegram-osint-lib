<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/schema/mtproto
 */
class p_q_inner_data implements TLClientMessage
{
    private const CONSTRUCTOR = 2211011308;

    private int $pq;
    private int $p;
    private int $q;
    private string $oldClientNonce;
    private string $serverNonce;
    private string $newClientNonce;

    /**
     * p_q_inner_data constructor.
     *
     * @param int    $pq
     * @param int    $p
     * @param int    $q
     * @param string $oldClientNonce
     * @param string $serverNonce
     * @param string $newClientNonce
     */
    public function __construct(int $pq, int $p, int $q, string $oldClientNonce, string $serverNonce, string $newClientNonce)
    {
        $this->pq = $pq;
        $this->p = $p;
        $this->q = $q;
        $this->oldClientNonce = $oldClientNonce;
        $this->serverNonce = $serverNonce;
        $this->newClientNonce = $newClientNonce;

        assert($this->p < $this->q);
        assert($this->p * $this->q === $this->pq);
    }

    public function getName(): string
    {
        return 'pq_inner_data';
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
            Packer::packBytes($this->newClientNonce);
    }
}
