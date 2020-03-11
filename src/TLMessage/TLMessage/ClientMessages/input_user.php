<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

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

    public function getName(): string
    {
        return 'input_user';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packInt($this->userId).
            Packer::packLong($this->accessHash);
    }
}
