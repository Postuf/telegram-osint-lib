<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/type/InputUser
 */
class input_user_from_message implements TLClientMessage
{
    private const CONSTRUCTOR = 756118935; // 0x2d117597

    /** @var int */
    private $channelId;
    /** @var int */
    private $accessHash;
    /** @var int */
    private $msgId;
    /** @var int */
    private $userId;

    public function __construct(int $channelId, int $accessHash, int $msgId, int $userId)
    {
        $this->channelId = $channelId;
        $this->accessHash = $accessHash;
        $this->msgId = $msgId;
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'input_user_from_message';
    }

    /**
     * @return string
     */
    public function toBinary()
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            (new input_peer_channel($this->channelId, $this->accessHash))->toBinary().
            Packer::packInt($this->msgId).
            Packer::packInt($this->userId);
    }
}
