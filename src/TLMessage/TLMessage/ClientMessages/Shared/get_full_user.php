<?php

namespace TLMessage\TLMessage\ClientMessages\Shared;

use TLMessage\TLMessage\Packer;
use TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/users.getFullUser
 */
class get_full_user implements TLClientMessage
{
    const CONSTRUCTOR = -902781519; // 0xCA30A5B1
    /** @see https://core.telegram.org/type/InputUser */
    const CONSTRUCTOR_INPUT_USER = -668391402; // 0xD8292816

    /**
     * @var int
     */
    private $userId;
    /**
     * @var int
     */
    private $accessHash;

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
        return 'get_full_user';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            Packer::packConstructor(self::CONSTRUCTOR_INPUT_USER).
            Packer::packInt($this->userId).
            Packer::packLong($this->accessHash);
    }
}
