<?php

declare(strict_types=1);

namespace TelegramOSINT\TLMessage\TLMessage\ClientMessages;

use TelegramOSINT\TLMessage\TLMessage\Packer;
use TelegramOSINT\TLMessage\TLMessage\TLClientMessage;

/**
 * @see https://core.telegram.org/method/messages.getFullChannel
 */
class get_full_channel implements TLClientMessage
{
    public const CONSTRUCTOR = 141781513;

    /** @var int */
    private int $channelId;
    /** @var int */
    private int $accessHash;

    public function __construct(int $channelId, int $accessHash)
    {
        $this->channelId = $channelId;
        $this->accessHash = $accessHash;
    }

    public function getName(): string
    {
        return 'get_full_channel';
    }

    public function toBinary(): string
    {
        return
            Packer::packConstructor(self::CONSTRUCTOR).
            (new input_channel($this->channelId, $this->accessHash))->toBinary();
    }
}
