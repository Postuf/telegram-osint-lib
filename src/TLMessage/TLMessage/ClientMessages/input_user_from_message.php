<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;

/**
 * @see https://core.telegram.org/type/InputUser
 */
class input_user_from_message extends input_user_common
{
    private const CONSTRUCTOR = 497305826;

    /** @var int */
    private int $channelId;
    /** @var int */
    private int $accessHash;
    /** @var int */
    private int $msgId;
    /** @var int */
    private int $userId;

    public function __construct(int $channelId, int $accessHash, int $msgId, int $userId)
    {
        $this->channelId = $channelId;
        $this->accessHash = $accessHash;
        $this->msgId = $msgId;
        $this->userId = $userId;
    }

    public function getName(): string
    {
        return 'input_user_from_message';
    }

    public function toBinary(): string
    {
        return Packer::packConstructor(self::CONSTRUCTOR).
            (new input_peer_channel($this->channelId, $this->accessHash))->toBinary().
            Packer::packInt($this->msgId).
            Packer::packLong($this->userId);
    }
}
