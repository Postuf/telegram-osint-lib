<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/users.getFullUser
 */
class get_full_user implements TLClientMessage
{
    public const CONSTRUCTOR = -902781519; // 0xCA30A5B1
    /** @see https://core.telegram.org/type/InputUser */
    public const CONSTRUCTOR_INPUT_USER = -668391402; // 0xD8292816
    private int $userId;
    private int $accessHash;
    /** @var int|null */
    private ?int $realUserId;
    /** @var int|null */
    private ?int $msgId;

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

    public function getName(): string
    {
        return 'get_full_user';
    }

    public function toBinary(): string
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
