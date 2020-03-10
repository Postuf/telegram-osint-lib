<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/** @see https://core.telegram.org/constructor/inputPeerUser */
class input_peer_user extends input_peer
{
    const CONSTRUCTOR = 2072935910; // 0x7B8E7DE6

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

    public function getName(): string
    {
        return 'input_peer_user';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($this->userId).
            Packer::packLong($this->accessHash);
    }
}
