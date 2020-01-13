<?php

declare(strict_types=1);

namespace TLMessage\TLMessage\ClientMessages\TgApp;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/** @see https://tl.telethon.dev/constructors/p_q_inner_data_dc.html */
class p_q_inner_data_dc implements TLClientMessage
{
    const CONSTRUCTOR = 0xA9F55F95;

    /**
     * @var int
     */
    private $pq;
    /**
     * @var int
     */
    private $p;
    /**
     * @var int
     */
    private $q;
    /**
     * @var string
     */
    private $oldClientNonce;
    /**
     * @var string
     */
    private $serverNonce;
    /**
     * @var string
     */
    private $newClientNonce;
    /**
     * @var int
     */
    private $dcId;

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
        assert($this->p * $this->q == $this->pq);

    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'pq_inner_data_dc';
    }

    /**
     * @return string
     */
    public function toBinary()
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
