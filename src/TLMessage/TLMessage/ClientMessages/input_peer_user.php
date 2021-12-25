<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/** @see https://core.telegram.org/constructor/inputPeerUser */
class input_peer_user extends input_peer
{
    public const CONSTRUCTOR = 3723011404;

    private int $userId;
    private int $accessHash;

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
            Packer::packLong($this->userId).
            Packer::packLong($this->accessHash);
    }
}
