<?php

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages\Shared;

use TelegramOSINT\TLMessage\TLMessage\ClientMessages\Api\input_user_from_message;
use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

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
    /** @var int|null */
    private $realUserId;
    /** @var int|null */
    private $msgId;

    /**
     * @param int      $userId
     * @param int      $accessHash
     * @param int|null $msgId      if this is passed, $userId and $accessHash are interpreted as
     * @param int|null $realUserId
     */
    public function __construct(int $userId, int $accessHash, ?int $msgId = null, ?int $realUserId = null)
    {
        $this->userId = $userId;
        $this->accessHash = $accessHash;
        $this->msgId = $msgId;
        $this->realUserId = $realUserId;
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
            ($this->msgId
                ? (
                (new input_user_from_message($this->userId, $this->accessHash, $this->msgId, $this->realUserId))->toBinary()
              ) : (
                Packer::packConstructor(self::CONSTRUCTOR_INPUT_USER).
                Packer::packInt($this->userId).
                Packer::packLong($this->accessHash)
            ));
    }
}
