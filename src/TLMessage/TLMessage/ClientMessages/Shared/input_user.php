<?php

namespace TLMessage\TLMessage\ClientMessages\Shared;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/** @see https://core.telegram.org/constructor/inputUser */
class input_user implements TLClientMessage
{
    const CONSTRUCTOR = -668391402; // 0xd8292816

    /**
     * @var int
     */
    private $userId;
    /**
     * @var int
     */
    private $accessHash;

    /**
     * @param int $userId
     * @param int $accessHash
     */
    public function __construct(int $userId, int $accessHash)
    {
        $this->userId = $userId;
        $this->accessHash = $accessHash;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'input_user';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($this->userId).
            Packer::packLong($this->accessHash);
    }
}
